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
$statusListe     = $pdo->query("SELECT * FROM vertragsart")->fetchAll();

// Löschen
if ($action === 'loeschen' && $id > 0) {
    $sql = "DELETE FROM vertragsart WHERE vertragsart_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]); 

    // PopUp zur Bestätigung anzeigen und Weiterleitung zur Mitgliederliste
    echo "<script>
        alert('Die Vertragsart wurde erfolgreich gelöscht.');
        window.location.href='index.php?bereich=vertragsarten';
        </script>";

    return;
}

// Neuen Vertrag anlegen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'neu') {
    $bezeichnung = $_POST['bezeichnung'] ?? '';
    $preis = $_POST['preis'] ?? '';
    $beschreibung = $_POST['beschreibung'] ?? '';

    // SQL Syntax
    $sql = "INSERT INTO vertragsart (bezeichnung, preis, beschreibung) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([ 
        $bezeichnung,
        $preis,
        $beschreibung
    ]);

    // Bestätigung
    echo "<script>
        alert('Die neue Vertragsart wurde erfolgreich angelegt.');
        window.location.href = 'index.php?bereich=vertragsarten';
    </script>";
    return;
}

if ($action === 'neu') {
    ?>
    <h1>Neue Vertragsart registrieren</h1>
    <p>Bitte füllen Sie die Felder aus, um eine neue Vertragsart im System anzulegen.</p>

    <form method="POST" action="index.php?bereich=vertragsarten&action=neu">
        
        <div class="feld">
            <label>Bezeichnung</label>
            <input type="text" name="bezeichnung" placeholder="Streifenkarte">
        </div>
        <div class="feld">
            <label>Beschreibung</label>
            <input type="text" name="beschreibung" placeholder="Eine kurze Beschreibung der Vertragsart">
        </div>
        <div class="feld">
            <label>Preis</label>
            <input type="currency" name="preis" placeholder="123,56">
        </div>

        <div class="form-aktionen">
            <button type="submit" class="btn-save">Speichern</button>
            <a href="index.php?bereich=vertragsarten" class="btn-cancel">Abbrechen</a>
        </div>
        
    </form>
    <?php
    return; 
}

// Änderungen speichern
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'speichern' && $id > 0) {
    // Daten abfangen
    $vertragsart_id = $_POST['vertragsart_id'] ?? '';
    $bezeichnung = $_POST['bezeichnung'] ?? '';
    $beschreibung = $_POST['beschreibung'] ?? '';
    $preis = $_POST['preis'] ?? '';

    // SQL-Syntax
    $sql = "UPDATE vertragsart 
            SET bezeichnung = ?, beschreibung = ?, preis = ?
            WHERE vertragsart_id = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$bezeichnung, $beschreibung, $preis, $id]);

    // PopUp bei PSeicherung und Weiterleitung
    echo "<script>
        alert('Die Daten wurden erfolgreich aktualisiert.');
        window.location.href = 'index.php?bereich=vertragsarten&id=" . $id . "';
    </script>";
    return;
}

//Detailansicht
if ($id > 0) {

    // Datensatz mit der ID = x holen
    $sql = "SELECT vertragsart_id,
                   bezeichnung,
                   beschreibung,
                   preis
            FROM vertragsart
            WHERE vertragsart_id = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $quali = $stmt->fetch(); 

    // Erst prüfen, ob das Armband überhaupt existiert
    if (!$quali) {
        echo '<h1>Vertragsarts-Details</h1>';
        echo '<p class="hinweis">Keine Vertragsart mit dieser ID gefunden.</p>';
        echo '<p><a href="index.php?bereich=vertragsarten" class="btn-cancel">&laquo; Zurück zur Liste</a></p>';
        return;
    }
    ?>

    <h1>Vertragsarts-Details</h1>

    <form method="POST" action="index.php?bereich=vertragsarten&id=<?= $id ?>&action=speichern">
        
        <div class="feld">
            <label>vertragsarts-ID</label>
            <input type="text" value="<?= h($quali['vertragsart_id']) ?>" readonly>
        </div>

        <div class="feld">
            <label>Bezeichnung</label>
            <input type="text" name="bezeichnung" value="<?= h($quali['bezeichnung']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>

        <div class="feld">
            <label>Beschreibung</label>
            <input type="text" name="beschreibung" value="<?= h($quali['beschreibung']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>

        <div class="feld">
            <label>Preis</label>
            <input type="currency" name="preis" value="<?= h($quali['preis']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>

        <div class="form-aktionen">
            <?php if ($istBearbeiten): ?>
                <button type="submit" class="btn-save">Speichern</button>
                <a href="index.php?bereich=vertragsarten&id=<?= $id ?>" class="btn-cancel">Abbrechen</a>
            <?php else: ?>
                <a href="index.php?bereich=vertragsarten&id=<?= $id ?>&action=bearbeiten" class="btn-edit">Bearbeiten</a>
                <a href="index.php?bereich=vertragsarten&action=loeschen&id=<?= $id ?>" 
                    class="btn-delete" 
                    onclick="return confirm('Möchten Sie diese Vertragsart wirklich dauerhaft löschen?');">Löschen</a>
                <a href="index.php?bereich=vertragsarten" class="btn-cancel">Zurück</a>
            <?php endif; ?>
        </div>
        
    </form>

    <?php
    return; 
}
// Liste aller vertragsarten mit Suchfunktion laden-> Identisch mit Suchfunktion der Mitglieder
$sql = "SELECT vertragsart_id,
               bezeichnung,
               preis,
               beschreibung
        FROM vertragsart";

$parameter = [];

if (!empty($suche)) {
    $sql .= " WHERE bezeichnung LIKE ?";
    
    $suchBegriffMitWildcards = "%" . $suche . "%";
    $parameter = [$suchBegriffMitWildcards];
}

$sql .= " ORDER BY bezeichnung";

$stmt = $pdo->prepare($sql);
$stmt->execute($parameter);
$liste = $stmt->fetchAll();
?>

<h1>Vertragsarten</h1>
<p>Klicken Sie auf eine Zeile, um alle Details anzuzeigen.</p>

<div class="such-container">
    <form method="GET" action="index.php">
        <input type="hidden" name="bereich" value="vertragsarten">
        
        <input type="text" name="suche" value="<?= h($suche) ?>" placeholder="Nach Bezeichnung suchen ...">
        
        <button type="submit" class="btn-save">Suchen</button>
        
        <?php if (!empty($suche)): ?>
            <a href="index.php?bereich=vertragsarten" class="btn-cancel">Filter löschen</a>
        <?php endif; ?>
    </form>
</div>

<table class="datentabelle">
    <thead>
        <tr>
            <th>ID</th>
            <th>Bezeichnung</th>
            <th>Preis in €</th>
            <th>Beschreibung</th>
        </tr>
    </thead>
    <tbody>
    <?php if (count($liste) === 0): ?>
        <tr>
            <td colspan="3" class="text-mitte">Noch keine Daten vorhanden.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($liste as $zeile): ?>
            <?php $link = 'index.php?bereich=vertragsarten&id=' . (int)$zeile['vertragsart_id']; ?>

            <tr class="klickbar">
                <td><a href="<?= $link ?>"><?= h($zeile['vertragsart_id']) ?></a></td>
                <td><a href="<?= $link ?>"><span class="vollname"><?= h($zeile['bezeichnung']) ?></span></a></td>
                <td><a href="<?= $link ?>"><?= h($zeile['preis']) ?></a></td>
                <td><a href="<?= $link ?>"><?= h($zeile['beschreibung'] ?? 'Unbekannt') ?></a></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>