<?php

// Hilfsfunktion: macht Text fuer die HTML-Ausgabe sicher und den Code leserlicher und kuerzer
// (verhindert, dass z.B. "<script>" im Datenfeld Schaden anrichtet)
function h($wert): string {
    return htmlspecialchars((string)$wert, ENT_QUOTES, 'UTF-8');
}

// Parameter der URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$termin_id = $_GET['termin_id'] ?? '';
$kurs_id= $_GET['kurs_id'] ?? '';
$raum_id= $_GET['raum_id'] ?? '';
$action = $_GET['action'] ?? '';
$suche = $_GET['suche'] ?? '';
$istBearbeiten = ($action === 'bearbeiten');

//Stammdaten
$kursListe = $pdo->query("SELECT * FROM kurs ORDER BY bezeichnung")->fetchAll();
$raumListe = $pdo->query("SELECT * FROM raum")->fetchAll();
$trainerListe = $pdo->query("SELECT trainer_id, vorname, nachname FROM trainer ORDER BY nachname, vorname")->fetchAll();
$kurstermin = $pdo->query("SELECT * FROM kurstermin ORDER BY datum")->fetchAll();

// Löschen
// Löschen von Armbändern
if ($action === 'loeschen' && $termin_id > 0) {
    $sql = "DELETE FROM kurstermin WHERE kurstermin_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$termin_id]); 

    // PopUp zur Bestätigung anzeigen und Weiterleitung zur Mitgliederliste
    echo "<script>
        alert('Der Termin wurde erfolgreich gelöscht.');
        window.location.href='index.php?bereich=kurse';
        </script>";

    return;
}

// Neuen Termin anlegen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'neu') {
    $kurs_id = $_POST['kurs'] ?? '';
    $kurstermin_id = $_POST['kurstermin_id'] ?? '';
    $raum_id = $_POST['raum'] ?? '';
    $datum = $_POST['datum'] ?? '';
    $start_zeit = $_POST['start_zeit'] ?? '';
    $ende_zeit = $_POST['ende_zeit'] ?? '';
    $trainer_id = $_POST['trainer'] ?? '';
    $trainer_suche = $_POST['trainer_suche'] ?? '';

    // ID aus dem Namensfeld filter "Max Mustermann (ID: 42)" der Befehl holt sich die 42 aus dem String
    if (!empty($trainer_suche) && preg_match('/\(ID:\s*(\d+)\)/', $trainer_suche, $treffer)) {
        // Die Reine Zahl wird in einen Integer umgewandelt
        $trainer_id = (int)$treffer[1];
    }

    // SQL Syntax
    $sql = "INSERT INTO kurstermin (kurs_id, raum_id, trainer_id, datum, start_zeit, ende_zeit) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([ 
        $kurs_id,
        $raum_id,
        $trainer_id,
        $datum,
        $start_zeit,
        $ende_zeit
    ]);

    // Bestätigung
    echo "<script>
        alert('Der neue Termin wurde erfolgreich angelegt.');
        window.location.href = 'index.php?bereich=kurse';
    </script>";
    return;
}

if ($action === 'neu') {
    ?>
    <h1>Neuen Kurstermin anlegen</h1>
    <p>Bitte füllen Sie die Felder aus, um einen neue Kurstermin im System anzulegen.</p>

    <form method="POST" action="index.php?bereich=kurse&action=neu">

        <!-- Dropdown mit den Kursen -->
        <div class="feld">
            <label>Kurs</label>
             <select name="kurs">
                <?php foreach ($kursListe as $kurs): ?>
                        <option value="<?= $kurs['kurs_id'] ?>">
                            <?= h($kurs['bezeichnung']) ?>
                        </option>
                <?php endforeach; ?>
            </select>
        </div>
        <!-- Dropdown mit den Räumen -->
        <div class="feld">
            <label>Raum</label>
             <select name="raum">
                <?php foreach ($raumListe as $raum): ?>
                        <option value="<?= $raum['raum_id'] ?>">
                            <?= h($raum['lage']) . ' - ' . h($raum['beschreibung']) ?>
                        </option>
                <?php endforeach; ?>
            </select>
        </div>
    <!-- Dropdown mit den Trainern -->
        <div class="feld">
            <label>Trainer</label>
             <select name="trainer">
                <?php foreach ($trainerListe as $trainer): ?>
                        <option value="<?= $trainer['trainer_id'] ?>">
                            <?= h($trainer['nachname']) . ' ' . h($trainer['vorname']) . ' (ID:' .  h($trainer['trainer_id']) . ')' ?>
                        </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="feld">
            <label>Datum</label>
            <input type="date" name="datum" id="heute">
            <script>document.getElementById('heute').valueAsDate = new Date();</script>
        </div>
        <div class="feld">
            <label>Beginn</label>
            <input type="time" name="start_zeit">
        </div>
        <div class="feld">
            <label>Ende</label>
            <input type="time" name="ende_zeit">
        </div>

        <div class="form-aktionen">
            <button type="submit" class="btn-save">Speichern</button>
            <a href="index.php?bereich=kurse" class="btn-cancel">Abbrechen</a>
        </div>
        
    </form>
    <?php
    return; 
}

