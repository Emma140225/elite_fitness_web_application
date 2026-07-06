<?php

// Hilfsfunktion: macht Text fuer die HTML-Ausgabe sicher und den Code leserlicher und kuerzer
// (verhindert, dass z.B. "<script>" im Datenfeld Schaden anrichtet)
function h($wert): string {
    return htmlspecialchars((string)$wert, ENT_QUOTES, 'UTF-8');
}

// Parameter der URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = $_GET['action'] ?? '';
$suche = $_GET['suche'] ?? '';
$istBearbeiten = ($action === 'bearbeiten');

//Stammdaten
$statusListe     = $pdo->query("SELECT * FROM armband_status")->fetchAll();
$mitgliederListe = $pdo->query("SELECT mitglied_id, vorname, nachname FROM mitglied ORDER BY nachname, vorname")->fetchAll();

// Löschen
// Löschen von Armbändern
if ($action === 'loeschen' && $id > 0) {
    $sql = "DELETE FROM armband WHERE armband_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]); 

    // PopUp zur Bestätigung anzeigen und Weiterleitung zur Mitgliederliste
    echo "<script>
        alert('Das Armband wurde erfolgreich gelöscht.');
        window.location.href='index.php?bereich=rfid';
        </script>";

    return;
}

// Neues armband anlegen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'neu') {
    $rfid           = $_POST['rfid'] ?? '';
    $status_id      = $_POST['status_id'] ?? null;
    $mitglied_suche = $_POST['mitglied_suche'] ?? '';

    // ID standardmäßig als null, Lagerbestand, noch nicht zugewiesen
    $mitglied_id = null;

    // ID aus dem Namensfeld filter "Max Mustermann (ID: 42)" der Befehl holt sich die 42 aus dem String
    if (!empty($mitglied_suche) && preg_match('/\(ID:\s*(\d+)\)/', $mitglied_suche, $treffer)) {
        // Die Reine Zahl wird in einen Integer umgewandelt
        $mitglied_id = (int)$treffer[1];
    }

    // SQL Syntax
    $sql = "INSERT INTO armband (rfid, status_id, mitglied_id) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $rfid, 
        $status_id, 
        $mitglied_id
    ]);

    // Bestätigung
    echo "<script>
        alert('Das neue RFID-Armband wurde erfolgreich angelegt.');
        window.location.href = 'index.php?bereich=rfid';
    </script>";
    return;
}

if ($action === 'neu') {
    ?>
    <h1>Neues Armband registrieren</h1>
    <p>Bitte füllen Sie die Felder aus, um ein neues Armband im System anzulegen.</p>

    <form method="POST" action="index.php?bereich=rfid&action=neu">
        
        <div class="feld">
            <label>RFID-Nummer</label>
            <input type="text" name="rfid" placeholder="123456">
        </div>

        <!-- Dropdown mit den Status Möglichkeiten -->
        <div class="feld">
            <label>Status</label>
             <select name="status_id">
                <?php foreach ($statusListe as $status): ?>
                    <?php if ($status['bezeichnung'] === 'Aktiv' || $status['bezeichnung'] === 'Deaktiviert'): ?>
                        <option value="<?= $status['armband_status_id'] ?>">
                            <?= h($status['bezeichnung']) ?>
                        </option>
                     ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Textfeld mit Suchfunktin der Mitglieder -->
        <div class="feld">
            <label>Mitglied</label>
            <input type="text" name="mitglied_suche" list="mitglieder_liste" placeholder="Nach Name suchen...">
                
                <datalist id="mitglieder_liste">
                    <?php foreach ($mitgliederListe as $m): ?>
                        <option value="<?= h($m['vorname']) ?> <?= h($m['nachname']) ?> (ID: <?= $m['mitglied_id'] ?>)"></option>
                    <?php endforeach; ?>
                </datalist>
        </div>

        <div class="form-aktionen">
            <button type="submit" class="btn-save">Speichern</button>
            <a href="index.php?bereich=mitglieder" class="btn-cancel">Abbrechen</a>
        </div>
        
    </form>
    <?php
    return; 
}

