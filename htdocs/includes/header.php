<?php
/* =========================================================================
   header.php  –  oberer, immer gleicher Teil jeder Seite
   -------------------------------------------------------------------------
   Hier steht:
   - der <head> mit Verweis auf die CSS-Datei (CSS = "Kosmetik")
   - der Seitenkopf (Logo / Titel)
   - die Navigation (Menue)

   Die Navigation ruft immer die index.php auf und uebergibt per
   ?bereich=... den gewuenschten Funktionsbereich. Beispiel:
       index.php?bereich=mitglieder
   ========================================================================= */
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Elite-Fitness – Verwaltung</title>
    <!-- CSS sorgt fuer das Aussehen, nicht fuer die Funktion -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- ====================== HEADER (Struktur = HTML) ===================== -->
<header class="kopf">
    <a class="logo" href="index.php">Elite-Fitness</a>
    <div class="untertitel">Web-Applikation</div>
</header>


<nav class="navigation">
    <ul>
        <li class="dropdown">
            <a href="index.php?bereich=mitglieder" class="dropbtn">Mitglieder</a>
            
            <ul class="dropdown-content">
                <li><a href="index.php?bereich=mitglieder">Mitglieder anzeigen</a></li>
                <li><a href="index.php?bereich=mitglieder&action=neu">Neues Mitglied registrieren</a></li>
            </ul>
        </li>
        <li class="dropdown">
            <a href="index.php?bereich=rfid" class="dropbtn">RFID-Armbänder</a>
            
            <ul class="dropdown-content">
                <li><a href="index.php?bereich=rfid">RFID-Bänder anzeigen</a></li>
                <li><a href="index.php?bereich=rfid&action=neu">Neues Armband registrieren</a></li>
            </ul>
        </li>
        <li class="dropdown">
            <a href="index.php?bereich=trainer" class="dropbtn">Trainer</a>
            
            <ul class="dropdown-content">
                <li><a href="index.php?bereich=trainer">Trainer anzeigen</a></li>
                <li><a href="index.php?bereich=trainer&action=neu">Neuen Trainer registrieren</a></li>
                <li><a href="index.php?bereich=qualifikationen">Qualifikationen anzeigen</a></li>
                <li><a href="index.php?bereich=qualifikationen&action=neu">Neue Qualifikation</a></li>
            </ul>
        </li>
        <li class="dropdown">
            <a href="index.php?bereich=vertragsarten" class="dropbtn">Vertragsarten</a>
            
            <ul class="dropdown-content">
                <li><a href="index.php?bereich=vertragsarten">Vertragsarten anzeigen</a></li>
                <li><a href="index.php?bereich=vertragsarten&action=neu">Neues Vertragsarten registrieren</a></li>
            </ul>
        </li>
        <li class="dropdown">
            <a href="index.php?bereich=mitgliedschaften" class="dropbtn">Mitgliedschaften</a>
            
            <ul class="dropdown-content">
                <li><a href="index.php?bereich=mitgliedschaften">Mitgliedschaften anzeigen</a></li>
                <li><a href="index.php?bereich=mitgliedschaften&action=neu">Neue Mitgliedschaft registrieren</a></li>
            </ul>
        </li>
        <li class="dropdown">
            <a href="index.php?bereich=kurse" class="dropbtn">Kurse</a>
            
            <ul class="dropdown-content">
                <li><a href="index.php?bereich=kurse">Kurse anzeigen</a></li>
                <li><a href="index.php?bereich=kurse&action=neu">Neuen Kurs anlegen</a></li>
            </ul>
        </li>
        <li><a href="index.php?bereich=stat_kurse">Statistik Kurse</a></li>
        <li><a href="index.php?bereich=stat_geraete">Statistik Geräte</a></li>
        <li><a href="index.php?bereich=stat_mitglieder">Statistik Mitglieder</a></li>
    </ul>
</nav>

<!-- ====================== CONTENT-BEREICH (Beginn) ==================== -->
<main class="inhalt">
