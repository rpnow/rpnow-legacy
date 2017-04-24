(function() {
  var path = (function() {
    var scripts = document.getElementsByTagName("script"),
      src = scripts[scripts.length-1].src;
    return src.substring(0, src.lastIndexOf('/'));
  })();
  function updateMOTD() {
    // check the motd
    var req = new XMLHttpRequest();
    req.open('GET', path + '/broadcast');
    req.onload = motdLoaded; // (defined below)
    req.send();
    // when the motd is loaded...
    function motdLoaded() {
      if(req.status !== 200) return;
      // if there is a motd
      if(req.responseText) {
        // ignore if the user already dismissed it
        if(req.responseText === localStorage.getItem('last_message')) return;
        // otherwise display it
        
        // if it's already there...
        if(document.getElementById('motd')) {
          // show it and update contents
          document.getElementById('motd').style.display="";
          document.getElementById('motd-content').innerHTML = req.responseText;
        }
        // otherwise create new element
        else {
          var el = document.createElement('div');
          el.id = 'motd';
            var elMsg = document.createElement('div');
            elMsg.id = 'motd-content';
            elMsg.innerHTML = req.responseText;
            el.appendChild(elMsg);
            var elClose = document.createElement('button');
            elClose.id = 'motd-close';
            elClose.addEventListener('click', function() { closeMotd(); });
            elClose.innerHTML = 'close';
            el.appendChild(elClose);
          document.querySelector('body').appendChild(el);
        }
      }
      // no motd = delete cookie
      else {
        localStorage.removeItem('last_message');
      }
    }
    function closeMotd() {
      document.querySelector('#motd').style.display="none";
      localStorage.setItem('last_message', req.responseText);
    }
    // check again every so often
    setTimeout(updateMOTD, 60*1000)
  }
  // delay first check by a couple seconds
  setTimeout(updateMOTD, 2*1000);
})();