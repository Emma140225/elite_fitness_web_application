<?php
/* =========================================================================
   db.php  –  Datenbank-Konnektivität
   -------------------------------------------------------------------------
   Stellt eine zentrale Verbindung zur MySQL-Datenbank her.
   Es wird von jeder Funktions-Datei per include eingebunden.
   ========================================================================= */

// --- Verbindungs-Parameter
$db_host = 'localhost';
$db_name = 'elite_fitness';
$db_user = 'root';
$db_pass = '';          
$db_charset = 'utf8mb4';

$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$db_charset";

// --- Optionen fuer PDO ---------------------------------------------------
$optionen = [
    // Fehler als Exceptions melden -> bessere Fehlersuche
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // Ergebnisse standardmaessig als assoziatives Array (Spaltenname => Wert)
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
 
];

// --- Verbindung aufbauen -------------------------------------------------
try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $optionen);
} catch (PDOException $e) {
    // Im Schulbetrieb zeigen wir die Meldung an. Auf einem echten Server
    // wuerde man die Meldung NICHT dem Besucher zeigen, sondern loggen.
    die('Datenbank-Verbindung fehlgeschlagen: ' . $e->getMessage());
}
