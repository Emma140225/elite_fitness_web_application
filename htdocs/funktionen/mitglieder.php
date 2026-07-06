<?php
/* =========================================================================
   funktionen/mitglieder.php  –  Funktionsbereich "Mitglieder"
   -------------------------------------------------------------------------
   Diese Datei ist das "Vorbild" fuer alle anderen Funktionsbereiche.
 
   WICHTIG (Sicherheit & saubere Trennung):
   - Datenbank-Zugriffe besser mit "Prepared Statements" (gegen SQL-Injection).
   - Ausgabe von Daten daher immer durch htmlspecialchars() schuetzen (gegen XSS).
     Dafuer gibt es unten die kleine Hilfsfunktion h() - dadurch wird der Code übersichtlicher.
   ========================================================================= */

// Hilfsfunktion: macht Text fuer die HTML-Ausgabe sicher und den Code leserlicher und kuerzer
// (verhindert, dass z.B. "<script>" im Datenfeld Schaden anrichtet)
function h($wert): string {
    return htmlspecialchars((string)$wert, ENT_QUOTES, 'UTF-8');
}

// Wurde ein bestimmtes Mitglied angeklickt? id aus der URL lesen
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Action Befehl aus URL prüfen um Daten zu bearbeiten
$action = $_GET['action'] ?? '';
$istBearbeiten = ($action === 'bearbeiten');

// Löschen von Mitgliedern
if ($action === 'loeschen' && $id > 0) {
    $sql = "DELETE FROM mitglied WHERE mitglied_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]); 

    // PopUp zur Bestätigung anzeigen und Weiterleitung zur Mitgliederliste
    echo "<script>
        alert('Das Mitglied wurde erfolgreich gelöscht.');
        window.location.href='index.php?bereich=mitglieder';
        </script>";

    return;
}

// Bearbeitungsmodus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id > 0) {
    $anrede       = $_POST['anrede'] ?? '';
    $vorname      = $_POST['vorname'] ?? '';
    $nachname     = $_POST['nachname'] ?? '';
    $geburtsdatum = $_POST['geburtsdatum'] ?? '';
    $geschlecht   = $_POST['geschlecht'] ?? '';
    $strasse      = $_POST['strasse'] ?? '';
    $hausnummer   = $_POST['hausnummer'] ?? '';
    $plz          = $_POST['plz'] ?? '';
    $ort          = $_POST['ort'] ?? '';
    $telefon      = $_POST['telefon'] ?? '';
    $mobil        = $_POST['mobil'] ?? '';
    $email        = $_POST['email'] ?? '';

    $sql = "UPDATE mitglied 
            SET anrede = ?, 
                vorname = ?, 
                nachname = ?, 
                geburtsdatum = ?, 
                geschlecht = ?, 
                strasse = ?, 
                hausnummer = ?, 
                plz = ?, 
                ort = ?, 
                telefon = ?, 
                mobil = ?, 
                email = ? 
            WHERE mitglied_id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $anrede, 
        $vorname, 
        $nachname, 
        $geburtsdatum, 
        $geschlecht, 
        $strasse, 
        $hausnummer, 
        $plz, 
        $ort, 
        $telefon, 
        $mobil, 
        $email, 
        $id
    ]);

    echo "<script>
        alert('Die Änderungen wurden erfolgreich gespeichert.');
        window.location.href = 'index.php?bereich=mitglieder&id=" . $id . "';
    </script>";

    return;
}

//Neues Mitglied in der Datenbank anlegen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'neu') {
    $anrede       = $_POST['anrede'] ?? '';
    $vorname      = $_POST['vorname'] ?? '';
    $nachname     = $_POST['nachname'] ?? '';
    $geburtsdatum = $_POST['geburtsdatum'] ?? null; // Wenn leer, als NULL in die DB schreiben
    $geschlecht   = $_POST['geschlecht'] ?? '';
    $strasse      = $_POST['strasse'] ?? '';
    $hausnummer   = $_POST['hausnummer'] ?? '';
    $plz          = $_POST['plz'] ?? '';
    $ort          = $_POST['ort'] ?? '';
    $telefon      = $_POST['telefon'] ?? '';
    $mobil        = $_POST['mobil'] ?? '';
    $email        = $_POST['email'] ?? '';

    //Beim Testen sind Fehler aufgetreten wenn das Geburtsdatum leer war, deswegen wird hier ein leeres Geb auf NULL gesetzt
    if (empty($geburtsdatum)){
        $geburtsdatum = null;
    }

    $sql = "INSERT INTO mitglied (
                anrede, vorname, nachname, geburtsdatum, geschlecht, 
                strasse, hausnummer, plz, ort, telefon, mobil, email
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $anrede, 
        $vorname, 
        $nachname, 
        $geburtsdatum, 
        $geschlecht, 
        $strasse, 
        $hausnummer, 
        $plz, 
        $ort, 
        $telefon, 
        $mobil, 
        $email
    ]);

    echo "<script>
        alert('Das neue Mitglied wurde erfolgreich angelegt.');
        window.location.href = 'index.php?bereich=mitglieder';
    </script>";
    return;
}

