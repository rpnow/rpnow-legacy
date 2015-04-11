function RPRoom(reqUrl, postsPerPage) {
  // robustness
  if(!reqUrl) throw new Error('No room specified.');
  
  // variables
  var interval = null;
  
  var self = this;
  var numMsg = 0;
  var numChar = 0;
  var timer = null;
  var charList = [];
  
  // get the latest posts
  this.loadLatest = function(callback) {
    $.ajax({
      type: 'GET',
      url: reqUrl + '/data',
      success: function(data) {
        // add messages
        for(var i = 0; i < data.messages.length; ++i) {
          addMessageElement(data.messages[i]);
        }
        // add characters
        for(var i = 0; i < data.characters.length; ++i) {
          addCharacterElement(data.characters[i]);
        }
        // initialize counters
        numMsg = data.messageCount;
        numChar = data.characterCount;
        // alter page
        $('#loading').hide();
        if(data.messageCount > postsPerPage) {
          $('#showing-latest').show();
        }
        else if(data.messageCount === 0) {
          $('#empty-room').show();
        }
        $('#message-box').slideDown(250);
        // done.
        callback();
      }
    });
  }
  
  // cancel update timer and refresh now
  this.updateNow = function() {
    if(!interval) throw new Error('startUpdating must be called first!');
    if(timer) {
      clearTimeout(timer);
      ajaxUpdate();
    }
  }
  
  // begin updating!
  this.startUpdating = function(myInterval) {
    // check to see if we're already fetching updates
    if(interval) throw new Error('already updating!');
    if(!(myInterval > 0)) throw new Error('bad inverval.');
    // set interval
    interval = myInterval;
    // update on a timer
    self.loadLatest(function() {
      timer = setTimeout(ajaxUpdate, interval);
      // additionally update the timestamps every so often
      updateTimeAgo();
      setInterval(updateTimeAgo, 600000);
    });
  }
  
  // fetch and apply character/message updates from server
  function ajaxUpdate() {
    timer = null;
    $.ajax({
      type: 'GET',
      url: reqUrl + '/updates',
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
          if(isAtBottom) $('html, body').animate({scrollTop: getDocHeight()}, 250);
        }
        // update characters
        for(var i = 0; i < data.characters.length; ++i) {
          addCharacterElement(data.characters[i]);
        }
        // update counters
        numMsg += data.messages.length;
        numChar += data.characters.length;
        // show alerts maybe
        if(data.messages.length > 0) {
          flashTitle('* ' + data.messages[data.messages.length -1].Name + ' says...', 3);
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
    $('<div/>', {
      'class': 'message ' + cssName(message.Name)
    }).append(
      $('<div/>', {
        'class': 'name',
        text: message.Name
      })
    ).append(
      $('<div/>', {
        'class': 'timestamp',
        'data-timestamp': message.Timestamp,
        text: displayTimestamp(message.Timestamp)
      })
    ).append(
      $('<div/>', {
        'class': 'content',
        html: formatMessage(message.Content, message.Name)
      })
    ).appendTo('#messages');
  }
  // add character button and character css
  function addCharacterElement(character) {
    //create and add element
    $('<li/>', {}).append(
      $('<a/>', {
        href: '#',
        'class': cssName(character.Name),
        text: character.Name
      })
    ).appendTo('#character-menu ul')
    // ... and add click functionality
    .click(clickCharaButton);
    // as well as giving them css (unless Narrator)
    if(character.Name !== 'Narrator') {
      $('head').append(
        $('<style/>', {
          text: '.' + cssName(character.Name)
            + "{ background-color: " + character.Color + "; "
            + "color: " + character.Contrast + "; }"
        })
      );
    }
    // remember that character!
    charList.push(character.Name);
  }
  
  this.send = function(data) {
    $.ajax({
      type: 'POST',
      url: reqUrl + '/send',
      data: data,
      success: function() { self.updateNow(); }
    });
  }
  this.addCharacter = function(data) {
    if(!data.name) {
      throw new Error('Please enter a name.');
    }
    if(charList.some(function(x) { return x == data.name; })) {
      throw new Error('Character "' + data.name + '" already exists!');
    }
    $.ajax({
      type: 'POST',
      url:  reqUrl + '/character',
      data: data,
      success: function() { self.updateNow(); }
    });
  }
}



/*****
 * MISC FUNCTIONS *
              *****/
//


// css name string for this character
function cssName(name) {
  return 'chara-' + name.replace(/[^0-9a-zA-Z]/g, function(x) { return '-' + x.charCodeAt(0).toString(16).toUpperCase(); });
}
// format string to have minimal markdown
function formatMessage(str, name) {
  // escape special characters
  str = escapeHtml(str);
  // actions
  str = str.replace(/\*([^\r\n\*_]+)\*/g, '<span class="action ' + cssName(name) + '">*$1*</span>');
  // bold
  str = str.replace(/__([^\r\n_]+)__/g, '<b>$1</b>');
  // italix
  str = str.replace(/_([^\r\n_]+)_/g, '<i>$1</i>');
  str = str.replace(/\/([^\r\n\/>]+)\//g, '<i>$1</i>');
  // line breaks
  // http://stackoverflow.com/questions/2919337/jquery-convert-line-breaks-to-br-nl2br-equivalent
  str = str.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br />$2');
  
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
