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
$qualiListe = $pdo->query("SELECT * FROM qualifikation")->fetchAll();



// Löschen von Trainern
if ($action === 'loeschen' && $id > 0) {

    $sqlDelete = "DELETE FROM trainer_qualifikation WHERE trainer_id = ?";
    $stmtDelete = $pdo->prepare($sqlDelete);
    $stmtDelete->execute([$id]);

    $sql = "DELETE FROM trainer WHERE trainer_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]); 

    // PopUp zur Bestätigung anzeigen und Weiterleitung zur Mitgliederliste
    echo "<script>
        alert('Der Trainer wurde erfolgreich gelöscht.');
        window.location.href='index.php?bereich=trainer';
        </script>";

    return;
}

// Neuen Trainer in der Datenbank anlegen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'neu') {
    $vorname      = $_POST['vorname'] ?? '';
    $nachname     = $_POST['nachname'] ?? '';
    $geburtsdatum = $_POST['geburtsdatum'] ?? null; 
    $geschlecht   = $_POST['geschlecht'] ?? '';
    $strasse      = $_POST['strasse'] ?? '';
    $hausnummer   = $_POST['hausnummer'] ?? '';
    $plz          = $_POST['plz'] ?? '';
    $ort          = $_POST['ort'] ?? '';
    $telefon      = $_POST['telefon'] ?? '';
    $email        = $_POST['email'] ?? '';

    // angehakte Qualiikationen in array speichern
    $qualifikationen = $_POST['qualifikationen'] ?? [];

    // Leeres Geb auf NULL setzen
    if (empty($geburtsdatum)) {
        $geburtsdatum = null;
    }

    // SQL-Syntax
    $sql = "INSERT INTO trainer (
                vorname, nachname, geburtsdatum, geschlecht, 
                strasse, hausnummer, plz, ort, telefon, email
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([ 
        $vorname, 
        $nachname, 
        $geburtsdatum, 
        $geschlecht, 
        $strasse, 
        $hausnummer, 
        $plz, 
        $ort, 
        $telefon, 
        $email
    ]);

    // ID des neuen Trainers auslesen
    $neueTrainerId = $pdo->lastInsertId();

    // Ausgewählte Qualifikationen in die Zwischentabelle schreiben
    if (!empty($qualifikationen) && is_array($qualifikationen)) {
        // SQL-Befehl für die Zwischentabelle 
        $sqlQuali = "INSERT INTO trainer_qualifikation (trainer_id, qualifikation_id) VALUES (?, ?)";
        $stmtQuali = $pdo->prepare($sqlQuali);
        
        // Schleif für alle Qualifikationen
        foreach ($qualifikationen as $quali_id) {
            $stmtQuali->execute([$neueTrainerId, (int)$quali_id]);
        }
    }

    // Bestätigung
    echo "<script>
        alert('Der neue Trainer wurde erfolgreich angelegt.');
        window.location.href = 'index.php?bereich=trainer';
    </script>";
    return;
}