// Neues Mitglied anlegen
if ($action === 'neu') {
    ?>
    <h1>Neues Mitglied registrieren</h1>
    <p>Bitte füllen Sie die Felder aus, um ein neues Mitglied im System anzulegen.</p>

    <form method="POST" action="index.php?bereich=mitglieder&action=neu">
        
        <div class="feld">
            <label>Anrede</label>
            <input type="text" name="anrede" placeholder="z. B. Frau / Herr / Divers">
        </div>
        <div class="feld">
            <label>Vorname *</label>
            <input type="text" name="vorname" required placeholder="Vorname des Mitglieds">
        </div>
        <div class="feld">
            <label>Nachname *</label>
            <input type="text" name="nachname" required placeholder="Nachname des Mitglieds">
        </div>
        <div class="feld">
            <label>Geburtsdatum</label>
            <input type="date" name="geburtsdatum">
        </div>
        <div class="feld">
            <label>Geschlecht</label>
            <input type="text" name="geschlecht" placeholder="z. B. w / m / d">
        </div>
        <div class="feld">
            <label>Straße</label>
            <input type="text" name="strasse" placeholder="Straße">
        </div>
        <div class="feld">
            <label>Hausnummer</label>
            <input type="text" name="hausnummer" placeholder="Nr.">
        </div>
        <div class="feld">
            <label>PLZ</label>
            <input type="text" name="plz" placeholder="5-stellige PLZ">
        </div>
        <div class="feld">
            <label>Ort</label>
            <input type="text" name="ort" placeholder="Wohnort">
        </div>
        <div class="feld">
            <label>Telefon</label>
            <input type="text" name="telefon" placeholder="Festnetznummer">
        </div>
        <div class="feld">
            <label>Mobil</label>
            <input type="text" name="mobil" placeholder="Mobilnummer">
        </div>
        <div class="feld">
            <label>E-Mail</label>
            <input type="email" name="email" placeholder="beispiel@domain.de">
        </div>

        <div class="form-aktionen">
            <button type="submit" class="btn-save">Speichern</button>
            <a href="index.php?bereich=mitglieder" class="btn-cancel">Abbrechen</a>
        </div>
        
    </form>
    <?php
    return; 
}

