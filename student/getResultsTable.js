// use this in your php file when you want to use getResultsTable.php
// define an object with the following fields for every table you want to use:
//   {
// 	title			appears at top of table
// 	data			full path of CSV file
//	field			use this name and id for the input field of this object
//	table			name and id of the element (e.g. div) for the table
//      prompt			text for promt, when no table is displayed, e.g. 
//					"<br>Írja be a kódját majd nyomjon 'Enter'-t<br>Submit code and press 'Enter'."
//   }


var _table_object = false;

// change input type to password and back
// parameters: new type and name (and id) of input field
function _changeInputTypeTo(newTypeString, field)
{
 // see https://www.universalwebservices.net/web-programming-resources/javascript/change-input-element-type-using-javascript/
   oldObject = document.getElementById(field);
   var newObject = document.createElement('input');
   newObject.type = newTypeString;
   if(oldObject.size) newObject.size = oldObject.size;
   if(oldObject.value) newObject.value = oldObject.value;
   if(oldObject.name) newObject.name = oldObject.name;
   if(oldObject.id) newObject.id = oldObject.id;
   if(oldObject.onkeyup) newObject.onkeyup = oldObject.onkeyup;         
   if(oldObject.onchange) newObject.onchange = oldObject.onchange;
   if(oldObject.className) newObject.className = oldObject.className;
   oldObject.parentNode.replaceChild(newObject,oldObject);
}

function _keyPressedInPasswordField(table_object)
{
  _table_object = table_object;
  if(_table_object.keypressed !== true)
  {
	_table_object.keypressed = true;
	// this does not work everywhere: document.getElementbyId("neptun").type="password";
	_changeInputTypeTo("password", _table_object.field);
	document.getElementById(_table_object.field).focus();
  }
  getMarks();
}

function _showResTable()
{
  if (xmlHttp.readyState == 4 ) 
  {
     if( xmlHttp.status != 200 ) 
     {
           alert("There was a problem retrieving data:\n" + xmlHttp.statusText);
           return;
     }
     document.getElementById(_table_object.table).innerHTML= xmlHttp.responseText == "<br>\n*" ? _table_object.nodata : xmlHttp.responseText;
  }
}

function getMarks()
{   // in atom/student:
  if(document.getElementById(_table_object.field).value.length < 6 ) { return;}
  requestString = "neptun="+document.getElementById(_table_object.field).value+
				"&data="+_table_object.data+".csv"+
				"&title="+_table_object.title;
// az útvonalnak vagy abszolútnak kell lennie, vagy relatívnak ahhoz a könyvtárhoz
// képest, amelyikbenaz ezt a scriptet használó php file van
// Ebben a példában az a fájl a fiz1.php, amihez képest a getResultsTable.php
// két szsinttel feljebb van
AJAXPostWithGetter("../../getResultsTable.php", requestString, _showResTable); 
}

function clearMarks(table_object)
{
  _table_object = table_object;
  document.getElementById(_table_object.table).innerHTML = _table_object.prompt; 
  document.getElementById(_table_object.field).value = "";
   _changeInputTypeTo("text",  _table_object.field);
   _table_object.keypressed = false;
  document.getElementById(_table_object.field).focus();
}

// DEBUG
function debugAlert(str)
{
  document.getElementById("debug").innerHTML=str;
}