// Änderungen speichern
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'speichern' && $termin_id > 0) {
    // Daten abfangen
    $kurs_id = $_POST['kurs'] ?? '';
    $kurstermin_id = $_POST['kurstermin_id'] ?? '';
    $raum_id = $_POST['raum'] ?? '';
    $datum = $_POST['datum'] ?? '';
    $start_zeit = $_POST['start_zeit'] ?? '';
    $ende_zeit = $_POST['ende_zeit'] ?? '';
    $trainer_id = $_POST['trainer'] ?? '';
    $trainer_suche = $_POST['trainer_suche'] ?? '';

    // Wir suchen im Text nach dem Muster "(ID: Zahl)" mithilfe eines Regulären Ausdrucks (Regex)
    if (!empty($trainer_suche) && preg_match('/\(ID:\s*(\d+)\)/', $trainer_suche, $treffer)) {
        // $treffer[1] enthält exakt die reine Zahl aus den Klammern
        $trainer_id = (int)$treffer[1];
    }

    // SQL-Syntax
    $sql = "UPDATE kurstermin 
            SET kurs_ID = ?, raum_id =?, trainer_id = ?, datum = ?, start_zeit = ?, ende_zeit = ?
            WHERE kurstermin_id = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$kurs_id, $raum_id, $trainer_id, $datum, $start_zeit, $ende_zeit, $termin_id]);

    // PopUp bei PSeicherung und Weiterleitung
    echo "<script>
        alert('Die Daten wurden erfolgreich aktualisiert.');
        window.location.href = 'index.php?bereich=kurse&kurs_id=" . $kurs_id . "';
    </script>";
    return;
}

if ($action === 'bearbeiten') {
    $sql = "SELECT * FROM kurstermin WHERE kurstermin_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$termin_id]);
    $aktuellerTermin = $stmt->fetch();
    ?>
    
    <h1>Kurstermin ändern</h1>

    <form method="POST" action="index.php?bereich=kurse&action=speichern&termin_id=<?= (int)$termin_id ?>">

        <div class="feld">
            <label>ID</label>
            <input type="text" name="kurstermin_id" value="<?= h($aktuellerTermin['kurstermin_id']) ?>" readonly>
        </div>
        <!-- Dropdown mit den Kursen -->
        <div class="feld">
            <label>Kurs</label>
             <select name="kurs">
                <?php foreach ($kursListe as $kurs): ?>
                    <?php $selected = ($kurs['kurs_id'] === $aktuellerTermin['kurs_id']) ? 'selected' : ''; ?>
                        <option value="<?= $kurs['kurs_id'] ?>" <?= $selected ?>>
                            <?= h($kurs['bezeichnung']) ?>
                        </option>
                <?php endforeach; ?>
            </select>
        </div>
        <!-- Dropdown mit den Räumen -->
        <div class="feld">
            <label>Raum</label>
             <select name="raum">
                <?php foreach ($raumListe as $raum): ?>
                    <?php $selected = ($raum['raum_id'] === $aktuellerTermin['raum_id']) ? 'selected' : ''; ?>
                        <option value="<?= $raum['raum_id'] ?>"<?= $selected ?>>
                            <?= h($raum['lage']) . ' - ' . h($raum['beschreibung']) ?>
                        </option>
                <?php endforeach; ?>
            </select>
        </div>
    <!-- Dropdown mit den Trainern -->
        <div class="feld">
            <label>Trainer</label>
             <select name="trainer">
                <?php foreach ($trainerListe as $trainer): ?>
                    <?php $selected = ($trainer['trainer_id'] === $aktuellerTermin['trainer_id']) ? 'selected' : ''; ?>
                        <option value="<?= $trainer['trainer_id'] ?>"<?= $selected ?>>
                            <?= h($trainer['nachname']) . ' ' . h($trainer['vorname']) . ' (ID:' .  h($trainer['trainer_id']) . ')' ?>
                        </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="feld">
            <label>Datum</label>
            <input type="date" name="datum" value="<?= h($aktuellerTermin['datum']) ?>">
        </div>
        <div class="feld">
            <label>Beginn</label>
            <input type="time" name="start_zeit" value="<?= h($aktuellerTermin['start_zeit']) ?>">
        </div>
        <div class="feld">
            <label>Ende</label>
            <input type="time" name="ende_zeit" value="<?= h($aktuellerTermin['ende_zeit']) ?>">
        </div>

        <div class="form-aktionen">
            <button type="submit" class="btn-save">Speichern</button>
            <a href="index.php?bereich=kurse" class="btn-cancel">Abbrechen</a>
        </div>
        
    </form>
    <?php
    return; 
}

