///////////////////////////////////////

var onload_queue = [];
var dom_loaded = false;

function loadScriptAsync(src, callback, run_immediately) {
  var script = document.createElement('script');
  script.type = "text/javascript";
  script.async = true;
  script.src = src;
  
  if("undefined" != typeof callback){
    script.onload = script.onreadystatechange = function() {
      if (dom_loaded || run_immediately)
        callback();
      else
        onload_queue.push(callback);
      script.onload = null;
      script.onreadystatechange = null;
    };
  }
  var s = document.getElementsByTagName('script')[0];
  s.parentNode.insertBefore(script, s);
}

function domLoaded() {
   dom_loaded = true;
   var len = onload_queue.length;
   for (var i = 0; i < len; i++) {
     onload_queue[i]();
   }
   onload_queue = null;
}

///////////////////////////////////////

// Dean Edwards/Matthias Miller/John Resig

function init() {
  // quit if this function has already been called
  if (arguments.callee.done) return;

  // flag this function so we don't do the same thing twice
  arguments.callee.done = true;

  // kill the timer
  if (_timer) clearInterval(_timer);

  // do stuff
  domLoaded();
}

/* for Mozilla/Opera9 */
if (document.addEventListener) {
  document.addEventListener("DOMContentLoaded", init, false);
}

/* for Internet Explorer */
/*@cc_on @*/
/*@if (@_win32)
  document.write("<script id=__ie_onload defer src=javascript:void(0)><\/script>");
  var script = document.getElementById("__ie_onload");
  script.onreadystatechange = function() {
    if (this.readyState == "complete") {
      init(); // call the onload handler
    }
  };
/*@end @*/

/* for Safari */
if (/WebKit/i.test(navigator.userAgent)) { // sniff
  var _timer = setInterval(function() {
    if (/loaded|complete/.test(document.readyState)) {
      init(); // call the onload handler
    }
  }, 10);
}

/* for other browsers */
window.onload = init;

///////////////////////////////////////