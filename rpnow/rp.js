function RP(id) {
  var rp = this;
  // properties
  Object.defineProperty(this, 'id', { get: function() { return id; }});
  
  // ajax requests
  // pieced together from: http://stackoverflow.com/questions/8567114/
  function ajax(url, method /*, data, callback */) {
    // variables
    var req = new XMLHttpRequest();
    var reqUrl = '/' + rp.id + '/ajax/' + url;
    var callback = null;
    var data = null;
    if(typeof(arguments[arguments.length-1]) === 'function')
      callback = arguments[arguments.length-1];
    if(arguments.length >= 3 && typeof(arguments[2]) === 'object')
      data = arguments[2];
    // callback function on success
    if(callback) req.onreadystatechange = function() {
      if(req.readyState === XMLHttpRequest.DONE && req.status === 200) {
        callback(JSON.parse(req.responseText));
      }
    }
    // generate query string from data
    var queryString = null;
    if(data) {
      var query = [];
      for (var key in data) {
        query.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
      }
      queryString = query.join('&');
    }
    // apply things to request
    if(method === 'GET' && queryString) reqUrl += '?' + queryString;
    req.open(method, reqUrl, true)
    if(method === 'POST') req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    // send
    if(method === 'GET' || !queryString) req.send();
    else req.send(queryString);
  }
  
  // load a single page
  this.fetchPage = function(pageNum, callback) {
    var msgs = [];
    var charas = [];
    ajax('page/'+pageNum, 'GET', function(e) {
      // add characters
      for(var i = 0; i < e.charas.length; ++i) {
        charas.push(new Chara(e.charas[i]));
      }
      // add messages
      for(var i = 0; i < e.msgs.length; ++i) {
        msgs.push(new Message(e.msgs[i], charas));
      }
      // callback
      callback({ msgs: msgs, charas: charas });
    });
  };
  
  // load a chat object
  this.chat = function() {
    var chat = {};
    // chat variables
    var msgs = [];
    var charas = [];
    var msgCounter;
    var charaCounter;
    /*
    var upMsgCounter;
    var upCharaCounter;
    */
    var maxMsgs;
    var interval;
    var isLoaded = false;
    var isLoading = false;
    var net;
    // events
    var onLoad, onMessage, onChara; /* onUpdateMessage, onUpdateChara, onUnloadMessage; */
    chat.onLoad = function(callback) { onLoad = callback; };
    chat.onMessage = function(callback) { onMessage = callback; };
    chat.onChara = function(callback) { onChara = callback; };
    /*
    chat.onUpdateMessage = function(callback) { onUpdateMessage = callback; };
    chat.onUpdateChara = function(callback) { onUpdateChara = callback; };
    chat.onUnloadMessage = function(callback) { onUnloadMessage = callback; };
    */
    // properties
    Object.defineProperties(chat, {
      'id': {value: rp.id},
      'charas': {get: function() { return charas; }},
      'msgs': {get: function() { return msgs; }},
      'msgCount': {get: function() { return msgCounter; }},
      'charaCount': {get: function() { return charaCounter; }},
      'maxMsgs': {get: function() { return maxMsgs; }}
    });
    chat.getCharaById = function(id) {
      for(var i = 0; i < charas.length; ++i)
        if(charas[i].id === +id)
          return charas[i];
      throw new Error('invalid character id: ' + id);
    }
    // for initializing the chat
    chat.load = function() {
      // prevent loading twice
      if(isLoaded) throw new Error('chat.load: chat was already loaded.');
      if(isLoading) throw new Error('chat.load: chat is already loading.');
      isLoading = true;
      // initial load
      ajax('chat', 'GET', function(data) {
        // set variables
        charas = data.charas.map(function(x){return new Chara(x);});
        msgs = data.msgs.map(function(x){return new Message(x, charas);});
        msgCounter = data.msgCounter;
        charaCounter = data.charaCounter;
        maxMsgs = data.postsPerPage;
        interval = data.refreshMillis;
        isLoaded = true;
        isLoading = false;
        // callback
        if(onLoad) onLoad(msgs, charas);
        // start updating
        net.start();
      });
    };
    // send message
    chat.sendMessage = function(content, voice, callback) {
      var data = {
        content: content,
        msgCounter: msgCounter, charaCounter: charaCounter
      };
      if(voice instanceof Chara) {
        data['type'] = 'Character';
        data.charaId = voice.id;
      }
      else {
        data['type'] = voice;
      }
      net.queuePost('message', data, callback);
    };
    // send character
    chat.sendChara = function(name, color, callback) {
      var data = {
        name: name, color: color,
        msgCounter: msgCounter, charaCounter: charaCounter
      };
      net.queuePost('character', data, callback);
    };
    /*
    chat.deleteMessage = function(id) {
      
    };
    chat.deleteChara = function(id) {
      
    };
    chat.undeleteMessage = function(id) {
      
    };
    chat.undeleteChara = function(id) {
      
    };*/
    // when an update comes in
    function processUpdates(data) {
      // add new characters
      if(data.newCharas) {
        var newCharas = data.newCharas.map(function(x){return new Chara(x);})
        for(var i = 0; i < newCharas.length; ++i) {
          charas.push(newCharas[i]);
          onChara(newCharas[i]);
        }
        charaCounter += data.newCharas.length;
      }
      // add new messages
      if(data.newMsgs) {
        var newMsgs = data.newMsgs.map(function(x){return new Message(x, charas);});
        for(var i = 0; i < newMsgs.length; ++i) {
          msgs.push(newMsgs[i]);
          onMessage(newMsgs[i]);
        }
        msgCounter += data.newMsgs.length;
      }
    }
    // flow of communications
    net = (function() {
      var timer;
      var queue = [];
      var busy = false;
      var reqCounter = 0;
      function fetchUpdates() {
        ++reqCounter;
        var reqNum = reqCounter;
        var params = { charaCounter: charaCounter, msgCounter: msgCounter };
        ajax('updates', 'GET', params, function(data) {
          if(busy || reqNum !== reqCounter) return;
          busy = true;
          processUpdates(data);
          doNext();
        });
      }
      function pop() {
        // block while posting this request
        busy = true;
        // nullify any ongoing passive request
        ++reqCounter;
        // cancel any delayed passive request
        if(timer) {
          clearTimeout(timer);
          timer = null;
        }
        // execute
        var req = queue.shift();
        ajax(req.url, 'POST', req.data, function(data) {
          processUpdates(data);
          if(req.callback) req.callback(data);
          doNext();
        });
      }
      function doNext() {
        if(queue.length > 0) {
          pop();
        }
        else {
          timer = setTimeout(fetchUpdates, interval);
          busy = false;
        }
      }
      return {
        start: function(){
          if(timer || busy) return;
          timer = setTimeout(fetchUpdates, interval);
        },
        queuePost: function(url, data, callback){
          // add ajax call to queue
          queue.push({ url: url, data: data, callback: callback });
          // if we're not busy, then we can execute it now
          if(!busy) pop();
        }
      }
    })();
    // done.
    return chat;
  };
  
  // classes
  function Message(data, charas) {
    Object.defineProperties(this, {
      'id': {value: +data.Number},
      'content': {value: data.Content},
      'timeSent': {value: data.Time_Created},
      'timeUpdated': {value: data.Time_Updated},
      'user': {value: new User(data.IPColor)},
      'type': {value: data.Type},
      'deleted': {value: data.Deleted},
      'createElement': {value: function(timeFormat) {
        // outer element with the appropriate class
        var el = $('<div/>', {
          'class': ({
            'Narrator':'message message-narrator',
            'Character':'message message-chara',
            'OOC':'message message-ooc'
          })[this.type]
        });
        // character-specific
        if(this.type === 'Character') {
          // style
          if(this.chara) el.css({'background-color':this.chara.color, 'color':this.chara.textColor});
          // nametag
          el.append($('<div/>', {
            'class': 'name',
            text: this.chara.name
          }));
        }
        // post details
        var ts = moment.unix(this.timeSent);
        if(!timeFormat || timeFormat === 'absolute') ts = ts.format('lll');
        else if(timeFormat === 'relative') ts = ts.calendar();
        else throw new Error('unknown time format');
        el.append(
          $('<div/>', {'class': 'message-details'})
          // color ip box
          .append(this.user.createIcon())
          // timestamp
          .append($('<span/>', {
            'class': 'timestamp',
            text: ts
          }))
        );
        // message body
        el.append($('<div/>', {
          'class': 'content',
          html: formatMessage(this.content, this.chara)
        }));
        return el;
      }}
    });
    if(this.type==='Character') Object.defineProperties(this, {
      'charaId': {value: +data.Chara_Number},
      'chara': {value: (function() {
        if(charas)
          for(var i = 0; i < charas.length; ++i)
            if(charas[i].id === +data.Chara_Number)
              return charas[i];
        return undefined;
      })()}
    });
    
  }
  function Chara(data) {
    Object.defineProperties(this, {
      'id': {value: +data.Number},
      'name': {value: data.Name},
      'color': {value: data.Color},
      'textColor': {value: data.Contrast},
      'user': {value: new User(data.IPColor)},
      'createButton': {value: function(callback) {
        return $('<a/>', {
          text: this.name,
          href: '#',
          'style': 'background-color:' + this.color + ';' + 'color:' + this.textColor
        }).click(callback);
      }}
    });
  }
  function User(data) {
    Object.defineProperties(this, {
      'anon': {value: true},
      'colors': {value: data},
      'createIcon': {value: function(){
        return $('<span/>', { 'class': 'color-ip-box' })
          .append($('<span/>', { 'style': 'background-color: ' + data[0] }))
          .append($('<span/>', { 'style': 'background-color: ' + data[1] }))
          .append($('<span/>', { 'style': 'background-color: ' + data[2] }));
      }}
    });
  }
}



