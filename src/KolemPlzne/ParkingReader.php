<?php

namespace KolemPlzne;

/**
 * Třída ParkingReader čte informace o parkování kol ze vstupního JSONu.
 *
 * @package KolemPlzne
 * @author Pavel Cvrček
 */
class ParkingReader
{
	const IGNORE_PARKING_M = 20;

	private $db;

	/**
	 * ParkingReader constructor.
	 * @param $db Interface pro přístup do databáze.
	 */
	public function __construct(ParkingDbInterface $db) {
		$this->db = $db;
	}

	/**
	 * Zjistí počet půjčení kol.
	 *
	 * @return bool Celkový počet zapůjčení kol dle JSONu nebo false,
	 * pokud se načtení nezdařilo.
	 */
	public function getCount() {
		return $this->db->getCount();
	}

	public function getData() {
		return $this->db->getDataAll();
	}

	/**
	 * Získání roků, pro které je možné získat statistiky (jsou obsaženy ve vstupním JSONu).
	 *
	 * @return array|bool Vrací false, pokud se nepodařilo roky získat nebo pole s roky,
	 * pro jaké jsou statistiky k dispozici.
	 */
	public function getDataYears() {
		return $this->db->getYears();
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
		return $this->db->getDataYear($year);
	}

	/**
	 * Vrací data pro zobrazení základní heatmapy.
	 *
     * @param currentYear rok, pro který se získávají data nebo null,
     * pokud na roku nezáleží
	 * @param justUnique true, pokud se mají brát pouze unikátní parkování,
	 * jinak false (výchozí). Vysvětlení v kódu níže.
	 *
	 * @return string|boolean Vrací data pro heatmapu či false,
	 * pokud se je nepodaří načíst.
	 */
	public function getHeatmapData($currentYear, $justUnique = false) {

	    if ($currentYear != null) {
	        $items = $this->db->getDataYear($currentYear);
        } else {
            $items = $this->db->getDataAll();
        }

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