//Detailansicht für Kurse
if ($kurs_id > 0) {

    // Datensatz mit der ID = x holen
    $sql = "SELECT kt.kurstermin_id,
                   kt.datum,
                   kt.start_zeit,
                   kt.ende_zeit,
                   k.kurs_id,
                   k.bezeichnung,
                   r.raum_id,
                   r.lage,
                   r.beschreibung,
                   t.vorname,
                   t.nachname
            FROM kurstermin kt
            Left JOIN kurs k ON kt.kurs_id = k.kurs_id
            LEFT JOIN raum r ON kt.raum_id = r.raum_id
            LEFT JOIN trainer t ON kt.trainer_id = t.trainer_id
            WHERE k.kurs_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$kurs_id]);

    $termine = $stmt->fetchAll(); 

    // Erst prüfen, ob Mitgliedschaft überhaupt existiert
    if (!$termine) {
        echo '<h1>Kurstermine</h1>';
        echo '<p class="hinweis">Keine Termine gefunden.</p>';
        echo '<p><a href="index.php?bereich=kurse" class="btn-cancel">&laquo; Zurück zur Liste</a></p>';
        return;
    }

    // Mitgliedsinfo für Dropdown
    $aktuelleTrainer = "";
    if (!empty($trainer['trainer_id'])) {
        $aktuelleTrainer = $trainer['vorname'] . ' ' . $trainer['nachname'] . ' (ID: ' . $trainer['trainer_id'] . ')';
    }
    ?>

    <h1>Kursinformationen</h1>

    <form method="POST" action="index.php?bereich=kurse&kurs_id=<?= $id ?>&action=speichern">
        
        <h2>
            <?= h($termine[0]['bezeichnung']) ?>
        </h2>
        <p>Zum Bearbeiten Kurstermin auswählen</p>
        
        <table class="kurstabelle">
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Zeit</th>
                    <th>Raum / Lage</th>
                    <th>Trainer</th>
                    <th>Teilnehmerzahl</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($termine as $termin): ?>
                    <?php $link = 'index.php?bereich=kurse&kurs_id=' . (int)$termin['kurs_id'] . '&action=bearbeiten&termin_id=' . (int)$termin['kurstermin_id']; ?>
                    <?php 
                     //Anzahl Teilnehemr für den Termin holen

                        $anzahlsql = "SELECT count(*)
                                    FROM kursteilnahme ktn
                                    JOIN kurstermin kt ON kt.kurstermin_id = ktn.kurstermin_id
                                    JOIN kurs k ON kt.kurs_id = k.kurs_id 
                                    WHERE ktn.kurstermin_id = ?";
                        $stmt = $pdo->prepare($anzahlsql);
                        $stmt->execute([$termin['kurstermin_id']]);
                        $anzahl = $stmt->fetch();
                        ?>
                    <tr>
                        <td><a href="<?= $link ?>"><?= h($termin['datum'] ? date('d.m.Y', strtotime($termin['datum'])) : 'Unbekannt') ?></a></td>
                        <td><a href="<?= $link ?>"><?= h($termin['start_zeit']) . ' - ' . h($termin['ende_zeit']) ?></a></td>
                        <td><a href="<?= $link ?>"><?= h($termin['lage']) . ' - ' . h($termin['beschreibung']) ?></a></td>
                        <td><a href="<?= $link ?>"><span class="vollname"><?= h($termin['vorname']) ?> <?= h($termin['nachname']) ?></span></a></td>
                        <td><a href="<?= $link ?>"><?= h($anzahl['count(*)']) ?></a></td>
                        <td><a href="index.php?bereich=kurse&kurs_id=<?= (int)$termin['kurs_id'] ?>&action=loeschen&termin_id=<?= (int)$termin['kurstermin_id'] ?>" 
                                class="btn-delete" 
                                onclick="return confirm('Möchten Sie diesen Termin wirklich dauerhaft löschen?');">Löschen</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>        

        <div class="form-aktionen">
                <a href="index.php?bereich=kurse" class="btn-cancel">Zurück</a>
        </div>
        
    </form>

    <?php
    return; 
}

