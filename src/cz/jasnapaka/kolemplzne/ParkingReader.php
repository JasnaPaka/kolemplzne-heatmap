<?php

namespace cz\jasnapaka\kolemplzne;

/**
 * Třída ParkingReader čte informace o parkování kol ze vstupního JSONu.
 *
 * @package cz\jasnapaka\kolemplzne
 * @author Pavel Cvrček
 */
class ParkingReader
{
	private $jsonUrl;

	/**
	 * ParkingReader constructor.
	 * @param $jsonUrl cesta ke vstupnímu JSONu.
	 */
	public function __construct($jsonUrl) {
		$this->jsonUrl = $jsonUrl;
	}

	/**
	 * Vrací data pro zobrazení základní heatmapy.
	 *
	 * @return string|boolean Vrací data pro heatmapu či false,
	 * pokud se je nepodaří načíst.
	 */
	public function getHeatmapData() {
		$result = file_get_contents($this->jsonUrl);
		if (!$result) {
			return false;
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