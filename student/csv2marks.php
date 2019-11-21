<?php
// csv2marks.php
// this file is used in the suite 'getResultsTable'

// Last modified 2018.12.17.
// bug fix: comment popups are back

// This script creates a HTML table for a single CSV (comma separated values) file
// The file is an UTF8 text file with fields delimited by either commas, or semicolons,
// or TAB characters (only one of these)

// SPECIAL HANDLING: appending the url the GET string n=1 disables sorting and
// displays name of student too. This is intended for myself and not to the world

// USAGE:
// 1. set up parameters $limitsL, $limitsH, arrays for point limits for marks 1..5
//              $limitsL starts with 0!
//          $AllowedFails - this many insufficient test results allowed
//              If not set an 'infinite' number (99999) is allowed
//                      $lang  - language: 0: 'Hungarian' or 1: 'English'
//                      $JMin  - minimal attendance required. If not enough
//                               then no marks will be given
//          $BLimit- sum of points required to use bonus points
//          $pointsTable - display points and marks in a separate table

// 2. Set up styles for classes
//  Style names used by this script (set them anywhere on your page):
//
//      .neptun - neptun code cell
//	.name   - name cell
//      .jelenlet - attendance
//      .mark   - mark cells OK points an semester is invalid
//      .emark  - mark cells: missing or too few points
//      .rmark  - common class for cells
//      .omark  - mark cells: mark 3
//      .jmark  - mark cells: mark 4
//      .lmark  - mark cells: mark 5
//      .graybg - E and O header background - sum header, mark header and sum
//      .restab - result table style
//      .erow   - attributes for even rows in the table
//                to make  background transparent use e.g. background-color:rgba(80,80,80,0.3)
//      .enhance - enhanced display for points and sums
//                 (asterisk after/before points for O and P)

// 3. call function
//           MakeResTable($table_title, $data_file name)
// to show all data OR call
//           MakeTableOfOneStudentData($table_title, $data_file, $neptun)
// to display data for a singel student with neptun code '$neptun"

// Special "neptun kóds":1a2b3C4  shows data for all students without their names
//                       1A2b3C4 shows data for all students with their names


// CSS classes used:

// ------------- CSV file structure -----------
// file must be encoded in UTF-8! To display Hungarian text correctly
// Convert CSV file from Windows' character set (e.g. with the free Notepad++)
//

// 1st line: name of columns, displayed in the table
// 2nd line: type of columns (same number as of column names):
// required parameter in <>, optional in [], text not in <> is verbatim
//  Possible types are:
//           B: (Bónusz) bonus points. Point total must be larger than $BLimit to add
//      the bonus points
//           E: (értékelés) mark, may be missing. Never set any data in this field
//       in the CSV file, as it is calculated by this script
//       MUST come after an "O" field!
//           J: (jelenlét) presence This field may contain one character for absence and
//		another one for presence. 
//			Absence may be denoted by a '-', '0' or 'N'
//			Presence  may be denoted by a '+','1','I','J' or 'Y'
//		J  may optionally followed by any two texts separated by a colon (:) 
//			first text is for Absence ' ', second for Presence
//              any character which is not an absence or presence character is just
//		echoed into the output and not used when adding the presences
//          	e.g. a '?' may mean 'not sure' - maybe came and left after signing)
//
//           K: (kód) Neptun code
//           M: (megjegyzés) remarks, may be missing If a field is followed by a remarks
//			the remarks will be shown in a hint
//           N: (név) name
//           O: (összeg) summation for all points added *before* this and the first points or
//              the previous summa block and may be followed by required point minimum, e.g. O12.4
//      Never set any data in this field in the CSV file, as it is calculated by this script.
//           P: (pont) points may be followed by point minimum and J marking attendance
//                  e.g. P20J means the minimum point required is 20 and this mark counts into
//          the total count of attendances (no separate J is needed)
//           S: (sorszám) ordinal of data in table, may be missing
//  Any type or any data may be PRECEEDED by an asterix (e.g. *P3.5, *12, *apple) which results
//  in an enhanced display (if the style for class .enhance is set in an external style sheet)
//
// Usual order is: [S,]K,N,J,P,M,P,M...P,M,O,E. This is not necessary, but P and S may not be
//      the first field and if E is used an O must also be used BEFORE it!
// 3rd and subsequent lines: data

