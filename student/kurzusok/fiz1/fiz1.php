<?php 
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
?>
<!DOCTYPE HTML>
<html>
<head>
    <!--
        az 'oldalak.css' adja meg az oldalak kinézetét 
        e helyett biztos m's lesz a te oldaladon
    -->
    <link rel="stylesheet" type="text/css" href="../../oldalak.css" />

    <?php 
    // ================================================================
    // Innentől kezdve a <head> -ba másolni:
    //       a ../../ a fájlok e könyvtárhoz képesti relatív útvonala
    // ha ezt megváltoztatjuk akkor a 'getResultsTable.js' fájlban
    // is meg kell változtassuk a 'getResultsTable.php' átvonalát
    ?>
    <script type="text/JavaScript" src="../../zoneajax.js"></script>
    <script type="text/JavaScript" src="../../getResultsTable.js"></script>
    
    <?php
        // getResultsTable.css adja meg a táblázat színeit
        // ezt vagy át kell írni, vagy ki kell cserélni valami másra
    ?> 
    <link rel="stylesheet" type="text/css" href="../../getResultsTable.css" />
 <?php
    // a fiz1.js fájlban vannak a táblázatok adatai 
    // ez nyilván más minden tárgyhoz
 ?> 
    <script type="text/JavaScript" src="fiz1.js"></script>
 <?php
    // --------------- idáig --------------------------------------------    
 ?>
</head>

<body>
<div class="spacer"></div>
<div class="kozepre"> 

    <h1>Példa a getResultsTable használatára</h1>

    <div class="content">
        <p>A getResultsTable egy WEB es "rendszer" amellyel a hallgatók eredményeit
        a GDPR-nek megfelelő módon illeszthetjük be saját WEB oldalunkba.</p>
        <p>Használata:</p>
    </div>
    
    <h2 >Jelenlét</h2>
<?php 
    // ================================================================
    // Jelenléti lista
    // Innentől kezdve a <body> -ba másolni. 
    // a táblázat neve ugyanaz, mint ami a fiz1.js-ben!
?>
 <span>Neptun:</span> 
 <input name="neptun-a" id="neptun-a" type=text size=6 value="" onkeyup="_keyPressedInPasswordField(attendance_table)"> 
 <input type="button" id="clear-a" value="Törlés" onclick="clearMarks(attendance_table)"> 
          
 <div name="marks-a" id="marks-a"><br>Írja be a Neptun kódját!</div>
  <?php
    // --------------------Idáig---------------------------------------
  ?>
 <br>

 <?php 
    // ================================================================
    // Jelenléti lista
    // Innentől kezdve a <body> -ba másolni. 
    // a táblázat neve ugyanaz, mint ami a fiz1.js-ben!
    ╨// pontszámok
?>
<span>Neptun:</span> 
 <input name="neptun" id="neptun" type=text size=6 value="" onkeyup="_keyPressedInPasswordField(results_table)"> 
 <input type="button" id="clear" value="Törlés" onclick="clearMarks(results_table)"> 
          
 <div name="marks" id="marks"><br>Írja be a Neptun kódját!</div><br>
 <?php
    // --------------------Idáig---------------------------------------
 ?>

<!-- kozepre vege -->
</div>
</body>
</html>