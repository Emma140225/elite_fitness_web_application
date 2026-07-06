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
$vertragsListe     = $pdo->query("SELECT * FROM vertragsart")->fetchAll();
$mitgliederListe = $pdo->query("SELECT mitglied_id, vorname, nachname FROM mitglied ORDER BY nachname, vorname")->fetchAll();

// Löschen
// Löschen von Armbändern
if ($action === 'loeschen' && $id > 0) {
    $sql = "DELETE FROM mitgliedschaft WHERE mitgliedschaft_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]); 

    // PopUp zur Bestätigung anzeigen und Weiterleitung zur Mitgliederliste
    echo "<script>
        alert('Die Mitgliedschaft wurde erfolgreich gelöscht.');
        window.location.href='index.php?bereich=mitgliedschaften';
        </script>";

    return;
}

// Neues Mitgliedschaft anlegen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'neu') {
    $mitgliedschaft_id = $_POST['mitgliedschaft_id'] ?? '';
    $mitglied_id = $_POST['mitglied_id'] ?? '';
    $vertragsart_id = $_POST['vertragsart_id'] ?? '';
    $vertragsbeginn = $_POST['vertragsbeginn'] ?? '';
    $vertragsende = $_POST['vertragsende'] ?? '';
    $mitglied_suche = $_POST['mitglied_suche'] ?? '';

    // ID aus dem Namensfeld filter "Max Mustermann (ID: 42)" der Befehl holt sich die 42 aus dem String
    if (!empty($mitglied_suche) && preg_match('/\(ID:\s*(\d+)\)/', $mitglied_suche, $treffer)) {
        // Die Reine Zahl wird in einen Integer umgewandelt
        $mitglied_id = (int)$treffer[1];
    }

    // SQL Syntax
    $sql = "INSERT INTO mitgliedschaft (mitglied_id, vertragsart_id, vertragsbeginn, vertragsende) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([ 
        $mitglied_id,
        $vertragsart_id,
        $vertragsbeginn,
        $vertragsende
    ]);

    // Bestätigung
    echo "<script>
        alert('Die neue Mitgliedschaft wurde erfolgreich angelegt.');
        window.location.href = 'index.php?bereich=mitgliedschaften';
    </script>";
    return;
}

if ($action === 'neu') {
    ?>
    <h1>Neue Mitgliedschaft registrieren</h1>
    <p>Bitte füllen Sie die Felder aus, um eine neue Mitgliedschaft im System anzulegen.</p>

    <form method="POST" action="index.php?bereich=mitgliedschaften&action=neu">

        <!-- Dropdown mit den Status Möglichkeiten -->
        <div class="feld">
            <label>Vertrag</label>
             <select name="vertragsart_id">
                <?php foreach ($vertragsListe as $vertrag): ?>
                        <option value="<?= $vertrag['vertragsart_id'] ?>">
                            <?= h($vertrag['bezeichnung']) ?>
                        </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Textfeld mit Suchfunktin der Mitglieder -->
        <div class="feld">
            <label>Mitglied</label>
            <input type="text" name="mitglied_suche" list="mitglieder_liste" placeholder="Nach Name suchen...">
                
                <datalist id="mitglieder_liste">
                    <?php foreach ($mitgliederListe as $m): ?>
                        <option value="<?= h($m['nachname']) . ", " ?> <?= h($m['vorname']) ?> (ID: <?= $m['mitglied_id'] ?>)"></option>
                    <?php endforeach; ?>
                </datalist>
        </div>
                <div class="feld">
            <label>Vertragsbeginn</label>
            <input type="date" name="vertragsbeginn" id="heute">
            <script>document.getElementById('heute').valueAsDate = new Date();</script>
        </div>
                <div class="feld">
            <label>Vertragsende</label>
            <input type="date" name="vertragsende" id ="ende">
        </div>

        <div class="form-aktionen">
            <button type="submit" class="btn-save">Speichern</button>
            <a href="index.php?bereich=mitgliedschaften" class="btn-cancel">Abbrechen</a>
        </div>
        
    </form>
    <?php
    return; 
}