//
//   a Point type column must contain valid numbers without thousand separators
//   The decimal character must be the correct one for the locale (dot or comma)
//   (same valid for min points in 'P' type above)
//   if it is comma and the delimiter character is also a comma, then it must be quoted by
//   double quotes
//
// The field delimiter character is determined from the *2nd character of the 2nd line*.
// therefore the first field type must not be 'P' or 'O'
// This way no quote character scan problem
//              delimiter is either comma, semicolon or TAB
// each line contains fields delimited by: comma or semicolon or TAB

// Example of a CSV file (commas as delimiters):
/*
Sorszám, Neptun kód, Név, "Első óra jelenlét (+,-)", kis zh1, m,kis zh2,m,Összes pont, Osztályzat
S,K,N,J,P2,M,P2,M,O4,E
1,ABCD123,Kovács András,+,3,"hiányosságok, hibás képlet", 5,,,,
2 EFG1HGR,Zoltán Enikő,-,5,,1,semmit nem írt,,,
 */
 
function CmpNeptun($a,$b)   // $a and $b are Student objects
{
	return strncasecmp($a->data[0],$b->data[0], 6);
}

function CmpName($a,$b)   // $a and $b are Student objects
{
	return strncasecmp($a->data[1],$b->data[1], 6);
}


class Field
{
  public $name;     // field name from 2nd line in file
  public $type;     // field type character
  public $data;     // optional min points (B,E,K,M,N,O,P,S) or texts (J)
  public $enhanced; // array for signalling if this is an enhanced field
}

// Students haz an array  of data
class Student
{
  public $data;     // array from input line split by delimiters: strings for field contents
}
// reads and processes the CSV file
// The field delimiter in the file can be comma, semicolon or TAB
// 1st line in file must contain field names separated by a single delimiter character
// field names may be empty
class CSVFile
{
  public $file_name;
  public $delim;    // field delimiter character from 1st line

  public $fields;   // array of 'Field's
  public $students; // array of student data (class Student) order corresponds to $types

  // globals, used by funcrions below
  public  $nameToo = false;       // also print name of student?
  private $sum = array(0=>0);             // result of summation before the last O field
  private $lacking =array(0=>0);          // number of point (P) fields with too few points
                                          // before the last O field
  private $bonus = 0;                     // sum of all bonuses
  private $jsum = 0;                      // attendance count
  private $wasRemark = false;             // remark was the next field
  private $mark = 0;              // 0..6 - may depend on bonus points, 1-5 mark, 0: lacking, 6: can't have a mark (few attendances)
  private $classes = array();         // class strings for all fields for one line
  private $row = 0;           // row # - index of student
  private $min_req = 0;           // minimum points required


  private function DieHard($msg)
  {
    die("$msg - hibás pontszám fájl");
  }

  private function GetDelim($line)
  {
    $this->delim = false;   // default: none
    for($i = 0; $i < strlen($line); ++$i)
    {
      if($line[$i] == "'" || $line[$i] == '"')      // skip quoted string
      {
          $q = $i++;
          while($i < strlen($line) )
          {
              if( ($line[$i] == "'" || $line[$i] == '"') && ($line[$i] == $line[$q] ) )
              {
                  ++$i;
                  break;
              }
              ++$i;
          }
          continue;
      }
      if($line[$i] == "," || $line[$i] == ";" || $line[$i] == "\t") // then delimiter found
      {
          $this->delim = $line[$i];
          break;
      }
    }
    if($this->delim === false)
         DieHard($this->file_name);

    return $this->delim;
  }

