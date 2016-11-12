<?php
	use cz\jasnapaka\kolemplzne\ParkingReader;

	include_once "config.php";
	include_once ROOT."/src/cz/jasnapaka/kolemplzne/ParkingReader.php";

	$reader = new ParkingReader(DATA);
	$data = $reader->getHeatmapData();
?>

<!DOCTYPE html>
<html lang="cs">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />

	<title>Heatmap KolemPlzne - kde parkují kola</title>

	<link href="css/style.css" rel="stylesheet" />


</head>
<body>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php print GM_API_KEY ?>"
></script>
<script src="components/heatmap.js/heatmap.min.js"></script>
<script src="components/heatmap.js/gmaps-heatmap.js"></script>

	<div id="map"></div>

	<script>
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
				var testData = {
					max: 5,
					data: [<?php print $data ?>]
				};

				heatmap.setData(testData);
			});

		}


	</script>




</body>
</html>