// Änderungen speichern
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'speichern' && $id > 0) {
    // Daten abfangen
    $mitgliedschaft_id = $_POST['mitgliedschaft_id'] ?? '';
    $mitglied_id = $_POST['mitglied_id'] ?? '';
    $vertragsart_id = $_POST['vertragsart_id'] ?? '';
    $vertragsbeginn = $_POST['vertragsbeginn'] ?? '';
    $vertragsende = $_POST['vertragsende'] ?? '';
    $mitglied_suche = $_POST['mitglied_suche'] ?? '';

    // Wir suchen im Text nach dem Muster "(ID: Zahl)" mithilfe eines Regulären Ausdrucks (Regex)
    if (!empty($mitglied_suche) && preg_match('/\(ID:\s*(\d+)\)/', $mitglied_suche, $treffer)) {
        // $treffer[1] enthält exakt die reine Zahl aus den Klammern
        $mitglied_id = (int)$treffer[1];
    }

    // SQL-Syntax
    $sql = "UPDATE mitgliedschaft 
            SET mitglied_id = ?, vertragsart_id = ?, vertragsbeginn = ?, vertragsende = ?
            WHERE mitgliedschaft_id = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$mitglied_id, $vertragsart_id, $vertragsbeginn, $vertragsende, $id]);

    // PopUp bei PSeicherung und Weiterleitung
    echo "<script>
        alert('Die Daten wurden erfolgreich aktualisiert.');
        window.location.href = 'index.php?bereich=mitgliedschaften&id=" . $id . "';
    </script>";
    return;
}