  private function Index($type) // index of field in array by field type character
  {
    for($i =0 ; $i < count($this->fields); ++$i)
    {
        if($this->fields[$i]->type == $type)
            return $i;
    }
    return false;
  }

  private function SplitLine($line) // split line using delimiter character
  {                                   // because line may contain quoted delimiters
                                      // this is not a simple split
                                      // EXPECTS: line with delimiters
                                      // RETURNS: array of white space trimmed
                                      //          sections in line

    $len = strlen($line);
    $sections = array();
        // cut up the line into sections
    $n = 0; // section index
    $i = 0; // loop index for start of section string
    $k = 0; // loop index for end of section
    while($i < $len)
    {
        while( $k < $len )
        {
          $ch = $line[$k];
          if( ($ch == "\"") || ($ch == "'") )
          {
        if($k == $i)    // quote at start of section
        {
            $k++;   // skip quote character
            $i++;   // skip starting quote character
        }
        while( ($k < $len) && ($line[$k] != $ch))
        {
            if($line[$k] == '\\')   // escape character
                 ++$k;
                        ++$k;
        }
                // here we are at the closing quote character
                // if the next character is the delimiter or the line ends
                // the section is determined
        if($k+1 < $len && $line[$k+1] == $this->delim)
        {
            $sections[$n] = substr($line, $i, $k-$i);
            ++$k;       // skip quote character to delimiter
            ++$n;       // next section
            break;
        }
                ++$k;       // skip quote character
      }
      else if($ch == '\\')  // escape character?
      {
        ++$k;
          }
          else if( ($ch == $this->delim) || ($k == $len-1)) // delimiter or EOL found?
          {
        $sections[$n] = trim( substr($line, $i, $k-$i) );
        ++$n;       // next section
        break;      // leave loop for $k
      }
      if($ch == $this->delim)
      {
        ++$n;
        break;
      }
      ++$k;
    }
    $i = ++$k;  // after delimiter or line end
    }
    return $sections;
  }

  private function GetFieldNames($line) // called for first line of file
  {
        $this->GetDelim($line);
        $lin_arr = $this->SplitLine($line);
    $this->fields = array();

    for($i = 0; $i < count($lin_arr); ++$i)
    {
        $field = new Field;
        $field->name = $lin_arr[$i];
        $this->fields[] = $field;
// DEBUG        error_log("i:$i, Field name: {$field->name}");
    }
  }

  private function TextForAttendance($field_index)  // returns correct text from header or default text for actual student
  {
    $field = $this->fields[$field_index];
    if($field->type != "J") // safety first
      return FALSE;
    if($field->data != "")  // 2 texts separated by ':' E.g. "JPresent:Absent"
    {
      if(strpos($field->data, ":") !== FALSE)
      {
        $v = preg_split("/:/", $field->data);
        $v[0] = trim($v[0]); $v[1] = trim($v[1]);
      }
    }
    else
    {
      $v[0] = " "; $v[1] = "+";     // default
    }

    $student = $this->students[$this->row];
    $act = $student->data[$field_index];

    if($act != false)
    {    
      if(strpos("-0Nn",$act) !== false)
        return $v[0];
      if(strpos("+1IiYy", $act) !== false)
         return $v[1];
    }
       
    return $act;	// default text (not +-01NIY)
  }

  private function EmitAttendance($field_index)
  {
    // DEBUG
//       myDEBUG("***  ");
       
    $act = $this->students[$this->row]->data[$field_index];
    if($act)
      $act = $act[0]; // 2018.12.17: check only first character for color
    if($act != false && strpos("-0Nn",$act) !== false)
      $act = "absent";
    else
      $act = "present";
    
    echo "<td class=\"jelenlet $act\" {$this->EmitComment($field_index)} >".$this->TextForAttendance($field_index)."</td>";
  }