//Detailansicht für Raumbelegung
if ($raum_id > 0) {

    // Datensatz mit der ID = x holen
    $sql = "SELECT kt.kurstermin_id,
                   kt.datum,
                   kt.start_zeit,
                   kt.ende_zeit,
                   k.kurs_id,
                   k.bezeichnung,
                   r.raum_id,
                   r.lage,
                   r.beschreibung,
                   t.vorname,
                   t.nachname
            FROM kurstermin kt
            Left JOIN kurs k ON kt.kurs_id = k.kurs_id
            LEFT JOIN raum r ON kt.raum_id = r.raum_id
            LEFT JOIN trainer t ON kt.trainer_id = t.trainer_id
            WHERE r.raum_id = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$raum_id]);
    $termine = $stmt->fetchAll(); 

    // Erst prüfen, ob Mitgliedschaft überhaupt existiert
    if (!$termine) {
        echo '<h1>Raumbelegung</h1>';
        echo '<p class="hinweis">Keine Termine gefunden.</p>';
        echo '<p><a href="index.php?bereich=kurse" class="btn-cancel">&laquo; Zurück zur Liste</a></p>';
        return;
    }

    // Mitgliedsinfo für Dropdown
    $aktuelleTrainer = "";
    if (!empty($trainer['trainer_id'])) {
        $aktuelleTrainer = $trainer['vorname'] . ' ' . $trainer['nachname'] . ' (ID: ' . $trainer['trainer_id'] . ')';
    }
    ?>

    <h1>Raumbelegung</h1>

    <form method="POST" action="index.php?bereich=kurse&raum_id=<?= $raum_id ?>&action=speichern">
        
        <h2>
            <?= h($termine[0]['lage']) . ' - ' . h($termine[0]['beschreibung']) ?>
        </h2>
        
        <table class="raumtabelle">
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Zeit</th>
                    <th>Kurs</th>
                    <th>Trainer</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($termine as $termin): ?>
                    <tr>
                        <td><?= h($termin['datum'] ? date('d.m.Y', strtotime($termin['datum'])) : 'Unbekannt') ?></td>
                        <td><?= h($termin['start_zeit']) . ' - ' . h($termin['ende_zeit']) ?></td>
                        <td><?= h($termin['bezeichnung']) ?></td>
                        <td><span class="vollname"><?= h($termin['vorname']) ?> <?= h($termin['nachname']) ?></span></td>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>        

        <div class="form-aktionen">
                <a href="index.php?bereich=kurse" class="btn-cancel">Zurück</a>
        </div>
        
    </form>

    <?php
    return; 
}
?>

<h1>Kurstermine und Raumbelegung</h1>
<p>Klicken Sie auf eine Zelle, um alle Details anzuzeigen.</p>

<?php if ($id === 0): ?>
    <h2>Kurs</h2>
    <div class="kurs-kachel-grid">
        <?php foreach ($kursListe as $k): ?>
            <?php $link = 'index.php?bereich=kurse&kurs_id=' . (int)$k['kurs_id']; ?>
            <a href="<?= $link ?>" class="kurs-kachel">
                <div class="kachel-inhalt">
                    <h3><?= h($k['bezeichnung']) ?></h3>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
    <hr>
    <h1>Raum</h1>
    <div class="kurs-kachel-grid">
        <?php foreach ($raumListe as $r): ?>
            <?php $link = 'index.php?bereich=kurse&raum_id=' . (int)$r['raum_id']; ?>
            <a href="<?= $link ?>" class="kurs-kachel">
                <div class="kachel-inhalt">
                    <h3><?= h($r['lage']) .' - ' .  h($r['beschreibung']) ?></h3>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
    <hr>
<?php endif; ?>