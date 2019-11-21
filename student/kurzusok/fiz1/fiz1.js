// egy táblázat a jelenlétekhez
var attendance_table = {                // a teljes fájl neve "kurzusok/fiz1/dat/fiz1j.csv"
    data: "kurzusok/fiz1/dat/fiz1j",    // a 'getResultsTable.php' könyvtárához képest  
    title: "jelenlét",
    field: "neptun-a",
    table: "marks-a",
    nodata: "<br>Nincs adat",
    prompt: "<br>Írja be a Neptun kódját!"
};
// egy táblázat a jegyekhez
var results_table = {                   // a teljes fájl neve "kurzusok/fiz1/dat/fiz1p.csv"
    data: "kurzusok/fiz1/dat/fiz1p",    // a 'getResultsTable.php' könyvtárához képest
    title: "kis ZH-k",
    field: "neptun",
    table: "marks",
    nodata: "<br>Nincs adat",
    prompt: "<br>Írja be a Neptun kódját!"
};
    