  // called for 2nd line in file
  private function LineToFields($line)  // cuts up lines to sections and
  {                 // returns array of 'Field's
                                        //          set up fields, they may contain *
                                        // EXPECTS: line with delimiter separated sections
                                        //          $fields exists and has the field names
                                        // RETURNS: array of fields
    $lin_arr = $this->SplitLine($line); // $line now will be an array
    if(count($lin_arr) != count($this->fields) )
        $this->DieHard("Field count  error on $this->file_name");

    $field = new Field;

    for($i = 0; $i < count($lin_arr); ++$i) // same number of sections for all files
    {
        // $lin_arr[$i];    // single letter or letter with '*' and maybe parameter

        if($lin_arr[$i] != "")
        {
		if($lin_arr[$i][0] == "*")
		{
		   $field->enhanced = true;
		   $lin_arr[$i] = substr($lin_arr[$i], 1);
		}
		else if($lin_arr[$i][strlen($lin_arr[$i])-1] == "*")
		{
		   $field->enhanced = true;
		   $lin_arr[$i] = substr($lin_arr[$i], 0, strlen($lin_arr[$i])-1);
		}
		$field->type = $lin_arr[$i][0];     // type character
		$field->data = trim(substr($lin_arr[$i], 1));   // required points and an optional "J" for attendance
        }
        else
        {
            unset($field->type);        // may cause warnings in log:  Undefined property: Field::$type in...
            unset($field->data);
        }

        $this->fields[$i]->type = $field->type;
        $this->fields[$i]->data = $field->data;
// myDEBUG_r("   $i\->field=", $field);        
//         $this->fields[$i] = $field;
    }
  }

  // Starting from the 3rd line
  private function LineToStudent($line) // returns student data
  {
      $student = new Student;
      $student->data = $this->SplitLine($line);
      return $student;
  }

  private function ReadFile($name) // reads named file and creates student records
  {
    global $JMin, $BLimit, $AllowedFails;
//myDebug("j:$JMin, BL:$BLimit, AF:$AllowedFails<br>"    );
//     myDEBUG("===============<br>File: $name<br>");
    if(!isset($JMin))
      $JMin = 0;
    if(!isset($BLimit))
      $BLimit = 0;
    if(!isset($AllowedFails))
      $AllowedFails = 99999;    // infinity!

//    myDebug("file:'$name,"'\n");

    $this->file_name = $name;
    $f = fopen($name, "r");
    if($f == false)
    {
       echo "*";
       die("'"+$name+"' File megnyitási hiba"); 
    }
//    myDebug(" - opened OK\n");
    $line = fgets($f);  // 1st line: field names and delimiter character
    $this->GetFieldNames($line);
    $line = fgets($f);  // 2nd line: field definitions
    $this->LineToFields($line);
    // loop for student data

    $this->students = array();
    while( !feof($f))
    {
      $line = fgets($f);
      trim($line);
      if($line === FALSE || $line=="")
         continue;
      $this->students[] = $this->LineToStudent($line);
    }
    fclose($f);
  }

