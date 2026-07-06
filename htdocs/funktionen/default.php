<?php
//Daten aus der Datenbank laden
$anzahlMitglieder = $pdo->query("SELECT COUNT(*) FROM mitglied")->fetchColumn();
$anzahlTrainer    = $pdo->query("SELECT COUNT(*) FROM trainer")->fetchColumn();
$anzahlArmbaender = $pdo->query("SELECT COUNT(*) FROM armband")->fetchColumn();

//Abfrage für Anzahl der kritischen Armbänder (Verloren/Defekt) und der aktiven Armbänder
$anzahlAktiverArmbaender = $pdo->query("SELECT COUNT(*) FROM armband WHERE status_id = 1")->fetchColumn();
$kritischeArmbaender = $pdo->query("SELECT COUNT(*) FROM armband WHERE status_id = 2 OR status_id = 3")->fetchColumn();


?>
<div class="dashboard-wrapper">
    <h1>Studio-Zentrale</h1>
    <p class="Untertitel">Willkommen zurück! Hier ist der aktuelle System-Überblick.</p>

    <div class="dashboard-grid stats-grid">
        <div class="kachel stat-kachel">
            <div class="stat-info">
                <h3>Aktive Mitglieder</h3>
                <p class="stat-zahl"><?= (int)$anzahlMitglieder ?></p>
            </div>
        </div>
        <div class="kachel stat-kachel">
            <div class="stat-info">
                <h3>Trainer-Team</h3>
                <p class="stat-zahl"><?= (int)$anzahlTrainer ?></p>
            </div>
        </div>

        <div class="kachel stat-kachel">
            <div class="stat-info">
                <h3>Aktive RFID-Bänder</h3>
                <p class="stat-zahl"><?= (int)$anzahlAktiverArmbaender ?></p>
            </div>
        </div>
    </div>

    <div class="dashboard-sektion">
        
        <div class="kachel-bereich">
            <h2>Schnellzugriff</h2>
            <div class="aktions-links">
                <a href="index.php?bereich=mitgliedschaften&action=neu" class="dashboard-btn">
                    <span>Neue Mitgliedschaft eintragen</span>
                </a>
                <a href="index.php?bereich=mitglieder&action=neu" class="dashboard-btn">
                    <span>Neues Mitglied eintragen</span>
                </a>
                <a href="index.php?bereich=trainer&action=neu" class="dashboard-btn">
                    <span>Neuen Trainer anlegen</span>
                </a>
                <a href="index.php?bereich=rfid" class="dashboard-btn variant-purple">
                    <span>RFID-Band zuweisen</span>
                </a>
            </div>
        </div>

        <div class="kachel-bereich">
            <h2>System-Check</h2>
            <div class="system-status">

                <?php if ($kritischeArmbaender > 0): ?>
                    <div class="status-zeile status-alarm">
                        <span class="status-punkt"></span>
                        <p><strong>Achtung:</strong> <?= (int)$kritischeArmbaender ?> Armband/Bänder sind als gesperrt oder verloren gemeldet!</p>
                    </div>
                <?php else: ?>
                    <div class="status-zeile status-ok">
                        <span class="status-punkt"></span>
                        <p>Alle ausgegebenen Armbänder sind voll einsatzbereit.</p>
                    </div>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>
