

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
    <input type="hidden" name="bereich" value="stat_kurse">
    <label for="statistik-select">Statistik auswählen</label>
    <select name="statistik" id="statistik-select" onchange="this.form.submit()">
        <option value="">Bitte auswählen</option>
        <option value="top10" <?= $auswahl === 'top10' ? 'selected' : '' ?>>Top 10 meistbesuchte Kurse</option>
        <option value="auslastung" <?=$auswahl === 'auslastung' ? 'selected' : '' ?>>Kursauslastung nach Wochentag</option>
        <option value="mitglieder" <?=$auswahl === 'mitglieder' ? 'selected' : '' ?>>Aktivste Mitglieder</option>
    </select>
</form>
<?php

switch ($auswahl){
    // Top 5 der meistbesuchten Kurse
    case "top10":
        $sql = "SELECT COUNT(*) AS anzahl, k.bezeichnung
                FROM kursteilnahme ktn
                JOIN kurstermin kt ON kt.kurstermin_id = ktn.kurstermin_id
                JOIN kurs k ON kt.kurs_id = k.kurs_id 
                GROUP BY k.kurs_id 
                ORDER BY anzahl DESC
                LIMIT 10";

        $topKurse = $pdo->query($sql)->fetchAll();
        ?>
        <div class="statistik-box">
            <h2>Top 10 Kurse nach Teilnahmen</h2>
            
            <table class="statistik-tabelle">
                <thead>
                    <tr>
                        <th>Platz</th>
                        <th>Kursbezeichnung</th>
                        <th>Anzahl Teilnahmen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $platz = 1; 
                    foreach ($topKurse as $kurs): 
                    ?>
                        <tr>
                            <td><?= $platz++ ?></td>
                            <td><span class="vollname"><?= h($kurs['bezeichnung']) ?></span></td>
                            <td><strong><?= h($kurs['anzahl']) ?></strong> Besuche</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php break; 

    case 'auslastung':
        //An welchen Tagen sind die meisten Teilanhmen an den Kursen
        //DAYNAME() gibt den Tag zu einem Datum zurück (Monday, Tuesday, ...)
        //WEEKDAY() gibt den Tag als int zurück (0 für Monday, ..., 6 für Sunday)
        $sql = "SELECT DAYNAME(kt.datum) AS tag, COUNT(*) as anzahl
                FROM kursteilnahme ktn
                JOIN kurstermin kt ON kt.kurstermin_id = ktn.kurstermin_id
                GROUP BY tag
                ORDER BY WEEKDAY(kt.datum)";

        $auslastung = $pdo->query($sql)->fetchAll();
        
        //Konvertierung der Wochentage von Enlisch nach Deutsch mithilfe eines Schlüssel-Wert-Paares
        $wochentage = [
            'Monday'    => 'Montag',
            'Tuesday'   => 'Dienstag',
            'Wednesday' => 'Mittwoch',
            'Thursday'  => 'Donnerstag',
            'Friday'    => 'Freitag',
            'Saturday'  => 'Samstag',
            'Sunday'    => 'Sonntag'
            ];?>
        
            <div class="statistik-box">
            <h2>Auslastung nach Wochentagen</h2>
            
            <table class="statistik-tabelle">
                <thead>
                    <tr>
                        <th>Wochentag</th>
                        <th>Anzahl Teilnahmen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($auslastung as $a): 
                    ?>
                        <tr>
                            <td><?= h($wochentage[$a['tag']]) ?></td>
                            <td><strong><?= h($a['anzahl']) ?></strong> Teilnahmen</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        break;

    case 'mitglieder':
        //Welche Mitglieder haben die meisten Kurse belegt
        $sql = "SELECT COUNT(*) AS anzahl, m.vorname, m.nachname, m.anrede
                FROM kursteilnahme ktn
                JOIN mitglied m ON m.mitglied_id = ktn.mitglied_id 
                GROUP BY m.mitglied_id
                ORDER BY anzahl DESC
                LIMIT 10";
        $topMitglieder = $pdo->query($sql)->fetchAll();
        ?>
        <div class="statistik-box">
            <h2>Top 10 Mitglieder mit den meisten Kursteilnahmen</h2>
            
            <table class="statistik-tabelle">
                <thead>
                    <tr>
                        <th>Platz</th>
                        <th>Name</th>
                        <th>Anzahl Kursteilnahmen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $platz = 1; 
                    foreach ($topMitglieder as $mitglied): 
                    ?>
                        <tr>
                            <td><?= $platz++ ?></td>
                            <td class="name-zelle">
                                    <span class="vollname"><?= h($mitglied['vorname']) ?> <?= h($mitglied['nachname']) ?></span>
                                    <span class="anrede-sub"><?= h($mitglied['anrede']) ?></span>
                            </td>
                            <td><strong><?= h($mitglied['anzahl']) ?></strong> Teilnahmen</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        break;

    default:
    ?>
    <h2>Bitte wählen Sie eine Kursstatistik aus dem Menü aus</h2>
    <?php
    break;
}?>