// Neuen Trainer anlegen
if ($action === 'neu') {
    ?>
    <h1>Neuen Trainer registrieren</h1>
    <p>Bitte füllen Sie die Felder aus, um einen neuen Trainer im System anzulegen.</p>

    <form method="POST" action="index.php?bereich=trainer&action=neu">
        
        <div class="feld">
            <label>Vorname</label>
            <input type="text" name="vorname" required placeholder="Vorname des Trainers">
        </div>
        <div class="feld">
            <label>Nachname</label>
            <input type="text" name="nachname" required placeholder="Nachname des Trainers">
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
            <input type="text" name="plz" placeholder="5-stellige Postleitzahl">
        </div>
        <div class="feld">
            <label>Ort</label>
            <input type="text" name="ort" placeholder="Wohnort">
        </div>
        <div class="feld">
            <label>Telefon</label>
            <input type="text" name="telefon" placeholder="telefon">
        </div>
        <div class="feld">
            <label>E-Mail</label>
            <input type="text" name="email" placeholder="beispiel@domain.de">
        </div>
        <div class="feld">
    <div class="feld-vollbreit">
    <label>Vorhandene Qualifikationen</label>
    <div class="checkbox-liste">
        
        <?php foreach ($qualiListe as $quali): ?>
            <?php 
                // Falls das Array leer ist fehler abfangen
                $aktuelleQualisAktiv = $zugeordneteQualis ?? [];
                
                // Prüfen, ob die ID derQualifikation im Array des Trainers existiert
                $istHakenGezetst = in_array($quali['qualifikation_id'], $aktuelleQualisAktiv);
                
                // HTML-Attribut für den Haken vorbereiten
                $checked = $istHakenGezetst ? 'checked' : '';
            ?>
            
            <div class="checkbox-eintrag">
                <label class="checkbox-label">
                    <input type="checkbox" 
                           name="qualifikationen[]" 
                           value="<?= $quali['qualifikation_id'] ?>" 
                           <?= $checked ?>>
                    <span class="checkbox-text"><?= h($quali['bezeichnung']) ?></span>
                </label>
            </div>
            
        <?php endforeach; ?>
        
    </div>
    <div class="form-aktionen">
            <button type="submit" class="btn-save">Speichern</button>
            <a href="index.php?bereich=trainer" class="btn-cancel">Abbrechen</a>
        </div>
</div>
</div>
        
    </form>
    <?php
    return; 
}

// Änderungen speichern
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'speichern' && $id > 0) {
    // Daten abfangen
    $trainer_id = $_POST['trainer_id'] ?? '';
    $vorname = $_POST['vorname'] ?? '';
    $nachname = $_POST['nachname'] ?? '';
    $geburtsdatum = $_POST['geburtsdatum'] ?? null;
    $geschlecht = $_POST['geschlecht'] ?? '';
    $strasse = $_POST['strasse'] ?? '';
    $hausnummer = $_POST['hausnummer'] ?? '';
    $plz = $_POST['plz'] ?? '';
    $ort = $_POST['ort'] ?? '';
    $telefon = $_POST['telefon'] ?? '';
    $email = $_POST['email'] ?? '';

    //Array für die Qualifikationen
    $qualifikationen = $_POST['qualifikationen'] ?? [];

    if (empty($geburtsdatum)) {
        $geburtsdatum = null;
    }

    // SQL-Syntax
    $sql = "UPDATE trainer 
            SET vorname = ?,
                nachname = ?,
                geburtsdatum = ?,
                geschlecht = ?,
                strasse = ?,
                hausnummer = ?,
                plz = ?,
                ort = ?,
                telefon = ?,
                email = ?
            WHERE trainer_id = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $vorname, 
        $nachname, 
        $geburtsdatum, 
        $geschlecht, 
        $strasse, 
        $hausnummer, 
        $plz, 
        $ort, 
        $telefon, 
        $email, 
        $id
    ]);

    //Alte Daten aus der Zwischentabelle löschen
    $sqlDelete = "DELETE FROM trainer_qualifikation WHERE trainer_id = ?";
    $stmtDelete = $pdo->prepare($sqlDelete);
    $stmtDelete->execute([$id]);

    // Neue Qualifikationen eintragen
    if (!empty($qualifikationen) && is_array($qualifikationen)) {
        $sqlInsert = "INSERT INTO trainer_qualifikation (trainer_id, qualifikation_id) VALUES (?, ?)";
        $stmtInsert = $pdo->prepare($sqlInsert);
        
        // Jede Qualifikation per Schleife eintrage
        foreach ($qualifikationen as $quali_id) {
            $stmtInsert->execute([$id, (int)$quali_id]);
        }
    }
    // PopUp bei PSeicherung und Weiterleitung
    echo "<script>
        alert('Die Trainer-Daten wurden erfolgreich aktualisiert.');
        window.location.href = 'index.php?bereich=trainer&id=" . $id . "';
    </script>";
    return;
}