// Detailansicht des gewählten Mitglieds aufrufen
if ($id > 0) {

    // Genau diesen einen Datensatz mit 'id = x' holen (Prepared Statement, Platzhalter ?)
    $sql = "SELECT * FROM mitglied WHERE mitglied_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $mitglied = $stmt->fetch(); 

    // Besser erst pruefen, ob es das Mitglied ueberhaupt gibt...
    if (!$mitglied) {
        echo '<h1>Mitglied</h1>';
        echo '<p class="hinweis">Kein Mitglied mit dieser ID gefunden.</p>';
        echo '<p><a href="index.php?bereich=mitglieder">&laquo; Zurück zur Liste</a></p>';
        return;   // Funktionsdatei "Mitglieder" hier beenden
    }
    ?>

    <h1>Mitglied – Detailansicht</h1>
 
    <!-- Formular nur zur ANZEIGE: readonly 
    Durch Drücken von Bearbeiten, wird das Formular von Readonly auf bearbeiten gesetzt
    Mit Speichern können die geänderten Daten in der DB gesichert werden, der Bearbeitungsmodus
    wird durch den action Tag in der <form> automatisch beendet-->
    <form class="datensatz-formular" method="POST" action="index.php?bereich=mitglieder&action=bearbeiten&id=<?= (int)$id ?>">
        <div class="feld">
            <label>Mitglieds-ID</label>
            <input type="text" value="<?= h($mitglied['mitglied_id']) ?>" readonly>
        </div>
        <div class="feld">
            <label>Anrede</label>
            <input type="text" name="anrede" value="<?= h($mitglied['anrede']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>
        <div class="feld">
            <label>Vorname</label>
            <input type="text" name="vorname" value="<?= h($mitglied['vorname']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>
        <div class="feld">
            <label>Nachname</label>
            <input type="text" name="nachname" value="<?= h($mitglied['nachname']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>
        <div class="feld">
            <label>Geburtsdatum</label>
            <input type="date" name="geburtsdatum" value="<?= h($mitglied['geburtsdatum']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>
        <div class="feld">
            <label>Geschlecht</label>
            <input type="text" name="geschlecht" value="<?= h($mitglied['geschlecht']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>
        <div class="feld">
            <label>Straße</label>
            <input type="text" name="strasse" value="<?= h($mitglied['strasse']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>
        <div class="feld">
            <label>Hausnummer</label>
            <input type="text" name="hausnummer" value="<?= h($mitglied['hausnummer']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>
        <div class="feld">
            <label>PLZ</label>
            <input type="text" name="plz" value="<?= h($mitglied['plz']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>
        <div class="feld">
            <label>Ort</label>
            <input type="text" name="ort" value="<?= h($mitglied['ort']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>
        <div class="feld">
            <label>Telefon</label>
            <input type="text" name="telefon" value="<?= h($mitglied['telefon']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>
        <div class="feld">
            <label>Mobil</label>
            <input type="text" name="mobil" value="<?= h($mitglied['mobil']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>
        <div class="feld">
            <label>E-Mail</label>
            <input type="text" name="email" value="<?= h($mitglied['email']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>

        <!-- Buttons zur Mitgliederbearbeitung, der Speichern Button wird nur im Bearbeitungsmdus angezeigt -->
        <div class="form-aktionen">
        <?php if ($istBearbeiten): ?>
            <button type="submit" class="btn-save">Änderungen speichern</button>
            <a href="index.php?bereich=mitglieder&id=<?= $id ?>" class="btn-cancel">Abbrechen</a>
        <?php else: ?>
            <a href="index.php?bereich=mitglieder&action=bearbeiten&id=<?= $id ?>" class="btn-edit">Bearbeiten</a>
            <a href="index.php?bereich=mitglieder&action=loeschen&id=<?= $id ?>" 
               class="btn-delete" 
               onclick="return confirm('Möchten Sie dieses Mitglied wirklich dauerhaft löschen?');">Löschen</a>
            <a href="index.php?bereich=mitglieder" class="btn-cancel">Zurück</a>
        <?php endif; ?>
    </div>
    </form>
    
    <?php
    // Detailansicht ist fertig -> Funktionsdatei beenden.
    return;
}



/* Alle Mitglieder laden und Suchfunktion aktivieren
Wenn die Seite das erste mal gelade wird, ist die Suchfunktion leer und es werden alle Mitglieder geladen.*/

$suche = $_GET['suche'] ?? '';
$sql = "SELECT m.mitglied_id, 
               m.anrede, 
               m.vorname, 
               m.nachname, 
               m.geburtsdatum, 
               m.plz, 
               m.ort, 
               m.email, 
               m.telefon, 
               m.mobil,
               ms.vertragsbeginn, 
               ms.vertragsende, 
               v.bezeichnung AS vertragsart_bezeichnung
        FROM mitglied m
        LEFT JOIN mitgliedschaft ms ON m.mitglied_id = ms.mitglied_id
        LEFT JOIN vertragsart v ON ms.vertragsart_id = v.vertragsart_id";

$parameter = [];

/*Wenn ein Suchbegriff eingegeben wird, wird die SQL Abfrage um ein WHERE erweitert.
Um auch ungefähre Werte zu suchen, wird mit einer Wildcard gearbeitet.*/
if (!empty($suche)) {
    $sql .= " WHERE m.vorname LIKE ?
                OR m.nachname LIKE ?
                OR m.ort LIKE ?
                OR m.PLZ LIKE ?
                OR m.email LIKE ?";
    
    $suchBegriffMitWildcards = "%" . $suche . "%";

    // Suchbegriff für jede WHERE Abfrage einzeln übergeben
    $parameter = [$suchBegriffMitWildcards, $suchBegriffMitWildcards, $suchBegriffMitWildcards, $suchBegriffMitWildcards, $suchBegriffMitWildcards];
}
//Sortierung wieder an das SQL-Statement anfügen
$sql .= " ORDER BY m.nachname, m.vorname";

