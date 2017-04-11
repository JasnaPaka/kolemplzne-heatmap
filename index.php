<?php
	use KolemPlzne\ParkingReader;
	use KolemPlzne\ParkingDb;

	include_once "config.php";
	include_once ROOT . "/src/KolemPlzne/ParkingReader.php";
    include_once ROOT . "/src/KolemPlzne/ParkingDb.php";

	$reader = new ParkingReader(new ParkingDb());
	$data = $reader->getHeatmapData();
	$dataUnique = $reader->getHeatmapData(true);
	$years = $reader->getDataYears();
?>

<!DOCTYPE html>
<html lang="cs">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Vizualizace míst, kde nejčastěji parkují kola bikesharingu KolemPlzne.cz">

	<title>Heatmap KolemPlzne - kde parkují kola</title>

	<link href="css/style.css" rel="stylesheet" />
</head>
<body>

<div id="buttons">
    <a href="#" onclick="changeVisible()" id="visible-link" ><strong>&minus;</strong></a>
</div>
<div id="options">
	<h1>Heatmap parkování kol KolemPlzne</h1>
	<p>Přehled, kde parkovala kola bikesharingu <a href="https://www.kolemplzne.cz/">KolemPlzne</a>
        v letech <?php print $years[sizeof($years)-1] ?> až <?php print $years[0] ?>. Aktualizováno denně.</p>
	<p id="p-options"><strong>Data mapy</strong>:</p>
	<form>
		<label><input type="radio" name="data" value="all" checked="checked"
			   onclick="changeData('all')">Všechna parkování</label><br />
		<label><input type="radio" name="data" value="unique"
			   onclick="changeData('unique')">Pouze unikátní</label>
		(<abbr title="Zredukováno o opakované parkování uživatelů v jednom místě.">info</abbr>)<br />
	</form>

    <p id="credits">Vytvořil <a href="http://jasnapaka.com/">Pavel Cvrček</a> na základě dat z bikesharingu
        KolemPlzne. Data jsou aktualizována denně.
    </p>
</div>

<script src="https://maps.googleapis.com/maps/api/js?key=<?php print GM_API_KEY ?>"
></script>
<script src="components/heatmap.js/heatmap.min.js"></script>
<script src="components/heatmap.js/gmaps-heatmap.js"></script>

	<div id="map"></div>

	<script>
		var testData = {
			max: 5,
			data: [<?php print $data ?>]
		};

		var testDataUnique = {
			max: 5,
			data: [<?php print $dataUnique ?>]
		};

		window.onload = function(){
			var map;

			map = new google.maps.Map(document.getElementById('map'), {
				center: {lat: 49.74403, lng: 13.36958},
				zoom: 13
			});

			heatmap = new HeatmapOverlay(map,
				{
					radius: 10,
					maxOpacity: .5,
					minOpacity: 0,
					blur: .75
				}
			);

			google.maps.event.addListenerOnce(map, 'idle', function () {
				heatmap.setData(testData);
			});

		}

		function changeData(value) {
			if (value == "all") {
				heatmap.setData(testData);
			} else {
				heatmap.setData(testDataUnique);
			}
		}

		function changeVisible() {
			var control = document.getElementById("visible-link");
			var panel = document.getElementById("options");

			if (control.innerHTML == "+") {
				control.innerHTML = "&minus;";
				panel.style.display = "block";
			} else {
				control.innerHTML = "+";
				panel.style.display = "none";
			}

		}


	</script>

</body>
</html>