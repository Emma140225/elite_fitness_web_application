

<?php

// Hilfsfunktion: macht Text fuer die HTML-Ausgabe sicher und den Code leserlicher und kuerzer
// (verhindert, dass z.B. "<script>" im Datenfeld Schaden anrichtet)
function h($wert): string {
    return htmlspecialchars((string)$wert, ENT_QUOTES, 'UTF-8');
}

// Parameter der URL
$auswahl = $_GET['statistik'] ?? '';
?>
<!-- Dropdown-Menü für die Auswahl der Statistiken -->
<form method="GET" action="index.php">
    <input type="hidden" name="bereich" value="stat_geraete">
    <label for="statistik-select">Statistik auswählen</label>
    <select name="statistik" id="statistik-select" onchange="this.form.submit()">
        <option value="">Bitte auswählen</option>
        <option value="arten" <?=$auswahl === 'arten' ? 'selected' : '' ?>>Meistbenutzte Gerätearten</option>
        <option value="zeit" <?=$auswahl === 'zeit' ? 'selected' : '' ?>>Durchschnittliche Zeit pro Gerät</option>
        
    </select>
</form>
<?php
switch ($auswahl){
    // Meistbenutzten Gerätearten
    case "arten":
        $sql = "SELECT COUNT(*) AS anzahl, g.bezeichnung
                FROM mitglied_fitnessgeraet mf
                JOIN fitnessgeraet f ON mf.fitnessgeraet_id = f.fitnessgeraet_id
                JOIN geraete_typ g ON f.geraetetyp_id = g.geraete_typ_id
                GROUP BY g.bezeichnung
                ORDER BY anzahl DESC";

        $topGeraet = $pdo->query($sql)->fetchAll();
        ?>
        <div class="statistik-box">
            <h2>Meistbenutzten Gerätearten</h2>
            
            <table class="statistik-tabelle">
                <thead>
                    <tr>
                        <th>Platz</th>
                        <th>Geräteart</th>
                        <th>Anzahl der Benutzungen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $platz = 1; 
                    foreach ($topGeraet as $g): 
                    ?>
                        <tr>
                            <td><?= $platz++ ?></td>
                            <td><span class="vollname"><?= h($g['bezeichnung']) ?></span></td>
                            <td><strong><?= h($g['anzahl']) ?></strong> Nutzungen</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php break; 

    case 'zeit':

        //Wie lange wird ein Gerät im Schnitt verwendet
        //TIMEDIFF berechnet die zeitliche differenz, TIME_TO_SEC wandelt sie in reine Sekunden um
        //AVG nimmt den Durchschnitt der Sekunden, SEC_TO_TIME wandelt die Sekunden wieder in ein sauberes Zeitformat zur anzeige
        $sql = "SELECT g.bezeichnung, SEC_TO_TIME(AVG(TIME_TO_SEC(TIMEDIFF(t.ende_zeit, t.start_zeit)))) AS schnitt
                FROM trainingseinheit t
                LEFT JOIN fitnessgeraet f ON t.fitnessgeraet_id = f.fitnessgeraet_id
                LEFT JOIN geraete_typ g ON g.geraete_typ_id = f.geraetetyp_id
                GROUP BY g.bezeichnung
                ORDER BY schnitt DESC";

        $zeit = $pdo->query($sql)->fetchAll();

        

        ?>

        <div class="statistik-box">
        <h2>Durchschnittliche Nutzungsdauer eines Gerätetyps</h2>
            
            <table class="statistik-tabelle">
                <thead>
                    <tr>
                        <th>Platz</th>
                        <th>Gerätetyp</th>
                        <th>Durchschnittliche Nutzungsdauer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $platz = 1;
                    foreach ($zeit as $z): 
                        //Aufsplitten des Zeitwertes in reine Stunden[0], Minuten[1] und Rest[2] um vollgende anzeige zu vermeiden: 00:25:43.5483
                        $stun_Min = explode(':', $z['schnitt']);
                        //Aufsplitten vom Rest (Sekunden.Millisekunden) auf reine Sekunden[0] und Millisekunden [1]
                        $seku = explode('.', $stun_Min[2]);
                    ?>
                        <tr>
                            <td><?= h($platz++) ?></td>
                            <td><span class="vollname"><?= h($z['bezeichnung']) ?></span></td>
                            <td><?= h($stun_Min[0] . ' Stunden ' . $stun_Min[1] . ' Minuten ' . $seku[0] . ' Sekunden') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        break;

    default:
    ?>
    <h2>Bitte wählen Sie eine Gerätestatistik aus dem Menü aus</h2>
    <?php
    break;
}?>


