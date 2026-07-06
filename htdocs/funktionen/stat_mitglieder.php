

<?php

// Hilfsfunktion: macht Text fuer die HTML-Ausgabe sicher und den Code leserlicher und kuerzer
// (verhindert, dass z.B. "<script>" im Datenfeld Schaden anrichtet)
function h($wert): string {
    return htmlspecialchars((string)$wert, ENT_QUOTES, 'UTF-8');
}

// Parameter der URL
$auswahl = $_GET['statistik'] ?? '';
$suche = $_GET['suche'] ?? '';
?>
<!-- Dropdown-Menü für die Auswahl der Statistiken -->
<form method="GET" action="index.php">
    <input type="hidden" name="bereich" value="stat_mitglieder">
    <label for="statistik-select">Statistik auswählen</label>
    <select name="statistik" id="statistik-select" onchange="this.form.submit()">
        <option value="">Bitte auswählen</option>
        <option value="altersgruppe" <?= $auswahl === 'altersgruppe' ? 'selected' : '' ?>>Altersgruppen-Analyse</option>
        <option value="geschlecht" <?=$auswahl === 'geschlecht' ? 'selected' : '' ?>>Geschlechterverteilung</option>
        <option value="aktivitaet" <?=$auswahl === 'aktivitaet' ? 'selected' : '' ?>>Durchschnitt der Mitglieder</option>
    </select>
</form>
<?php