// legacy methods

//escape html special chars from AJAX updates
// http://stackoverflow.com/questions/1787322/htmlspecialchars-equivalent-in-javascript
function escapeHtml(text) {
  var map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}


function formatMessage(text, chara) {
  // escape special characters
  var str = escapeHtml(text);
  // urls
  // http://stackoverflow.com/a/3890175
  str = str.replace(
    /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim,
    '<a href="$1" target="_blank">$1</a>'
  );
  // actions
  if(chara) {
    str = str.replace(/\*([^\r\n\*_]+)\*/g, '<span class="action" style="background-color:' + chara.color + ';' + 'color:' + chara.textColor + '">*$1*</span>');
  }
  // bold
  str = str.replace(/(^|\s|(?:&quot;))__([^\r\n_]+)__([\s,\.\?!]|(?:&quot;)|$)/g, '$1<b>$2</b>$3');
  // italix
  str = str.replace(/(^|\s|(?:&quot;))_([^\r\n_]+)_([\s,\.\?!]|(?:&quot;)|$)/g, '$1<i>$2</i>$3');
  str = str.replace(/(^|\s|(?:&quot;))\/([^\r\n\/>]+)\/([\s,\.\?!]|(?:&quot;)|$)/g, '$1<i>$2</i>$3');
  // both!
  str = str.replace(/(^|\s|(?:&quot;))___([^\r\n_]+)___([\s,\.\?!]|(?:&quot;)|$)/g, '$1<b><i>$2</i></b>$3');
  // line breaks
  // http://stackoverflow.com/questions/2919337/jquery-convert-line-breaks-to-br-nl2br-equivalent
  str = str.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br />$2');
  // fake line breaks
  str = str.replace(/(\\r\\n|\\n\\r|\\r|\\n|\&lt;br(?: ?\/)?\&gt;)/g, '<br />');
  // mdash
  str = str.replace(/--/g, '&mdash;');
  
  // done.
  return str;
}
