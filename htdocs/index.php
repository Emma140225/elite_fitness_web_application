<?php
/* =========================================================================
   index.php  –  Einstiegspunkt und "Verteiler" der Anwendung
   -------------------------------------------------------------------------
   Aufgabe dieser Datei:
   1. Datenbank-Verbindung einbinden  (includes/db.php)
   2. Header ausgeben                 (includes/header.php)
   3. Den passenden Funktionsbereich laden (eine Datei im Ordner funktionen/)
   4. Footer ausgeben                 (includes/footer.php)

   Welcher Bereich geladen wird, steht in der URL:
       index.php?bereich=mitglieder
   ========================================================================= */

// 1) Datenbank-Verbindung herstellen ($pdo steht danach bereit)
require 'includes/db.php';

// 2) Kopf der Seite ausgeben: HTML-Grundgerüst mit allen Menuepunkten
require 'includes/header.php';

// 3) Welcher Menuepunkt wurde angeklickt?
$bereich = $_GET['bereich'] ?? 'start';

if ($bereich === 'mitglieder') {
    require 'funktionen/mitglieder.php';

} elseif ($bereich === 'rfid') {
    require 'funktionen/rfid.php';

} elseif ($bereich === 'trainer') {
    require 'funktionen/trainer.php';

} elseif ($bereich ==='qualifikationen'){
    require 'funktionen/qualifikationen.php';

} elseif ($bereich === 'vertragsarten') {
    require 'funktionen/vertragsarten.php';

} elseif ($bereich === 'mitgliedschaften') {
    require 'funktionen/mitgliedschaften.php';

} elseif ($bereich === 'kurse') {
    require 'funktionen/kurse.php';

} elseif ($bereich === 'stat_kurse') {
    require 'funktionen/stat_kurse.php';

} elseif ($bereich === 'stat_geraete') {
    require 'funktionen/stat_geraete.php';

} elseif ($bereich === 'stat_mitglieder') {
    require 'funktionen/stat_mitglieder.php';

} else {
    // -------- Startseite (wenn kein Bereich gewaehlt wurde) --------------
    require 'funktionen/default.php';

}

// 4) Fuss der Seite ausgeben
require 'includes/footer.php';
