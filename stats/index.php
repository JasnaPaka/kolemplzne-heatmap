<?php
    use KolemPlzne\ParkingReader;
    use KolemPlzne\ParkingStats;
    use KolemPlzne\ParkingDb;

    include_once "./../config.php";
    include_once ROOT . "/src/KolemPlzne/ParkingReader.php";
    include_once ROOT . "/src/KolemPlzne/ParkingStats.php";
    include_once ROOT . "/src/KolemPlzne/ParkingDb.php";

    $reader = new ParkingReader(new ParkingDb());

    /** Vrácení českého názvu měsíce
     * @param int 1-12
     * @return string
     * @copyright Jakub Vrána, https://php.vrana.cz/
     */
    function cesky_mesic($mesic) {
        static $nazvy = array(1 => 'leden', 'únor', 'březen', 'duben', 'květen', 'červen', 'červenec', 'srpen', 'září', 'říjen', 'listopad', 'prosinec');
        return $nazvy[$mesic];
    }

    /** Vrácení českého názvu dne v týdnu
     * @param int 1-7, 1 je pondělí
     * @return string
     * @copyright Jakub Vrána, https://php.vrana.cz/
     */
    function cesky_den($den) {
        static $nazvy = array(1 => 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota', 'neděle');
        return $nazvy[$den];
    }

    function getUMOName($code) {
		preg_match_all('!\d+!', $code, $matches);
		return "Plzeň ".($matches[0][0]);
    }
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8" />
    <title>Statistika KolemPlzne.cz</title>
    <link href="./../css/style.css" rel="stylesheet" />
</head>
<body>

<div id="stats-content">

<h1>Statistika KolemPlzne.cz</h1>

<?php

$years = $reader->getDataYears();
if (sizeof($years) == 0) {
    print('<p class="error">Není k dispozici žádná statistika</p>');
} else {
    // Výčet roků
    print '<p id="years">';
    $isFirst = true;
    foreach ($reader->getDataYears() as $year) {

		if (!$isFirst) {
			print " | ";
		}

		if ($year == $reader->getCurrentYear()) {
            print $year;
        } else {
			printf('<a href="./?year=%d">%d</a>', $year, $year);
        }

		$isFirst = false;
    }
    print '</p>';

	$stats = new ParkingStats($reader->getDataYear($reader->getCurrentYear()), $reader->getCurrentYear());
    printf ('<p><strong>Celkem jízd v roce %d</strong>: %d</p>', $reader->getCurrentYear(), $stats->getCount());

    // Přehled parkování dle městských obvodů
    print '<h2>Parkování dle městských obvodů</h2>';
    $umo = $stats->getStatsByUMO();
    if (!$umo) {
        print ("<p>Informace o parkování v městských obvodech není k dispozici.</p>");
    } else {
		print '<table>';
		foreach ($umo as $key => $value) {
			printf ("<tr><th>%s</th><td class='r'>%d</td></tr>", $key, $value);
		}
		print '</table>';
		print '<p class="note"><strong>Poznámka</strong>: Parkování mimo území Plzně není v tabulce zahrnuto.</p>';
    }

	// Přehled parkování dle částí městských obvodů
	print '<h2>Parkování dle částí městských obvodů</h2>';
	$umo = $stats->getStatsByUMOPart();
    print '<table>';
    foreach ($umo as $key => $value) {
        printf ("<tr><th>%s</th><td class='r'>%d</td></tr>", $key, $value);
    }
    print '</table>';
    print '<p class="note"><strong>Poznámka</strong>: Parkování mimo území Plzně není v tabulce zahrnuto.</p>';

    // Přehled parkování dle měsíce
	print '<h2>Počet výpůjček dle měsíce</h2>';
	print '<table>';
	foreach ($stats->getStatsByMonth() as $key => $value) {
	    printf ("<tr><th>%s</th><td class='r'>%d</td></tr>", cesky_mesic($key), $value);
    }
	print '</table>';

    // Přehled parkování dle dne v týdnu
	print '<h2>Počet výpůjček dle dne v týdnu</h2>';
	print '<table>';
	foreach ($stats->getStatsByDayOfWeek() as $key => $value) {
		printf ("<tr><th>%s</th><td class='r'>%d</td></tr>", cesky_den($key), $value);
	}
	print '</table>';

	// Půjčování dle délky
	print '<h2>Výpůjčky dle délky</h2>';
	$statsLength = $stats->getStatsByLength();
}
?>

<table>
    <tr><th>do 30 minut</th><td><?php print $statsLength[0] ?></td>
    <tr><th>30 minut až 1 hodina</th><td><?php print $statsLength[1] ?></td>
    <tr><th>1 hodina až 2 hodiny</th><td><?php print $statsLength[2] ?></td>
    <tr><th>2 hodiny až 4 hodiny</th><td><?php print $statsLength[3] ?></td>
    <tr><th>4 hodiny až 6 hodin</th><td><?php print $statsLength[4] ?></td>
    <tr><th>více než 6 hodin</th><td><?php print $statsLength[5] ?></td>
</table>

</div>

</body>
</html>