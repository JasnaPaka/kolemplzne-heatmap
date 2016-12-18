<?php

// Root projektu, netřeba měnit
define ('ROOT', __DIR__);

// Cesta k JSON z API KolemPlzne. Obsahuje informace, kde parkovala kola
define ('DATA', "");

// Klíč ke Google Maps
// (viz https://developers.google.com/maps/documentation/javascript/get-api-key)
define ('GM_API_KEY', "");

// Služba pro detekci městské části na základě GPS souřadnice
define ('UMO_SERVICE', "http://tools.jasnapaka.com/mestske-obvody-plzen/service.php");

// Zda se jedná o produkční prostředí (true) nebo ne (false)
define ('PRODUCTION', true);