  private function EmitTableHeader($title) // emits a HTML table header
  {
    global $lang;

    $cnt = 0;   // column count (name and comment columns don't count
    for($i = 0; $i < count($this->fields); ++$i)
      if(strpos("NM", $this->fields[$i]->type) == FALSE )
      {
        ++$cnt;
      }
// table:
//    +-----------------------------------------------------+
//    |                table title                          |
//    +--------+---------+---------+---------+--------------+
//    | neptun | point 1 | point 2 | point 3 | sum column   |
//    +--------+---------+---------+---------+--------------+
//    |        |         |         |         |              |
    echo "<table class='restab'><thead><tr>";
    echo "<th colspan=$cnt>$title</th></tr>\n<tr>";
            // first field is ordinal if present then neptune code
        if($this->Index("S") !== false)
             echo "<th class=\"neptun\">&nbsp;</th>";

        echo "<th class=\"neptun\">".$this->fields[$this->Index("K")]->name."</th>";
        // when used in debug mode
        // second column is the name of the student
        if($this->nameToo)
           echo "<th class=\"name\">".$this->fields[$this->Index("N")]->name."</th>";
    // table head for J
//    $presence = $this->fields[$this->Index("J")]->name;
// error_log(" $i ->{$this->fields[$this->Index("J")]->name}"  );    

    for($i = 0; $i < count($this->fields); ++$i)
    {
//error_log(" $i ->{$this->fields[$i]->name}"  );
        $hclass = "";
        $actfield =  $this->fields[$i];
        if($actfield->enhanced)
           $hclass .= " enhance";
        if(strpos("SKNM", $actfield->type[0]) == FALSE)
        {
            if($actfield->type[0] == "E")
                echo "<th class=\"graybg$hclass\">".$actfield->name."</th>";
            else if($actfield->type[0] == "O")
                echo "<th class=\"graybg$hclass\">".$actfield->name."</th>";
            else if($actfield->type[0] == "J")
                echo "<th>".$actfield->name."</th>";
//                echo "<th style=\"cursor:pointer\" title=\"".$presence."\">".$actfield->name."</th>";
            else if($hclass != "")
                    echo "<th class=\"$hclass\">".$actfield->name."</th>";
            else
                echo "<th>".$actfield->name."</th>";
        }
// myDEBUG_r(" $i ->", $actfield  );
    }
    echo "</tr></thead>\n<tbody>\n";
  }

  private function Mark()
  {
    global $limitsL, $BLimit, $AllowedFails, $JMin; // lower point limit, bonus limit and fail limit

    $sum = $this->sum[$this->actSum-1];             // actSum was incremented by previous O field
    if($sum >= $BLimit)
      $sum += $this->bonus;

    if($this->jsum < $JMin)
      return 6;
    if($this->lacking[$this->actSum-1] > $AllowedFails)
       return 0;

    $which = 0; // index in point limit tables: 0..6
    for(; $which < 5; ++$which)
    if($limitsL[$which] > $sum)
        break;
//myDEBUG("Mark($this->sum[$this->actSum], $this->bonus)=$which<br>");
    return $which;      // returns 1,2,3,4,5 mark, 0: lacking , 6: too few attendances
  }

