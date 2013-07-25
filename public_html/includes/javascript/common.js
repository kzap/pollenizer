var p_container = null;

function findPosX(obj) {
	var curleft = 0;
	if (obj.offsetParent)
	{
		while (obj.offsetParent)
		{
			curleft += obj.offsetLeft;
			obj = obj.offsetParent; 
		}
	}
	return curleft;
}
function findPosY(obj) {
	var curtop = 0;
	if (obj.offsetParent)
	{
		while (obj.offsetParent)
		{
			curtop += obj.offsetTop;
			obj = obj.offsetParent; 
		}
	}
	return curtop;
}


function initAJAX()
{
	var o = false;
	/*@cc_on @*/
	/*@if (@_jscript_version >= 5)
	try {
	o = new ActiveXObject("Msxml2.XMLHTTP");
	} catch (e) {
	try {
	o = new ActiveXObject("Microsoft.XMLHTTP");
	} catch (E) {
	o = false;
	}
	}
	@end @*/
	if (!o && typeof XMLHttpRequest!='undefined')
	{
	
		o = new XMLHttpRequest();
	}

	return o;
}


function LoadIntoElementRegistration( url, element_id, text, field )
{
        var X = initAJAX();
    var element = document.getElementById(element_id);
        var text;

        if (text == null)
        {
                text = 'Loading';
        }

    element.innerHTML = text;

        X.open("GET", url);
        X.send(null);

        X.onreadystatechange = function()
        {
        if ( X.readyState == 4 && X.status == 200 )
                {
            element.innerHTML = X.responseText;
            if (X.responseText == '<font style="color: green;">Okay</font>') {
                document.getElementById(field).style.border='1px solid #777777';
            } else {
                document.getElementById(field).style.border='red 1px solid';
            }
        }
    }
}


function LoadIntoElement( url, element_id, text )
{
	var X = initAJAX();
    var element = document.getElementById(element_id);
	var text;
	
	if (text == null)
	{
		text = 'Loading';
	}

    element.innerHTML = text;
	
	X.open("GET", url);
	X.send(null);

	X.onreadystatechange = function()
	{
        if ( X.readyState == 4 && X.status == 200 )
		{
            element.innerHTML = X.responseText;
        }
    }	
}

function ElementVal(element_id)
{
	var element = document.getElementById(element_id);

	return element.value;
}

function setActiveStyleSheet(title) {
  var i, a, main;
  for(i=0; (a = document.getElementsByTagName("link")[i]); i++) {
    if(a.getAttribute("rel").indexOf("style") != -1 && a.getAttribute("title")) {
      a.disabled = true;
      if(a.getAttribute("title") == title)
		{
		  a.disabled = false;
			
			var title = getActiveStyleSheet();
			createCookie("style", title, 365);
		}
    }
  }
}

