<?php

use KolemPlzne\ParkingWriter;
use KolemPlzne\ParkingDb;

include_once "config.php";
include_once ROOT."/src/KolemPlzne/ParkingWriter.php";
include_once ROOT."/src/KolemPlzne/ParkingDb.php";

spl_autoload_register(function ($class) {
	include "config.php";
});

if (!set_time_limit(9999)) {
	die ("Nepodarilo se nastavit casovy limit skriptu.");
}

if (!ini_set('default_socket_timeout', 900)) {
	die ("Nepodarilo se nastavit casovy limit pro sitove spojeni.");
}

$writer = new ParkingWriter(new ParkingDb());
$result = $writer->process();

if ($result) {
	print "Aktualizace probehla v poradku.";
} else {
	print ("Pri aktualizaci nastala chyba.");
}