  private function ProcessStudentData()      // one line of table
  {
    global $JMin, $BLimit, $AllowedFails;

    $student = $this->students[$this->row];

    $this->actSum = 0;      // index of actual summation result
    $this->sum[0] = 0;      // result of summation
    $this->bonus = 0;       // sum of all bonuses
    $this->jsum = 0;        // attendance count
    $this->wasRemark = false;  // remark was the next field
    $this->lacking[0] = 0;  // number of point (P) fields with too few points
    $this->mark = 0;        // 1..5 - may depend on bonus points
    $this->classes = array();   // class strings for all fields

    for($i = 0; $i < count($this->fields); ++$i)    // go through all field types for student
    {                       // start of TD tag
      $class4field = "";
      $typ = $this->fields[$i]->type;
      if(strpos("KNMS", $typ)!==FALSE)  // do not calculate for these
            continue;
      $act = $student->data[$i];    // if not false then counts into attendance
                                    // but it may be  false or an empty string
      $attendance = 0;		    // used for "P" when the option J is set for it
      if($typ != "J")
      {
	$pos = strpos($this->fields[$i]->data, "J"); // J may be appended to point string 
	if($pos !== FALSE)                           // to signal this field's value counts
	{					     // as an attendance too
		$attendance = 1;
		$s = $this->fields[$i]->data;
		$s = substr($this->fields[$i]->data, 0, -1);   // cut "J" from end of string
		$this->min_req = $s;
	}
	else
		$this->min_req = $this->fields[$i]->data; // may return false or an empty string
	//myDebug("jelenlet + min pontok = ".$this->min_req."<br>");
      }
      else
        $this->min_req = $this->fields[$i]->data; // may return false or an empty string
        
      switch($typ)
      {
	case "B" : $this->bonus += $act; break;             // sum bonus points
	case "E" : $this->mark = $this->Mark();             // marks
		   $class4field = $this->ClassForMark();
		   break;
	case "J" : if(strlen($act) !=0 && strpos("+1IiYy", $act) )              // attendance field: student was present
			 ++$this->jsum;                     // number of attendances
		   break;
	case "K": $class4field .= "neptun"; break;              // Neptun code
	case "M": break;                            // Remark
	case "N": break;                            // name of student
	case "O": if( ($this->lacking[$this->actSum] > $AllowedFails) || ($this->sum[$this->actSum] < $this->min_req) )     // sum of points
			$class4field = "emark";
//myDebug("$i, Neptun: ".$student->data[0].", jelenlet: ".$this->jsum.", sum: ".$this->sum[$this->actSum]."<br>");
//myDebug(" O: $this->sum[$this->actSum], lacking:".$this->lacking[$this->actSum].", min_req=".$this->min_req.", class=$class4field<br>");
		  ++$this->actSum;      // next summation comes into this element of sum[]
		  $this->sum[$this->actSum] = 0;
		  $this->lacking[$this->actSum] = 0;
		  break;
	case "P": if($act === FALSE || $act === "" || $act < $this->min_req)// points
			++$this->lacking[$this->actSum];   // empty cell or too small?
		  if($act !== FALSE && $act !== "")
		  {
			   $this->jsum += $attendance;
			   $this->sum[$this->actSum] += $act;
                  }			   
		  $class4field = ($act < $this->min_req ? "emark" : "mark");
//myDebug("Row: ".$this->row.", Field #$i, type = $typ".$this->fields[$i]->data.", Neptun: ".$student->data[0].", P: $act, lacking:".$this->lacking[$this->actSum].", min_req=".$this->min_req.", class=$class4field<br>");
		  break;
        case "S":  break;                           // row number - when present

      } // switch
      if($this->fields[$i]->enhanced)
	$class4field .= " enhanced";
      $this->classes[$i] = $class4field;
    } // for all fields
  }

  private function EmitMark($i) // $sum is resulting points rounded to nearest whole number , $jsum: number of attendances
  {
    global $lang;

    $htexts = array("Hiány!","(elégtelen)","(elégséges)","(közepes)","(jó)","(jeles)", "Nem kaphat jegyet");
    $etexts = array("Problem!","(insufficient)","(sufficient)","(satisfactory)","(good)","(excellent)","Invalid Semester ");
    $texts = array($htexts, $etexts);

    echo "<td class=\"".$this->classes[$i]."\"";
    if($this->mark > 0 && $this->mark < 6)  // marks 1.. 5
       echo $this->EmitComment($i).">".$this->mark." ".$texts[$lang][$this->mark];
    else
        echo ">".$texts[$lang][6];
    echo "</td>";

  }

  private function ClassForMark()
  {
    global $limitsL, $JMin, $AllowedFails;
    $classes = array("rmark", "emark", "omark", "omark", "jmark", "lmark", "mark");
    if($this->jsum < $JMin)
      return $classes[6];

    if($AllowedFails < $this->lacking[$this->actSum])
        return $classes[1];         // failed

    return $classes[$this->mark];
  }

  private function EmitComment($i)
  {
    $student = $this->students[$this->row];
// DEBUG
//myDEBUG_r("M",$this->fields, -1);
//myDEBUG_r("M",$student->data, -1);_  

    if( ($i+1 >= count($this->fields)) || ($this->fields[$i+1]->type != "M"))
      return "";
    $act = $student->data[$i+1];
    if($act !== FALSE && $act !== "")
    {
//myDEBUG_r(" M-mező ($i+1):",$student->data, -1);      
      return " style=\"cursor:pointer\" title=\"$act\" ";
     }
  }

