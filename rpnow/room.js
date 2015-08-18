
/*****
 * MAIN ROOM CLASS *
               *****/
//

function RPRoom(reqUrl) {
  // robustness
  if(!reqUrl) throw new Error('No room specified.');
  
  // variables
  var interval = null;
  var postsPerPage = null;
  
  var self = this;
  var numMsg = 0;
  var numChar = 0;
  var timer = null;
  var charList = [];
  var alertNoise = null;
  
  Object.defineProperty(this, 'messageCount', {
    get: function() { return numMsg; }
  });
  
  Object.defineProperty(this, 'postsPerPage', {
    get: function() { return postsPerPage; }
  });
  
  // get latest posts and refresh
  this.loadFeed = function(params) {
    $.ajax({
      type: 'GET',
      url: reqUrl + '/ajax/latest',
      success: function(data) {
        // ppp
        postsPerPage = data.postsPerPage;
        // add messages
        for(var i = 0; i < data.messages.length; ++i) {
          addMessageElement(data.messages[i]);
        }
        // add characters
        for(var i = 0; i < data.characters.length; ++i) {
          addCharacterElement(data.characters[i]);
          addCharacterCSS(data.characters[i]);
          charList.push(data.characters[i].Name);
        }
        // grab the notification noise
        alertNoise = new Audio('assets/alert.mp3');
        // initialize counters
        numMsg = data.messageCount;
        numChar = data.characterCount;
        // callback
        if(params.onload) params.onload();
        // start updating
        if(params.millis) {
          interval = params.millis;
          timer = setTimeout(ajaxUpdate, interval);
        }
        // additionally update the timestamps every so often
        updateTimeAgo();
      }
    });
  };
  
  // cancel update timer and refresh now
  this.updateNow = function() {
    if(!interval) throw new Error('startUpdating must be called first!');
    if(timer) {
      clearTimeout(timer);
      ajaxUpdate();
    }
  };
  
  // load specified page
  this.loadPage = function(params) {
    $.ajax({
      type: 'GET',
      url: reqUrl + '/ajax/' + params.page,
      success: function(data) {
        // ppp
        postsPerPage = data.postsPerPage;
        // add messages
        for(var i = 0; i < data.messages.length; ++i) {
          addMessageElement(data.messages[i]);
        }
        // add characters
        for(var i = 0; i < data.characters.length; ++i) {
          addCharacterCSS(data.characters[i]);
        }
        // callback
        if(params.onload) params.onload();
        // update the timestamps every so often
        updateTimeAgo();
      }
    });
  };
  
  // fetch and apply character/message updates from server
  function ajaxUpdate() {
    timer = null;
    $.ajax({
      type: 'GET',
      url: reqUrl + '/ajax/updates',
      data: { characters: numChar, messages: numMsg },
      success: function(data) {
        // update messages
        if(data.messages.length > 0) {
          // check if we're at the bottom of the page
          var isAtBottom = $(window).scrollTop() + $(window).height() >= getDocHeight();
          // add the messages
          for(var i = 0; i < data.messages.length; ++i) {
            addMessageElement(data.messages[i]);
          }
          // if we were at the bottom of the page, scroll down to bottom
          if(isAtBottom) $('html, body').stop().animate({scrollTop: getDocHeight()}, 250);
        }
        // update characters
        for(var i = 0; i < data.characters.length; ++i) {
          addCharacterElement(data.characters[i]);
          addCharacterCSS(data.characters[i]);
          charList.push(data.characters[i].Name);
        }
        // update counters
        numMsg += data.messages.length;
        numChar += data.characters.length;
        // do an alert if the tab isn't in focus
        if(data.messages.length > 0 && document.visibilityState === 'hidden') {
          var lastMsg = data.messages[data.messages.length -1];
          var alertText;
          if(lastMsg.Type === 'Character') alertText = lastMsg.Name + ' says...';
          else if(lastMsg.Type === 'Narrator') alertText = 'The narrator says...';
          else if(lastMsg.Type === 'OOC') alertText = 'OOC message...';
          flashTitle('* ' + alertText, 3);
          alertNoise.play();
        }
        // done. wait and then do this again
        timer = setTimeout(ajaxUpdate, interval);
      }
    });
  }
  
  // update the timestamps in the message boxes
  function updateTimeAgo() {
    $('#messages .message .timestamp').text(
      function() { return displayTimestamp($(this).data().timestamp); }
    );
    setInterval(updateTimeAgo, 600000);
  }
  
  // add a message element and adjust other things accordingly
  function addMessageElement(message) {
    var screenMsgs = $('.message').length;
    // remove empty-room message if it's there
    if(screenMsgs === 0) {
      $('#empty-room').remove();
    }
    // remove old messages if there's too many
    else if(screenMsgs >= postsPerPage) {
      $('.message').slice(0,1).remove();
      // maybe now show the "showing-latest" message
      if(numMsg === postsPerPage) {
        $('#showing-latest').show();
      }
    }
    // create and add element
    var el = null;
    if(message.Type === 'Narrator') {
      el = $('<div/>', {
        'class': 'message message-narrator'
      });
    }
    else if(message.Type === 'Character') {
      el = $('<div/>', {
        'class': 'message message-chara ' + cssName(message.Name)
      }).append(
        $('<div/>', {
          'class': 'name',
          text: message.Name
        })
      );
    }
    else if(message.Type === 'OOC') {
      el = $('<div/>', {
        'class': 'message message-ooc'
      });
    }
    // post details
    el.append(
      $('<div/>', {
        'class': 'message-details'
      })
      // color ip box
      .append(
        $('<span/>', { 
          'class': 'color-ip-box',
          'style': 'background-color: ' + message.IPColor
        })
      )
      // timestamp
      .append(
        $('<span/>', {
          'class': 'timestamp',
          'data-timestamp': message.Timestamp,
          text: displayTimestamp(message.Timestamp)
        })
      )
    )
    // message body
    .append(
      $('<div/>', {
        'class': 'content',
        html: formatMessage(message)
      })
    ).appendTo('#messages');
  }
  // add character button and character css
  function characterElement(name) {
    return $('<li/>').append(
      $('<a/>', {
        href: '#',
        'class': cssName(name),
        text: name
      }).click(clickCharaButton)
    );
  }
  function addCharacterElement(character) {
    //create and add element
    characterElement(character.Name)
      .appendTo('#character-menu ul#normal-characters');
  }
  function addCharacterCSS(character) {
    // add character css to page
    $('head').append(
      $('<style/>', {
        text: '.' + cssName(character.Name)
          + "{ background-color: " + character.Color + "; "
          + "color: " + character.Contrast + "; }"
      })
    );
  }
  
  this.addMessage = function(data) {
    $.ajax({
      type: 'POST',
      url: reqUrl + '/ajax/message',
      data: data,
      success: function() { self.updateNow(); }
    });
  };
  this.addCharacter = function(data) {
    if(!data.name) {
      throw new Error('Please enter a name.');
    }
    if(charList.some(function(x) { return x == data.name; })) {
      throw new Error('Character "' + data.name + '" already exists!');
    }
    $.ajax({
      type: 'POST',
      url:  reqUrl + '/ajax/character',
      data: data,
      success: function() { self.updateNow(); }
    });
  };
}