// Änderungen speichern
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'speichern' && $id > 0) {
    // Daten abfangen
    $rfid = $_POST['rfid'] ?? '';
    $status_id = $_POST['status_id'] ?? null;
    $mitglied_suche = $_POST['mitglied_suche'] ?? '';

    // Standard ohne Mitglied
    $mitglied_id = null;

    // Magischer HTML5-Datalist-Trick:
    // Wir suchen im Text nach dem Muster "(ID: Zahl)" mithilfe eines Regulären Ausdrucks (Regex)
    if (!empty($mitglied_suche) && preg_match('/\(ID:\s*(\d+)\)/', $mitglied_suche, $treffer)) {
        // $treffer[1] enthält exakt die reine Zahl aus den Klammern
        $mitglied_id = (int)$treffer[1];
    }

    // SQL-Syntax
    $sql = "UPDATE armband 
            SET rfid = ?, status_id = ?, mitglied_id = ? 
            WHERE armband_id = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$rfid, $status_id, $mitglied_id, $id]);

    // PopUp bei PSeicherung und Weiterleitung
    echo "<script>
        alert('Die Armband-Daten wurden erfolgreich aktualisiert.');
        window.location.href = 'index.php?bereich=rfid&id=" . $id . "';
    </script>";
    return;
}

//Detailansicht
if ($id > 0) {

    // Datensatz mit der ID = x holen
    $sql = "SELECT a.armband_id,
                   a.rfid,
                   a.status_id,
                   a.mitglied_id,
                   s.bezeichnung AS status_bezeichnung,
                   m.vorname,
                   m.nachname,
                   m.anrede
            FROM armband a
            LEFT JOIN mitglied m ON a.mitglied_id = m.mitglied_id
            LEFT JOIN armband_status s ON s.armband_status_id = a.status_id
            WHERE a.armband_id = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $armband = $stmt->fetch(); 

    // Erst prüfen, ob das Armband überhaupt existiert
    if (!$armband) {
        echo '<h1>Armband-Details</h1>';
        echo '<p class="hinweis">Kein Armband mit dieser ID gefunden.</p>';
        echo '<p><a href="index.php?bereich=rfid" class="btn-cancel">&laquo; Zurück zur Liste</a></p>';
        return;
    }

    // Mitgliedsinfo für Dropdown
    $aktuellesMitgliedText = "";
    if (!empty($armband['mitglied_id'])) {
        $aktuellesMitgliedText = $armband['nachname'] . ', ' . $armband['vorname'] . ' (ID: ' . $armband['mitglied_id'] . ')';
    }
    ?>

    <h1>Armband-Details</h1>

    <form method="POST" action="index.php?bereich=rfid&id=<?= $id ?>&action=speichern">
        
        <div class="feld">
            <label>Armband-ID</label>
            <input type="text" value="<?= h($armband['armband_id']) ?>" readonly>
        </div>

        <div class="feld">
            <label>RFID-Nummer</label>
            <input type="text" name="rfid" value="<?= h($armband['rfid']) ?>" readonly>
        </div>
        
        <!-- Dropdown mit den Status Möglichkeiten -->
        <div class="feld">
            <label>Zustand / Status</label>
            <select name="status_id" <?= $istBearbeiten ? '' : 'disabled' ?>>
                <?php foreach ($statusListe as $status): ?>
                    <?php $selected = ($status['armband_status_id'] === $armband['status_id']) ? 'selected' : ''; ?>
                    <option value="<?= $status['armband_status_id'] ?>" <?= $selected ?>>
                        <?= h($status['bezeichnung']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <!-- Textfeld mit Suchfunktin der Mitglieder -->
        <div class="feld">
            <label>Zugeordnetes Mitglied (Suchfunktion)</label>
            <?php if ($istBearbeiten): ?>
                <input type="text" name="mitglied_suche" list="mitglieder_liste" value="<?= h($aktuellesMitgliedText) ?>" placeholder="Nach Name suchen...">
                
                <datalist id="mitglieder_liste">
                    <?php foreach ($mitgliederListe as $m): ?>
                        <option value="<?= h($m['nachname']) . ", " ?> <?= h($m['vorname']) ?> (ID: <?= $m['mitglied_id'] ?>)"></option>
                    <?php endforeach; ?>
                </datalist>
            <?php else: ?>
                <input type="text" value="<?= !empty($aktuellesMitgliedText) ? h($aktuellesMitgliedText) : 'Aktuell keinem Mitglied zugewiesen' ?>" readonly>
            <?php endif; ?>
        </div>

        <div class="form-aktionen">
            <?php if ($istBearbeiten): ?>
                <button type="submit" class="btn-save">Speichern</button>
                <a href="index.php?bereich=rfid&id=<?= $id ?>" class="btn-cancel">Abbrechen</a>
            <?php else: ?>
                <a href="index.php?bereich=rfid&id=<?= $id ?>&action=bearbeiten" class="btn-edit">Bearbeiten</a>
                <a href="index.php?bereich=rfid&action=loeschen&id=<?= $id ?>" 
                    class="btn-delete" 
                    onclick="return confirm('Möchten Sie dieses Armband wirklich dauerhaft löschen?');">Löschen</a>
                <a href="index.php?bereich=rfid" class="btn-cancel">Zurück</a>
            <?php endif; ?>
        </div>
        
    </form>

    <?php
    return; 
}

// Liste aller Armbänder mit Suchfunktion laden-> Identisch mit Suchfunktion der Mitglieder
$sql = "SELECT a.armband_id,
               a.rfid,
               s.bezeichnung AS status_bezeichnung,
               m.vorname,
               m.nachname,
               m.anrede
        FROM armband a
        LEFT JOIN mitglied m ON a.mitglied_id = m.mitglied_id
        LEFT JOIN armband_status s ON s.armband_status_id = a.status_id";

$parameter = [];

if (!empty($suche)) {
    $sql .= " WHERE m.vorname LIKE ?
                 OR m.nachname LIKE ?
                 OR a.rfid LIKE ?";
    
    $suchBegriffMitWildcards = "%" . $suche . "%";
    $parameter = [$suchBegriffMitWildcards, $suchBegriffMitWildcards, $suchBegriffMitWildcards];
}

$sql .= " ORDER BY m.nachname, m.vorname";

$stmt = $pdo->prepare($sql);
$stmt->execute($parameter);
$liste = $stmt->fetchAll();
?>

<h1>Armbänder</h1>
<p>Klicken Sie auf eine Zeile, um alle Details des Armbands anzuzeigen.</p>

<div class="such-container">
    <form method="GET" action="index.php">
        <input type="hidden" name="bereich" value="rfid">
        
        <input type="text" name="suche" value="<?= h($suche) ?>" placeholder="Nach Name oder RFID suchen ...">
        
        <button type="submit" class="btn-save">Suchen</button>
        
        <?php if (!empty($suche)): ?>
            <a href="index.php?bereich=rfid" class="btn-cancel">Filter löschen</a>
        <?php endif; ?>
    </form>
</div>

<table class="datentabelle">
    <thead>
        <tr>
            <th>ID</th>
            <th>RFID-Nummer</th>
            <th>Status</th>
            <th>Name</th>
        </tr>
    </thead>
    <tbody>
    <?php if (count($liste) === 0): ?>
        <tr>
            <td colspan="4" class="text-mitte">Noch keine Daten vorhanden.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($liste as $zeile): ?>
            <?php $link = 'index.php?bereich=rfid&id=' . (int)$zeile['armband_id']; ?>

            <tr class="klickbar">
                <td><a href="<?= $link ?>"><?= h($zeile['armband_id']) ?></a></td>
                <td><a href="<?= $link ?>"><?= h($zeile['rfid']) ?></a></td>
                <td><a href="<?= $link ?>"><?= h($zeile['status_bezeichnung'] ?? 'Unbekannt') ?></a></td>
                
                <td class="name-zelle">
                    <a href="<?= $link ?>">
                        <?php if (!empty($zeile['nachname'])): ?>
                            <span class="vollname"><?= h($zeile['nachname']) . ", " ?> <?= h($zeile['vorname']) ?></span>
                            <span class="anrede-sub"><?= h($zeile['anrede']) ?></span>
                        <?php else: ?>
                            <span class="anrede-sub">Nicht zugewiesen</span>
                        <?php endif; ?>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>