//Detailansicht
if ($id > 0) {

    // Datensatz mit der ID = x holen
    $sql = "SELECT t.trainer_id,
                   t.vorname,
                   t.nachname,
                   t.geburtsdatum,
                   t.geschlecht,
                   t.strasse,
                   t.hausnummer,
                   t.plz,
                   t.ort,
                   t.telefon,
                   t.email,
                   q.bezeichnung
            FROM trainer t
            LEFT JOIN trainer_qualifikation tq ON t.trainer_id = tq.trainer_id
            LEFT JOIN qualifikation q ON tq.qualifikation_id = q.qualifikation_id
            WHERE t.trainer_id = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $trainer = $stmt->fetch(); 

    // Erst prüfen, ob der Trainer überhaupt existiert
    if (!$trainer) {
        echo '<h1>Trainer-Details</h1>';
        echo '<p class="hinweis">Kein Trainer mit dieser ID gefunden.</p>';
        echo '<p><a href="index.php?bereich=trainer" class="btn-cancel">&laquo; Zurück zur Liste</a></p>';
        return;
    }

    //Zugeordnete Qualifikationen suchen und in einArray speichern
    $sqlZugeordnet = "SELECT qualifikation_id FROM trainer_qualifikation WHERE trainer_id = ?";
    $stmtZugeordnet = $pdo->prepare($sqlZugeordnet);
    $stmtZugeordnet->execute([$id]);

    //FETCH_COLUMN speicehrt nur die ID als einfache Zahl zurück
    $zugeordneteQualis = $stmtZugeordnet->fetchAll(PDO::FETCH_COLUMN);
?>
    <h1>Trainer-Details</h1>

    <form method="POST" action="index.php?bereich=trainer&id=<?= $id ?>&action=speichern">
        
        <div class="feld">
            <label>Trainer-ID</label>
            <input type="text" value="<?= h($trainer['trainer_id']) ?>" readonly>
        </div>
        <div class="feld">
            <label>Vorname</label>
            <input type="text" name="vorname" value="<?= h($trainer['vorname']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>
        <div class="feld">
            <label>Nachname</label>
            <input type="text" name="nachname" value="<?= h($trainer['nachname']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>
        <div class="feld">
            <label>Geburtsdatum</label>
            <input type="date" name="geburtsdatum" value="<?= h($trainer['geburtsdatum']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>
        <div class="feld">
            <label>Geschlecht</label>
            <input type="text" name="geschlecht" value="<?= h($trainer['geschlecht']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>
        <div class="feld">
            <label>Straße</label>
            <input type="text" name="strasse" value="<?= h($trainer['strasse']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>
        <div class="feld">
            <label>Hausnummer</label>
            <input type="text" name="hausnummer" value="<?= h($trainer['hausnummer']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>
        <div class="feld">
            <label>PLZ</label>
            <input type="text" name="plz" value="<?= h($trainer['plz']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>
        <div class="feld">
            <label>Ort</label>
            <input type="text" name="ort" value="<?= h($trainer['ort']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>
        <div class="feld">
            <label>Telefon</label>
            <input type="text" name="telefon" value="<?= h($trainer['telefon']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>
        <div class="feld">
            <label>E-Mail</label>
            <input type="text" name="email" value="<?= h($trainer['email']) ?>" <?= $istBearbeiten ? '' : 'readonly' ?>>
        </div>
        <div class="feld">
    <div class="feld-vollbreit">
    <label>Vorhandene Qualifikationen</label>
    <div class="checkbox-liste">
        
        <?php foreach ($qualiListe as $quali): ?>
            <?php 
                // Falls das Array leer ist fehler abfangen
                $aktuelleQualisAktiv = $zugeordneteQualis ?? [];
                
                // Prüfen, ob die ID derQualifikation im Array des Trainers existiert
                $istHakenGezetst = in_array($quali['qualifikation_id'], $aktuelleQualisAktiv);
                
                // HTML-Attribut für den Haken vorbereiten
                $checked = $istHakenGezetst ? 'checked' : '';
            ?>
            
            <div class="checkbox-eintrag">
                <label class="checkbox-label">
                    <input type="checkbox" 
                           name="qualifikationen[]" 
                           value="<?= $quali['qualifikation_id'] ?>" 
                           <?= $checked ?> 
                           <?= $istBearbeiten ? '' : 'disabled' ?>>
                    <span class="checkbox-text"><?= h($quali['bezeichnung']) ?></span>
                </label>
            </div>
        <?php endforeach; ?>
        
    </div>
</div>
</div>

        <div class="form-aktionen">
            <?php if ($istBearbeiten): ?>
                <button type="submit" class="btn-save">Änderungen speichern</button>
                <a href="index.php?bereich=trainer&id=<?= $id ?>" class="btn-cancel">Abbrechen</a>
            <?php else: ?>
                <a href="index.php?bereich=trainer&id=<?= $id ?>&action=bearbeiten" class="btn-edit">Bearbeiten</a>
                <a href="index.php?bereich=trainer&action=loeschen&id=<?= $id ?>" 
                    class="btn-delete" 
                    onclick="return confirm('Möchten Sie diesen Trainer wirklich dauerhaft löschen?');">Löschen</a>
                <a href="index.php?bereich=trainer" class="btn-cancel">Zurück</a>
            <?php endif; ?>
        </div>
        
    </form>

    <?php
    return; 
}
// Liste aller Trainer mit Suchfunktion laden -> Identisch mit Suchfunktion der Mitglieder
$sql = "SELECT t.trainer_id,
               t.vorname,
               t.nachname,
               GROUP_CONCAT(q.bezeichnung SEPARATOR ', ') AS qualifikationen
            FROM trainer t
            LEFT JOIN trainer_qualifikation tq ON t.trainer_id = tq.trainer_id
            LEFT JOIN qualifikation q ON tq.qualifikation_id = q.qualifikation_id";

