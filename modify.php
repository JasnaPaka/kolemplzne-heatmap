<?php
/**
 * Skript po spuštění provede modifikaci JSONu staženého z KolemPlzne.cz.
 * Podstatou změn je doplnění městských částí dle místa zaparkování pro
 * budoucí statistiky.
 */

use cz\jasnapaka\kolemplzne\ParkingReader;
use cz\jasnapaka\kolemplzne\ParkingStats;

include "config.php";

include_once ROOT."/src/cz/jasnapaka/kolemplzne/ParkingReader.php";
include_once ROOT."/src/cz/jasnapaka/kolemplzne/ParkingStats.php";

$start = microtime(true);

if (PRODUCTION) {
	die ("Nelze spustit v produkcnim prostredi.");
}

if (!set_time_limit(9999)) {
	die ("Nepodarilo se nastavit casovy limit skriptu.");
}

$jsonString = file_get_contents(DATA);
$data = json_decode($jsonString);

$id = 0;
foreach ($data->items as $item) {
	if ($item->rent_end_lat == null || $item->rent_end_lon == null) {
		$item->area = "";
	}

	$id++;

	$output = @file_get_contents(sprintf(UMO_SERVICE."?lat=%f&long=%f",
		$item->rent_end_lat, $item->rent_end_lon));
	if ($output === false) {
		$item->area = "";
	} else {
		$xml = simplexml_load_string($output);
		$item->area = (string) $xml->code;
	}
}

$newJsonString = json_encode($data, JSON_UNESCAPED_UNICODE);
file_put_contents(ROOT.'/data/kolemplzne-new.json', $newJsonString);

$time_elapsed_secs = microtime(true) - $start;
printf ("Uspesne dobehlo. Doba behu: %d (sekundy)", $time_elapsed_secs);