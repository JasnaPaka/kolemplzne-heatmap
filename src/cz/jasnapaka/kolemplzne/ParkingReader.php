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
	private $jsonObjCache;

	/**
	 * ParkingReader constructor.
	 * @param $jsonUrl cesta ke vstupnímu JSONu.
	 */
	public function __construct($jsonUrl) {
		$this->jsonUrl = $jsonUrl;
	}

	/**
	 * Načte vstupní JSON nebo použije již nakešované načtení.
	 *
	 * @return bool|mixed Načtený JSON nebo false, pokud se jej
	 * nepodařilo načíst.
	 */
	private function getJsonData() {
		if ($this->jsonObjCache != null) {
			return $this->jsonObjCache;
		}

		if (!file_exists($this->jsonUrl)) {
			return false;
		}

		$result = file_get_contents($this->jsonUrl);
		if (!$result) {
			return false;
		}

		$obj = json_decode($result);
		if (!$obj) {
			return false;
		}
		$this->jsonObjCache = $obj;

		return $obj;
	}

	/**
	 * Zjistí počet půjčení kol.
	 *
	 * @return bool Celkový počet zapůjčení kol dle JSONu nebo false,
	 * pokud se načtení nezdařilo.
	 */
	public function getCount() {
		$obj = $this->getJsonData();
		if (!$obj) {
			return false;
		}

		return $obj->total_count;
	}

	public function getData() {
		$obj = $this->getJsonData();
		if (!$obj) {
			return false;
		}

		return $obj->items;
	}

	/**
	 * Vrací data pro zobrazení základní heatmapy.
	 *
	 * @param justUnique true, pokud se mají brát pouze unikátní parkování,
	 * jinak false (výchozí). Vysvětlení v kódu níže.
	 *
	 * @return string|boolean Vrací data pro heatmapu či false,
	 * pokud se je nepodaří načíst.
	 */
	public function getHeatmapData($justUnique = false) {

		$obj = $this->getJsonData();
		if (!$obj) {
			return false;
		}

		$items = $obj->items;
		if ($justUnique) {
			// TODO
		}

		$output = "";
		foreach ($items as $item) {
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