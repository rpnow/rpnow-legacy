function updateMOTD() {
  // cookie functions
  // cookies are used to remember if you already closed this MOTD.
  // functions from: http://www.w3schools.com/js/js_cookies.asp
  // modified for entire domain with:
  // http://stackoverflow.com/questions/5671451/
  function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + encodeURIComponent(cvalue) + "; " + expires + ";path=/";
  }
  function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0)==' ') c = c.substring(1);
      if (c.indexOf(name) == 0) return decodeURIComponent(c.substring(name.length,c.length));
    }
    return "";
  }
  function deleteCookie(cname) {
    document.cookie = cname + "=;expires=Wed; 01 Jan 1970"
  }
  // check the motd
  var req = new XMLHttpRequest();
  req.open('GET', '/broadcast/');
  req.onload = motdLoaded; // (defined below)
  req.send();
  // when the motd is loaded...
  function motdLoaded() {
    if(req.status !== 200) return;
    // if there is a motd
    if(req.responseText) {
      // ignore if the user already dismissed it
      if(req.responseText === getCookie('lastmessage')) return;
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
      deleteCookie('lastmessage');
    }
  }
  function closeMotd() {
    document.querySelector('#motd').style.display="none";
    setCookie('lastmessage', req.responseText, 14);
  }
  // check again every so often
  setTimeout(updateMOTD, 60*1000)
}
// delay first check by a couple seconds
setTimeout(updateMOTD, 2*1000);