$stmt = $pdo->prepare($sql); // query($sql) durch prepare($sql) getauscht, durch ddie Parameter darf die SQL-Syntax nicht sofort ausgeführt werden
$stmt->execute($parameter);
$liste = $stmt->fetchAll();   // alle Datensaetze als Array
?>

<h1>Mitglieder</h1>
<p>Klicken Sie auf eine Zeile, um alle Details des Mitglieds anzuzeigen.</p>

<!--Suchfeld um nach Mitgliedern filtern zu können-->
<div class="such-container">
    <form method="GET" action="index.php">
        <input type="hidden" name="bereich" value="mitglieder">
        
        <input type="text" name="suche" value="<?= h($suche) ?>" placeholder="Nach Name, Ort, PLZ oder E-Mail suchen...">
        
        <button type="submit" class="btn-save">Suchen</button>
        
        <?php if (!empty($suche)): ?>
            <a href="index.php?bereich=mitglieder" class="btn-cancel">Filter löschen</a>
        <?php endif; ?>
    </form>
</div>
<!-- =========================================================================
     Jede Zelle enthaelt einen Link (<a>) zur Detailansicht. Der Link zeigt
     auf  index.php?bereich=mitglieder&id=...  – also reines HTML/PHP, kein
     JavaScript. 
     ========================================================================= -->
<table class="datentabelle">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Geburtsdatum</th>
            <th>PLZ / Ort</th>
            <th>E-Mail</th>
            <th>Telefon / Mobil</th>
            <th>Vertragsart</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
    <?php if (count($liste) === 0): ?>
        <tr>
            <td colspan="8" class="text-mitte">Noch keine Mitglieder vorhanden.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($liste as $zeile): ?>
            <?php
                // Link für die Detailansicht generieren
                $link = 'index.php?bereich=mitglieder&id=' . (int)$zeile['mitglied_id'];
                
                // Geburtsdatum von SQL YYYY-MM-DD in das deutsche Format DD.MM.YYYY umwandeln
                $geburtFormatiert = !empty($zeile['geburtsdatum']) ? date('d.m.Y', strtotime($zeile['geburtsdatum'])) : '–';

                // Status für die Badges berechnen
                $heute = date('Y-m-d');
                $statusText = 'Kein Vertrag';
                if (!empty($zeile['vertragsbeginn'])) {
                    if (empty($zeile['vertragsende']) || $zeile['vertragsende'] >= $heute) {
                        $statusText = 'Aktiv';
                    } else {
                        $statusText = 'Abgelaufen';
                    }
                }
            ?>

            <tr class="klickbar">
                <td><a href="<?= $link ?>"><?= h($zeile['mitglied_id']) ?></a></td>
                
                <td>
                    <a href="<?= $link ?>">
                        <span class="vollname"><?= h($zeile['nachname']) . ", " ?> <?= h($zeile['vorname']) ?></span>
                        <span class="anrede-sub"><?= h($zeile['anrede']) ?></span>
                    </a>
                </td>
                
                <td><a href="<?= $link ?>"><?= h($geburtFormatiert) ?></a></td>
                
                <td><a href="<?= $link ?>"><?= h($zeile['plz']) ?> <?= h($zeile['ort']) ?></a></td>
                
                <td class="email-zelle"><a href="<?= $link ?>"><?= h($zeile['email']) ?></a></td>
                
                <td>
                    <a href="<?= $link ?>">
                        <?= h(!empty($zeile['telefon']) ? $zeile['telefon'] : (!empty($zeile['mobil']) ? $zeile['mobil'] : '–')) ?>
                    </a>
                </td>
                
                <td><a href="<?= $link ?>"><?= h($zeile['vertragsart_bezeichnung'] ?? '–') ?></a></td>
                
                <td>
                    <a href="<?= $link ?>" class="badge-link">
                        <?php if ($statusText === 'Aktiv'): ?>
                            <span class="badge badge-aktiv">Aktiv</span>
                        <?php elseif ($statusText === 'Abgelaufen'): ?>
                            <span class="badge badge-abgelaufen">Abgelaufen</span>
                        <?php else: ?>
                            <span class="badge badge-kein">Kein Vertrag</span>
                        <?php endif; ?>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