  private function EmitStudentData()    // one line of table
  {
    $student = $this->students[$this->row];
//myDEBUG_r("Student data: ",$student);
        // Set row color for alternating rows
	if($this->row & 1)          // set even and odd row colors
	  echo "<tr class=\"erow\">";       // but $row starts at 0
	else                    // therefore the odd row is the even row in table!
	  echo "<tr>";
	// display table row for student
	// first: ordinal, next neptun code next student name (usually hidden)
	$ix = $this->Index("S");
	if($ix !== false)
	  echo "<td class=\"neptun\">$row</td>";

    $i = $this->Index("K");	// neptun code
    echo "<td class=\"neptun\" {$this->EmitComment($i)}>".$student->data[$i]."</td>";

    $i = $this->Index("N");	// name
    if($this->nameToo)
        echo "<td class=\"neptun\" {$this->EmitComment($i)}>".$student->data[$i]."</td>";
//myDEBUG("Name:".$student->data[$i]."\n");
    $this->actSum = 0;  // index of sum to print out
    for($i = 0; $i < count($this->fields); ++$i)    // go through all field types for student
    {                       // start of TD tag
      $typ = $this->fields[$i]->type;
      if(strpos("KNMS", $typ)!==FALSE)      // K,N,S printed, M - is printed after P or O
                continue;
      $act = $student->data[$i];            // points or presence
                                            // may be  false or an empty string
      if($act === FALSE || $act === "")
            $act = "&nbsp;";
//myDebug("Neptun: ".$student->data[0].", jelenlet: ".$this->jsum.", sum: ".$this->sum[$this->actSum]."<br>");
      switch($typ)
      {
        case "B": echo "<td class=\"".$this->classes[$i]."\">$act</td>"; break;
        case "E": $this->EmitMark($i);break; // $i  - field_index needed for comment!
        case "J": $this->EmitAttendance($i); break;
        case "O": echo "<td class=\"".$this->classes[$i]."\"".$this->EmitComment($i).">".$this->sum[$this->actSum]."</td>";
                  ++$this->actSum;
                  break;
        case "P": echo "<td class=\"".$this->classes[$i]."\"".$this->EmitComment($i).">$act</td>"; break;
      } //switch
    } // loop for all fields
    echo "</tr>\n";
  }

  private function Sort()
  {
// DEBUG
//for($i=0; $i < count($this->students); ++$i)
//myDEBUG_r("", $this->students[$i]);
    return;
    
//    myDEBUG("Sorting #{$this->nameToo}<br>");
    if($this->nameToo)
      usort($this->students, "CmpName");
    else
      usort($this->students, "CmpNeptun");
// DEBUG
//for($i=0; $i < count($this->students); ++$i)
//    myDebug($this->students[$i]->fields[$this->Index('K')]->data." ".$this->students[$i]->fields[$this->Index('N')]->data."<br>");
  }
  
  public function EmitPointsTable()
  {
    global $lang, $limitsL,$limitsH;

    $names = array("Pontok","Points", "Jegy", "Mark");

    echo "<br><table border=2px>".
         "<thead>".
         "<tr><th>".$names[$lang]."</th><th>".$names[$lang + 2]."</th></tr>".
    "<thead><tbody>".
    "<tr><td>".$limitsL[0]."  -". $limitsH[0]."</td><td>1</td></tr>".
    "<tr><td>".$limitsL[1]."  -". $limitsH[1]."</td><td>2</td></tr>".
    "<tr><td>".$limitsL[2]."  -". $limitsH[2]."</td><td>3</td></tr>".
    "<tr><td>".$limitsL[3]."  -". $limitsH[3]."</td><td>4</td></tr>".
    "<tr><td>".$limitsL[4]."  -". $limitsH[4]."</td><td>5</td></tr>".
    "</tbody></table><br>\n";
  }