/*****
 * ENCLOSE CLASS IN A FACTORY *
                          *****/
//

var RPRoom = (function() {
  var _class = RPRoom;
  var _last = null;
  
  return function() {
    if(arguments.length === 1) {
      if(_last) throw new Error('Already connected to a room.');
      _last = new _class(arguments[0]);
    }
    if(_last === null) {
      throw new Error('Please specify room ID.');
    }
    return _last;
  }
})();




/*****
 * MISC FUNCTIONS *
              *****/
//

// css name string for this character
function cssName(name) {
  return 'chara-' + name.replace(/[^0-9a-zA-Z]/g, function(x) { return '-' + x.charCodeAt(0).toString(16).toUpperCase(); });
}
// format string to have minimal markdown
function formatMessage(message) {
  // escape special characters
  str = escapeHtml(message.Content);
  // urls
  // http://stackoverflow.com/a/3890175
  str = str.replace(
    /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim,
    '<a href="$1" target="_blank">$1</a>'
  );
  // actions
  if(message.Type === 'Character') {
    str = str.replace(/\*([^\r\n\*_]+)\*/g, '<span class="action ' + cssName(message.Name) + '">*$1*</span>');
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
//cross-browser get height of document.
// http://james.padolsey.com/javascript/get-document-height-cross-browser/
function getDocHeight() {
  var D = document;
  return Math.max(
    D.body.scrollHeight, D.documentElement.scrollHeight,
    D.body.offsetHeight, D.documentElement.offsetHeight,
    D.body.clientHeight, D.documentElement.clientHeight
  );
}
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

// text displayed in the timestamp field
function displayTimestamp(t) {
  return moment.unix(t).calendar();
}

//timer function for the title bar
flashTitle = (function() {
  var oldTitle = document.title;
  var alertMsg = null;
  var i = 0;
  var informTimer = null;
  function timerAction() {
    if(i%2) document.title = oldTitle;
    else document.title = alertMsg;
    --i;
    if(i >= 0) informTimer = setTimeout(timerAction, 500);
  }
  document.addEventListener('visibilitychange', function(evt) {
    if(document.visibilityState === 'visible') {
      clearTimeout(informTimer);
      document.title = oldTitle;
    }
  });
  return function(msg, flashes) {
    if(document.visibilityState === 'visible') return;
    i = (flashes===undefined? 1: flashes)*2;
    if(informTimer) clearTimeout(informTimer);
    alertMsg = msg;
    timerAction(msg);
  };
})();
