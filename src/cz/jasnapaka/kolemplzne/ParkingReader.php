<?php

namespace cz\jasnapaka\kolemplzne;

class ParkingReader
{
	private $jsonUrl;

	public function __construct($jsonUrl) {
		$this->jsonUrl = $jsonUrl;
	}

	public function getHeatmapData() {
		$result = file_get_contents($this->jsonUrl);
		if (!$result) {
			return "";
		}

		$obj = json_decode($result);

		$output = "";
		foreach ($obj->items as $item) {
			$lat = $item->rent_end_lat;
			$lng = $item->rent_end_lon;

			if (strlen($lat) == 0 || strlen ($lng) == 0) {
				continue;
			}

			if (strlen($output) > 0) {
				$output.=",";
			}

			$output.= sprintf("{lat: %s, lng: %s, count: 1}\n", $lat, $lng);
		}

		return $output;
	}
}