  public function EmitTable($table_title, $data_file)
  {
//      if($_GET["n"] == "1")
//        $this->nameToo = true;

      $this->ReadFile($data_file);
      $this->Sort();  // only when no 'n=1' argument (no name list)
      
      echo "<div class=\"table\">\n";
      $this->EmitTableHeader($table_title);

      for($this->row = 0; $this->row < count($this->students); ++$this->row)
      {
        $this->ProcessStudentData();
        $this->EmitStudentData();
      }
      echo "</tbody></table>\n".
           "</div>";	// end of "table" div      
      
//myDEBUG("all data printed\n");      
  }

  public function EmitOneStudentTable($table_title, $data_file, $neptun)
  {
//myDEBUG("student data printing start\n");      
//    if($_GET["n"] == "1")
//        $this->nameToo = true;

    $this->ReadFile($data_file);
    $this->Sort();  // only when no 'n=1' argument (no name list)
//   myDebug("sorted");

    for($this->row = 0; $this->row < count($this->students); ++$this->row)
    {
      $student = $this->students[$this->row];
    
      for($fi=0; $fi < count($this->fields); ++$i)    // go through all field types for student
      {
        $this->ProcessStudentData();
	if($this->fields[$fi]->type == "K")
	{
// DEBUG
//myDEBUG("Searched: ".strtolower($neptun).", data: ".strtolower($student->data[$fi])."<br>\n");
	    
          if( strtolower($student->data[$fi]) == strtolower($neptun)) 
          {
            echo "<div class=\"table\">\n";
            $this->EmitTableHeader($table_title);
            $this->EmitStudentData();
            echo "</tbody></table>\n".
                 "</div>"; // end of div.table
//myDEBUG("student data printed\n");      
            return;
          }
          else
            break;		// NEPTUN field found do not check others
        }
      }
    }
    echo "*";
  }

} // end of CSVFile object

function MakeResTable($table_title, $data_file)
{
  global $pointsTable;

  $table = new CSVFile;
  if($pointsTable)
    $table->EmitPointsTable();
  $table->EmitTable($table_title, $data_file);
}

function MakeTableOfOneStudentData($table_title, $data_file, $neptun)
{
  global $pointsTable, $specialCodeForListWithNames, $specialCodeForList;
  
  // DEBUG
//  myDebug("title=$table_title, file=$data_file, code=$neptun");

  $table = new CSVFile;
  if($pointsTable)
    $table->EmitPointsTable();
  if($neptun==$specialCodeForListWithNames)
     $table->nameToo = true;
  if( ($neptun==$specialCodeForList) || ($neptun==$specialCodeForListWithNames))
  {
     $table->EmitTable($table_title, $data_file);
  }
  else
     $table->EmitOneStudentTable($table_title, $data_file, $neptun);
}

// DEBUG prints
//-----------------------------------------------------------------
function myDEBUG($str,$title="")
{
    $f = fopen("/tmp/csv2marks.log","a");
    fprintf($f,$_SERVER['REMOTE_ADDR']." -- ". $title.$str);
    fclose($f);

/*    if($_SERVER['REMOTE_ADDR'] == "152.66.103.3" || $_SERVER['REMOTE_ADDR'] == "78.139.3.234")
    {
        echo " ".$str;
    }
*/
}

// prints a 1D array like it was 2D with $col_cnt columns in a single row
function myDEBUG_r($title = "", $arr, $col_size=-1)
{
    $f = fopen("/tmp/csv2marks.log","a");
    fprintf($f,$title.print_r($arr));
    fclose($f);
/*

    if($_SERVER['REMOTE_ADDR'] == "152.66.103.3" || $_SERVER['REMOTE_ADDR'] == "78.139.3.234")
    {
       echo "$title<br><pre>\n";
       print_r($arr);
       echo "</pre><br>----------<br>";
    }
 */   
}