//Detailansicht
if ($id > 0) {

    // Datensatz mit der ID = x holen
    $sql = "SELECT mg.mitgliedschaft_id,
                   mg.mitglied_id,
                   mg.vertragsbeginn,
                   mg.vertragsende,
                   v.bezeichnung,
                   v.vertragsart_id,
                   m.vorname,
                   m.nachname,
                   m.anrede
            FROM mitgliedschaft mg
            JOIN mitglied m ON mg.mitglied_id = m.mitglied_id
            JOIN vertragsart v ON v.vertragsart_id = mg.vertragsart_id
            WHERE mg.mitgliedschaft_id = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $mitgliedschaft = $stmt->fetch(); 

    // Erst prüfen, ob Mitgliedschaft überhaupt existiert
    if (!$mitgliedschaft) {
        echo '<h1>Mitgliedschaft</h1>';
        echo '<p class="hinweis">Keine Mitgliedschaft mit dieser ID gefunden.</p>';
        echo '<p><a href="index.php?bereich=mitgliedschaften" class="btn-cancel">&laquo; Zurück zur Liste</a></p>';
        return;
    }

    // Mitgliedsinfo für Dropdown
    $aktuellesMitgliedText = "";
    if (!empty($mitgliedschaft['mitglied_id'])) {
        $aktuellesMitgliedText = $mitgliedschaft['nachname'] . ', ' . $mitgliedschaft['vorname'] . ' (ID: ' . $mitgliedschaft['mitglied_id'] . ')';
    }
    ?>

    <h1>Mitgliedschaft</h1>

    <form method="POST" action="index.php?bereich=mitgliedschaften&id=<?= $id ?>&action=speichern">
        
        <div class="feld">
            <label>ID</label>
            <input type="text" value="<?= h($mitgliedschaft['mitgliedschaft_id']) ?>" readonly>
        </div>
        <!-- Textfeld mit Suchfunktin der Mitglieder -->
        <div class="feld">
            <label>Zugeordnetes Mitglied</label>
             <?php if ($istBearbeiten): ?>
                <input type="text" name="mitglied_suche" list="mitglieder_liste" value="<?= h($aktuellesMitgliedText) ?>" readonly>
                 
                <datalist id="mitglieder_liste">
                    <?php foreach ($mitgliederListe as $m): ?>
                        <option value="<?= h($m['nachname']) . ", " ?> <?= h($m['vorname']) ?> (ID: <?= $m['mitglied_id'] ?>)"></option>
                    <?php endforeach; ?>
                </datalist>
            <?php else: ?>
                <input type="text" value="<?= !empty($aktuellesMitgliedText) ? h($aktuellesMitgliedText) : 'Aktuell keinem Mitglied zugewiesen' ?>" readonly>
            <?php endif; ?>
        </div>

        <!-- Dropdown mit den Vertragsarten -->
        <div class="feld">
            <label>Vertragsart</label>
            <select name="vertragsart_id" <?= $istBearbeiten ? '' : 'disabled' ?>>
                <?php foreach ($vertragsListe as $vertrag): ?>
                    <?php $selected = ($vertrag['vertragsart_id'] === $mitgliedschaft['vertragsart_id']) ? 'selected' : ''; ?>
                    <option value="<?= $vertrag['vertragsart_id'] ?>" <?= $selected ?>>
                        <?= h($vertrag['bezeichnung']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="feld">
            <label>Vertragsbeginn</label>
            <input type="date" name="vertragsbeginn" value="<?= h($mitgliedschaft['vertragsbeginn'])?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>
        <div class="feld">
            <label>Vertragsende</label>
            <input type="date" name="vertragsende" value="<?= h($mitgliedschaft['vertragsende']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>

        

        <div class="form-aktionen">
            <?php if ($istBearbeiten): ?>
                <button type="submit" class="btn-save">Speichern</button>
                <a href="index.php?bereich=mitgliedschaften&id=<?= $id ?>" class="btn-cancel">Abbrechen</a>
            <?php else: ?>
                <a href="index.php?bereich=mitgliedschaften&id=<?= $id ?>&action=bearbeiten" class="btn-edit">Bearbeiten</a>
                <a href="index.php?bereich=mitgliedschaften&action=loeschen&id=<?= $id ?>" 
                    class="btn-delete" 
                    onclick="return confirm('Möchten Sie diese Mitgliedschaft wirklich dauerhaft löschen?');">Löschen</a>
                <a href="index.php?bereich=mitgliedschaften" class="btn-cancel">Zurück</a>
            <?php endif; ?>
        </div>
        
    </form>

    <?php
    return; 
}
// Liste aller Mitgliedschaften laden
$sql = "SELECT mg.mitgliedschaft_id,
                   mg.vertragsbeginn,
                   mg.vertragsende,
                   v.bezeichnung,
                   m.vorname,
                   m.nachname,
                   m.anrede
            FROM mitgliedschaft mg
            JOIN mitglied m ON mg.mitglied_id = m.mitglied_id
            JOIN vertragsart v ON v.vertragsart_id = mg.vertragsart_id";

$parameter = [];

if (!empty($suche)) {
    $sql .= " WHERE m.vorname LIKE ?
                 OR m.nachname LIKE ?
                 OR v.bezeichnung LIKE ?";
    
    $suchBegriffMitWildcards = "%" . $suche . "%";
    $parameter = [$suchBegriffMitWildcards, $suchBegriffMitWildcards, $suchBegriffMitWildcards];
}

$sql .= " ORDER BY m.nachname, m.vorname";

$stmt = $pdo->prepare($sql);
$stmt->execute($parameter);
$liste = $stmt->fetchAll();
?>

<h1>Mitgliedschaften</h1>
<p>Klicken Sie auf eine Zeile, um alle Details anzuzeigen.</p>

<div class="such-container">
    <form method="GET" action="index.php">
        <input type="hidden" name="bereich" value="mitgliedschaften">
        
        <input type="text" name="suche" value="<?= h($suche) ?>" placeholder="Nach Name oder Vertrag suchen ...">
        
        <button type="submit" class="btn-save">Suchen</button>
        
        <?php if (!empty($suche)): ?>
            <a href="index.php?bereich=mitgliedschaften" class="btn-cancel">Filter löschen</a>
        <?php endif; ?>
    </form>
</div>

<table class="datentabelle">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Vertrag</th>
            <th>Vertragsbeginn</th>
            <th>Vertragsende</th>
        </tr>
    </thead>
    <tbody>
    <?php if (count($liste) === 0): ?>
        <tr>
            <td colspan="5" class="text-mitte">Noch keine Daten vorhanden.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($liste as $zeile): ?>
            <?php $link = 'index.php?bereich=mitgliedschaften&id=' . (int)$zeile['mitgliedschaft_id']; ?>

            <tr class="klickbar">
                <td><a href="<?= $link ?>"><?= h($zeile['mitgliedschaft_id']) ?></a></td>
                <td class="name-zelle">
                    <a href="<?= $link ?>">
                        <?php if (!empty($zeile['nachname'])): ?>
                            <span class="vollname"><?= h($zeile['vorname']) ?> <?= h($zeile['nachname']) ?></span>
                            <span class="anrede-sub"><?= h($zeile['anrede']) ?></span>
                        <?php else: ?>
                            <span class="anrede-sub">Nicht zugewiesen</span>
                        <?php endif; ?>
                    </a>
                </td>

                <td><a href="<?= $link ?>"><?= h($zeile['bezeichnung']) ?></a></td>
                <td><a href="<?= $link ?>"><?= h($zeile['vertragsbeginn'] ? date('d.m.Y', strtotime($zeile['vertragsbeginn'])) : 'Unbekannt') ?></a></td>
                <td><a href="<?= $link ?>"><?= h($zeile['vertragsende'] ? date('d.m.Y', strtotime($zeile['vertragsende'])) : 'Unbekannt') ?></a></td>
                
                
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>