$parameter = [];

if (!empty($suche)) {
    $sql .= " WHERE t.vorname LIKE ?
                 OR t.nachname LIKE ?
                 OR q.bezeichnung LIKE ?";
    
    $suchBegriffMitWildcards = "%" . $suche . "%";
    $parameter = [$suchBegriffMitWildcards, $suchBegriffMitWildcards, $suchBegriffMitWildcards];
}

$sql .= " GROUP BY t.trainer_id
          ORDER BY t.nachname, t.vorname";

$stmt = $pdo->prepare($sql);
$stmt->execute($parameter);
$liste = $stmt->fetchAll();
?>

<h1>Trainer</h1>
<p>Klicken Sie auf eine Zeile, um alle Details des Trainers anzuzeigen.</p>

<div class="such-container">
    <form method="GET" action="index.php">
        <input type="hidden" name="bereich" value="trainer">
        
        <input type="text" name="suche" value="<?= h($suche) ?>" placeholder="Nach Name oder Qualifikation suchen ...">
        
        <button type="submit" class="btn-save">Suchen</button>
        
        <?php if (!empty($suche)): ?>
            <a href="index.php?bereich=trainer" class="btn-cancel">Filter löschen</a>
        <?php endif; ?>
    </form>
</div>

<table class="datentabelle">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Qualifikationen</th>
        </tr>
    </thead>
    <tbody>
    <?php if (count($liste) === 0): ?>
        <tr>
            <td colspan="3" class="text-mitte">Noch keine Daten vorhanden.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($liste as $zeile): ?>
            <?php $link = 'index.php?bereich=trainer&id=' . (int)$zeile['trainer_id']; ?>

            <tr class="klickbar">
                <td><a href="<?= $link ?>"><?= h($zeile['trainer_id']) ?></a></td>
                <td>
                    <a href="<?= $link ?>">
                        <?php if (!empty($zeile['nachname'])): ?>
                            <span class="vollname"><?= h($zeile['nachname']) . ", " ?> <?= h($zeile['vorname']) ?></span>
                        <?php else: ?>
                            <span class="anrede-sub">Nicht zugewiesen</span>
                        <?php endif; ?>
                    </a>
                </td>
                <td>
    <a href="<?= $link ?>">
        <?php if (!empty($zeile['qualifikationen'])): ?>
            <?php 
                // Den String der Qualifikationen wieder in ein PHP-Array umwandeln
                $qualiArray = explode(', ', $zeile['qualifikationen']); 
            ?>
            <div class="badge-container">
                <?php foreach ($qualiArray as $einzelneQuali): ?>
                    <span class="badge-quali"><?= h($einzelneQuali) ?></span>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <span class="keine-quali">Keine Qualifikationen</span>
        <?php endif; ?>
    </a>
</td>
                
                
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>