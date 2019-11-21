<?php
// Ez a script hozza létre és adja vissza az eredmény táblázatot
// amit a zoneajaz.js kér le POST-tal
// INPUT:  "neptun" -> neptun vagy speciális kód
//         "data"   -> a CSV file neve ennek a fájlnak a könyvtárához képest
//         "title"  -> a táblzat címe
$neptun=$_POST["neptun"];
$data_file=$_POST["data"];
$table_title=$_POST["title"];
// Ezt a kettőt át kell írni valami másra!
// Javaslom legyen min. egy karakterrel hosszabb, mint a neptun kódok
$specialCodeForList="Abcdefg";
$specialCodeForListWithNames="AbcdefG";

include "csv2marks.php";

//echo getcwd()";
// response when no data exists is "<br>\n*"
echo "<br>\n";
MakeTableOfOneStudentData($table_title, $data_file, $neptun); // returns '*' when no data exists

?>