switch ($auswahl){
    // Analyse der verschiedenen Altersgruppen
    case "altersgruppe":
        //TIMESTAMPDIFF ermittelt das Alter des Mitglieds, CURDATE() gibt das heutige datum aus
        //CASE WHEN fungiert als alternative zur verschachtelten(unübersichtilichen) IF-Bedingung
        $sql = "SELECT Count(*) as anzahl,
                CASE WHEN TIMESTAMPDIFF(YEAR, m.geburtsdatum, CURDATE()) < 18 THEN 'u18'
                    WHEN TIMESTAMPDIFF(YEAR, m.geburtsdatum, CURDATE()) <= 29 THEN 'u29'
                    WHEN TIMESTAMPDIFF(YEAR, m.geburtsdatum, CURDATE()) <= 49 THEN 'u49'
                    ELSE '50+'
                END AS altersgruppe
                FROM mitglied m
                GROUP BY altersgruppe
                ORDER BY
                CASE WHEN altersgruppe = 'u18' THEN 1
                     WHEN altersgruppe = 'u29' THEN 2
                     WHEN altersgruppe = 'u49' THEN 3
                     WHEN altersgruppe = '50+' THEN 4
                END";

        $altersgruppen = $pdo->query($sql)->fetchAll();
        ?>
        <div class="statistik-box">
            <h2>Altersgruppen-Analyse</h2>
            
            <table class="statistik-tabelle">
                <thead>
                    <tr>
                        <th>Altersgruppe</th>
                        <th>Anzahl Mitglieder</th>
                    </tr>
                </thead>
                <tbody>
                    <?php  
                    foreach ($altersgruppen as $a): 
                        if ($a['altersgruppe'] === 'u18'){
                            $label = 'Unter 18 Jahre';
                        } elseif ($a['altersgruppe'] === 'u29'){
                            $label = '18 - 29 Jahre';
                        } elseif ($a['altersgruppe'] === 'u49'){
                            $label = '30 - 49 Jahre';
                        } else {
                            $label = 'Über 50 Jahre';
                        }
                    ?>
                        <tr>
                            <td><?= $label ?></td>
                            <td><strong><?= h($a['anzahl']) ?></strong> Mitglieder</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php break; 

    case 'geschlecht':
        //Zählen der verschiedenen geschlechtseinträge
        $sql = "SELECT COUNT(m.geschlecht) as anzahl, m.geschlecht
                FROM mitglied m
                GROUP BY m.geschlecht
                ORDER BY anzahl";

        $geschlecht = $pdo->query($sql)->fetchAll();
        ?>
        
            <div class="statistik-box">
            <h2>Geschlechterverteilung</h2>
            
            <table class="statistik-tabelle">
                <thead>
                    <tr>
                        <th>Geschlecht</th>
                        <th>Anzahl</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($geschlecht as $g): 
                        if ($g['geschlecht'] === 'w'){
                            $label_a = 'Weiblich';
                            $label_b = 'weibliche';
                        } elseif ($g['geschlecht'] === 'm'){
                            $label_a = 'Männlich';
                            $label_b = 'männliche';
                        } else {
                            $label_a = 'Divers';
                            $label_b = 'diverse';
                        }
                    ?>
                        <tr>
                            <td><span class="vollname"><?= h($label_a) ?></span></td>
                            <td><?= h($g['anzahl'] . ' ' . $label_b . ' Mitglieder') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        break;

    case 'aktivitaet':
        //Fitnessdtaen der Mitglieder
        $sql = "SELECT g.bezeichnung, 
                       m.vorname,
                       m.nachname,
                       m.anrede,
                       m.mitglied_id,
                       COUNT(*) as anzahl_einheiten,
                       SEC_TO_TIME(AVG(TIME_TO_SEC(TIMEDIFF(t.ende_zeit, t.start_zeit)))) AS zeit_schnitt,
                       AVG(t.gewicht_kg) AS gewicht,
                       AVG(t.wiederholungen) AS wiederholungen,
                       AVG(t.saetze) AS saetze
                FROM trainingseinheit t
                JOIN mitglied m ON t.mitglied_id = m.mitglied_id
                JOIN fitnessgeraet f ON t.fitnessgeraet_id = f.fitnessgeraet_id
                JOIN geraete_typ g ON f.geraetetyp_id = g.geraete_typ_id";
        $parameter = [];

        if(!empty($suche)){
            $sql .= " WHERE m.nachname LIKE ?
                         OR m.vorname LIKE ?";
            
            $suchBegriffMitWildcards = "%" . $suche . "%";
            $parameter = [$suchBegriffMitWildcards, $suchBegriffMitWildcards];
        }

        $sql .= " GROUP BY m.mitglied_id, g.bezeichnung
                  ORDER BY m.nachname, m.vorname";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($parameter);
        $topMitglieder = $stmt->fetchAll();
        ?>

        <div class="statistik-box">
            <h2>Durchschnittlichen Trainingseinheiten der Mitglieder</h2>
            <!-- Suchfeld zur Mitgliedersuche -->
            <div class="such-container">
                <form method="GET" action="index.php">
                    <input type="hidden" name="bereich" value="stat_mitglieder">
                    <input type="hidden" name="statistik" value="aktivitaet">
                    
                    <input type="text" name="suche" value="<?= h($suche) ?>" placeholder="Nach Name suchen ....">
                    
                    <button type="submit" class="btn-save">Suchen</button>
                    
                    <?php if (!empty($suche)): ?>
                        <a href="index.php?bereich=stat_mitglieder&statistik=aktivitaet" class="btn-cancel">Filter löschen</a>
                    <?php endif; ?>
                </form>
            </div>
            <table class="statistik-tabelle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Gerät</th>
                        <th>Anzahl Einheiten</th>
                        <th>Nutzungsdauer</th>
                        <th>Gewicht</th>
                        <th>Wiederholungen</th>
                        <th>Sätze</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $letzte_id = 0;
                    foreach ($topMitglieder as $mitglied): 
                        
                        //Aufsplitten des Zeitwertes in reine Stunden[0], Minuten[1] und Rest[2] um vollgende anzeige zu vermeiden: 00:25:43.5483
                        $stun_Min = explode(':', $mitglied['zeit_schnitt']);
                        //Aufsplitten vom Rest (Sekunden.Millisekunden) auf reine Sekunden[0] und Millisekunden [1]
                        $seku = explode('.', $stun_Min[2]);
                    ?>
                        <tr>
                            <?php
                            if ($letzte_id !== $mitglied['mitglied_id']){
                                ?>
                                <td class="name-zelle">
                                    <span class="vollname"><?= h($mitglied['nachname']) . ", " ?> <?= h($mitglied['vorname']) ?></span>
                                    <span class="anrede-sub"><?= h($mitglied['anrede']) ?></span>
                                </td>
                                <?php
                                $letzte_id = $mitglied['mitglied_id'];
                            }else {
                                ?>
                                <td>

                                </td>
                                <?php
                            }
                        ?>
                            
                            <td><?= h($mitglied['bezeichnung']) ?></td>
                            <td><?= h($mitglied['anzahl_einheiten']) ?></td>
                            <td><?= h($stun_Min[0] . ' Stunden ' . $stun_Min[1] . ' Minuten ' . $seku[0] . ' Sekunden') ?></td>
                            <td><?= h((int)$mitglied['gewicht']) ?></td>
                            <td><?= h((int)$mitglied['wiederholungen']) ?></td>
                            <td><?= h((int)$mitglied['saetze']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        break;

    default:
    ?>
    <h2>Bitte wählen Sie eine Mitgliederstatistik aus dem Menü aus</h2>
    <?php
    break;
}?>


