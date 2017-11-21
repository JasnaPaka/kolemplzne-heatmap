# Heatmapa parkování kol KolemPlzne
[![Stav buidu](https://travis-ci.org/JasnaPaka/kolemplzne-heatmap.svg?branch=master)](https://travis-ci.org/JasnaPaka/kolemplzne-heatmap)

Stránka s heatmapou zobrazující parkování kol bikesharingu [KolemPlzne](https://www.kolemplzne.cz/).

# Požadavky
* Webový server s PHP 5.3 či vyšší.
* Databázový server MySQL (testována verze 5.6.x).
* Přístup k API KolemPlzne.cz.

## Instalace
* Nakopírovat obsah projektu na webový server.
* Soubor *config-default.php* přejmenovat na *config.php*. 
* Doplnit do souboru *config.php* cestu k JSONu s informací o parkování kol (nejčastěji webová URL), doplnit klíč k API Google Maps a přístupové údaje k MySQL. V URL k JSONu lze uvést *%s* místo hodnoty u parametru *date_from*, stáhnou se pak data za posledních 6 měsíců (vhodné pro inkrementální aktualizace).
* Do databáze MySQL naimportovat SQL skript, který je v adresáři *sql*.
