// This works yay!

var xmlHttp;
var AJAXGet = null;
var AjaxAtWork = 0;

function GetXmlHttpObject() {
  var xmlHttp=null;
  try
    {// Internet Explorer
      xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
    }
  catch (e)
    {
    try
      {
        xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
      }
    catch (e)
      {  // Firefox, Opera 8.0+, Safari
      xmlHttp=new XMLHttpRequest();
      }
    }

  return xmlHttp;
}

function stateChanged() {
  if( !AJAXGet ) return;
  if (xmlHttp.readyState == 4 ) {
    if( xmlHttp.status != 200 ) {
       alert("There was a problem retrieving data:\n" + xmlHttp.statusText);
       return;
    }
    AJAXGet(xmlHttp.responseText);
  }
}

function AJAXPostWithGetter(php, params, getter)    // S.A.
{
//    AJAXGET=getter;
//    AJAXSend(php, params, true);
  xmlHttp = GetXmlHttpObject();
  var rrr=parseInt(Math.random()*99999999);  // cache buster
  var url = php+"?rrr="+rrr+"&"+params;
  xmlHttp.onreadystatechange=getter;
  xmlHttp.open("POST",php,true);
  xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xmlHttp.send(params);
}

function AJAXSend(php, params, post) {
  xmlHttp = GetXmlHttpObject();
  var rrr=parseInt(Math.random()*99999999);  // cache buster
  var url = php+"?rrr="+rrr+"&"+params;
  xmlHttp.onreadystatechange=stateChanged;
  if( !post) {
    xmlHttp.open("GET",url,true);
    xmlHttp.send(null);
  }
  else {
    xmlHttp.open("POST",php,true);
    xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
//    xmlHttp.setRequestHeader("Content-length", params.length);
//    xmlHttp.setRequestHeader("Connection", "close");
    xmlHttp.send(params);
  }
}

function stateChangedXML() {
  if( !AJAXGet ) return;
  if (xmlHttp.readyState == 4) { 
    if( xmlHttp.status != 200 ) {
       alert("There was a problem retrieving the XML data:\n" + xmlHttp.statusText);
       return;
    }
    if( !xmlHttp.responseXML ) alert("File not found!");
    else {
      AJAXGet(xmlHttp.responseXML);
    }
  }
}

function AJAXGetXMLFile(doc,ret) {
  AJAXGet = ret;
  xmlHttp = GetXmlHttpObject();
  xmlHttp.onreadystatechange=stateChangedXML;
  xmlHttp.open("GET",doc,true);
  xmlHttp.setRequestHeader("Content-Type", "text/xml");
  xmlHttp.setRequestHeader("Cache-Control","no-cache");
//  xmlHttp.setRequestHeader("If-Modified-Since", new Date(0) ); // no-cache version for IE
// this did not work for Chrome, because the Date(0) string contained Hungarian accented
// letters on Hungarian languge Windowses. Firefox, IE, PaleMoon were fine.
  xmlHttp.setRequestHeader("If-Modified-Since", "Thu Jan 01 1970 01:00:00 GMT" ); // no-cache version for IE
  xmlHttp.send(null);
}

function AJAXSET() {
  AjaxAtWork = 1;
}

function AJAXWORK() {
  if( AjaxAtWork == 1 ) return 1;
  AJAXSET();
  return 0;
}

function AJAXUNSET() {
  AjaxAtWork = 0;
}

function repeatIfAJAXWORK(params, time) {
  if( AJAXWORK() ) {
    setTimeout("repeatIfAJAXWORK('"+params+"',"+time+")",time);
    return;
  }
  
  AJAXUNSET();
  
  var arg = "";
  var tmp = explode("(",params,2);
  
  params = tmp[0];
  if( tmp[1] ) 
    arg = cutLast(tmp[1]);
  
  window[params](arg);
}
