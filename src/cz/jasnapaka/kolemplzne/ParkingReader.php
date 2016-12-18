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
	const IGNORE_PARKING_M = 20;

	private $jsonUrl;
	private $jsonObjCache;
	private $dataYearsCache;

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
	 * Získání roků, pro které je možné získat statistiky (jsou obsaženy ve vstupním JSONu).
	 *
	 * @return array|bool Vrací false, pokud se nepodařilo roky získat nebo pole s roky,
	 * pro jaké jsou statistiky k dispozici.
	 */
	public function getDataYears() {
		if ($this->dataYearsCache != null) {
			return $this->dataYearsCache;
		}

		$years = array();

		$obj = $this->getJsonData();
		if (!$obj) {
			return false;
		}

		$items = $obj->items;
		foreach ($items as $item) {
			if ($item->rent_end_datetime == null) {
				continue;
			}

			$date = strtotime($item->rent_end_datetime);
			$currentYear = (int) date ('Y', $date);

			$found = false;
			foreach ($years as $year) {
				if ($year === $currentYear) {
					$found = true;
				}
			}

			if (!$found) {
				$years[] = $currentYear;
			}
		}

		rsort($years);
		$this->dataYearsCache = $years;

		return $years;
	}

	/**
	 * Vrátí rok, pro který se budou dle URL zobrazovat statistiky. Pokud není rok
	 * specifikován, bude se používat aktuální rok.
	 *
	 * @return int
	 */
	public function getCurrentYear() {
		if (isset($_GET["year"])) {
			$year = (int) $_GET["year"];
			if ($year > 0) {
				return $year;
			}
		}

		$years = $this->getDataYears();
		return $years[0];
	}

	/**
	 * Vrátí data pro zvolený rok.
	 *
	 * @param $year zvolený rok
	 * @return array pole s daty
	 */
	public function getDataYear($year) {
		$data = array();

		$obj = $this->getJsonData();
		if (!$obj) {
			return $data;
		}

		$items = $obj->items;
		foreach ($items as $item) {
			$lat = $item->rent_end_lat;
			$lng = $item->rent_end_lon;

			if (strlen($lat) == 0 || strlen ($lng) == 0) {
				continue;
			}

			if ($item->rent_end_datetime == null) {
				continue;
			}

			$date = strtotime($item->rent_end_datetime);
			$itemYear = date ('Y', $date);
			if ($itemYear == $year) {
				$data[] = $item;
			}
		}

		return $data;
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
			$items = $this->uniqueParking($items);
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

	/**
	 * Ze seznamu s parkováním odstraní ta parkování, kdy jeden uživatel
	 * služby na stejném místě parkuje opakovaně. V takovém případě vracíme
	 * pouze jeden záznam. Za stejná parkování se považují ta, kdy je
	 * vzdálenost dvou souřadnic do dvou metrů.
	 *
	 * @param array $items seznam parkování
	 * @return array zredukovaný seznam parkování
	 */
	private function uniqueParking($items) {
		$userMap = array();
		$newItems = array();

		foreach ($items as $item) {
			$ignore = false;

			if (isset($userMap[$item->user_id])) {

				foreach ($userMap[$item->user_id] as $newItem) {
					if ($item->user_id == $newItem->user_id) {
						$distance = $this->distance($item->rent_end_lat, $newItem->rent_end_lon,
								$item->rent_end_lat, $item->rent_end_lon) * 1000;
						if ($distance <= self::IGNORE_PARKING_M) {
							$ignore = true;
						}

					}
				}
			} else {
				$userMap[$item->user_id] = array();
			}

			if (!$ignore) {
				$newItems[] = $item;
				$userMap[$item->user_id][] = $item;
			}

		}

		return $newItems;
	}

	/**
	 * Vrátí vzdálenost dvou souřadnic v kilometrech.
	 * http://stackoverflow.com/a/11178145
	 *
	 * @param $lat1
	 * @param $lon1
	 * @param $lat2
	 * @param $lon2
	 * @return float
	 */
	private function distance($lat1, $lon1, $lat2, $lon2) {

		$pi80 = M_PI / 180;
		$lat1 *= $pi80;
		$lon1 *= $pi80;
		$lat2 *= $pi80;
		$lon2 *= $pi80;

		$r = 6372.797; // mean radius of Earth in km
		$dlat = $lat2 - $lat1;
		$dlon = $lon2 - $lon1;
		$a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlon / 2) * sin($dlon / 2);
		$c = 2 * atan2(sqrt($a), sqrt(1 - $a));
		$km = $r * $c;

		return $km;
	}
}