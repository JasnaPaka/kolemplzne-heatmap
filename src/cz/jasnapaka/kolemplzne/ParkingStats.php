<?php

namespace cz\jasnapaka\kolemplzne;

/**
 * Třída ParkingStats získává z dat bikesharingu KolemPlzne některé statistické údaje.
 *
 * Data jsou zpracovávána pro konkrétní rok. U vstupních dat v konstruktoru se
 * předpokládá, že jsou pro konkrétní rok (neprovádí se další kontrola).
 *
 * @package cz\jasnapaka\kolemplzne
 * @author Pavel Cvrček
 */
class ParkingStats
{
	private $data;
	private $year;

	function __construct($data, $year)
	{
		$this->data = $data;
		$this->year = $year;
	}

	public function getData() {
		return $this->data;
	}

	public function getYear() {
		return $this->year;
	}

	/**
	 * Získá počet parkování v daném roce.
	 *
	 * @return int Vrací počet zaparkování.
	 */
	public function getCount() {
		$count = 0;

		foreach ($this->data as $item) {
			if ($item->rent_end_lat == null || $item->rent_end_lon == null) {
				continue;
			}

			$count++;
		}

		return $count;
	}

	/**
	 * Získá statistiku parkování kol dle městských částí.
	 *
	 * @return array|bool Vrací false, pokud údaj není dostupný nebo pole, kde klíč je
	 * kód městské části (formát "umoX") a jako hodnota počet zaparkování.
	 */
	public function getStatsByUMO() {
		$umo = array();

		// Nejprve sestavíme data pro POST
		$areaFound = true;
		foreach ($this->data as $item) {
			if ($item->rent_end_lat == null || $item->rent_end_lon == null) {
				continue;
			}

			if (!isset($item->area)) {
				$areaFound = false;
				break;
			}

			if ($item->area == "") {
				continue;
			}

			if (isset($umo[$item->area])) {
				$umo[$item->area]++;
			} else {
				$umo[$item->area] = 1;
			}
		}

		if (!$areaFound) {
			return false;
		}

		ksort($umo);

		return $umo;
	}

	/**
	 * Získá statistiku parkování kol dle měsíce.
	 *
	 * @return array Vrací pole, kde klíčem je číslo měsíce (číslováno od jedničky)
	 * a hodnotou počet zaparkování v daném měsíci.
	 */
	public function getStatsByMonth() {
		$arr = array();

		foreach ($this->data as $item) {
			if ($item->rent_start_datetime == null) {
				continue;
			}

			$date = strtotime($item->rent_start_datetime);
			$itemMonth = (int) date ('m', $date);

			if (isset($arr[$itemMonth])) {
				$arr[$itemMonth]++;
			} else {
				$arr[$itemMonth]=1;
			}
		}

		ksort($arr);

		return $arr;
	}

	/**
	 * Získá statistiku parkování kol dle dne v týdnu.
	 *
	 * @return array Vrací pole, kde klíčem je číslo dne v týdnu (číslováno od jedničky)
	 * a hodnotou počet zaparkování v daném dni v týdnu.
	 */
	public function getStatsByDayOfWeek() {
		$arr = array();

		foreach ($this->data as $item) {
			if ($item->rent_start_datetime == null) {
				continue;
			}

			$date = strtotime($item->rent_start_datetime);
			$itemWeek = ((int) date ('w', $date)) + 1;

			if (isset($arr[$itemWeek])) {
				$arr[$itemWeek]++;
			} else {
				$arr[$itemWeek]=1;
			}
		}

		ksort($arr);

		return $arr;
	}

	/**
	 * Získá statistiku parkování kol dle délky výpůjčky.
	 *
	 * @return array Vrací pole, kde klíčem je skupina od-do a jako hodnota počet
	 * délek výpůjček, které do daného intervalu spadají.
	 */
	public function getStatsByLength() {
		$arr = array(0, 0, 0, 0, 0, 0);

		foreach ($this->data as $item) {
			if ($item->rent_start_datetime == null || $item->rent_end_datetime == null) {
				continue;
			}

			$datetime1 = strtotime($item->rent_start_datetime);
			$datetime2 = strtotime($item->rent_end_datetime);
			$interval  = abs($datetime2 - $datetime1);
			$minutes   = round($interval / 60);

			if ($minutes <= 30) {
				$arr[0]++;
			}
			if ($minutes > 30 && $minutes <= 60) {
				$arr[1]++;
			}
			if ($minutes > 60 && $minutes <= 120) {
				$arr[2]++;
			}
			if ($minutes > 120 && $minutes <= 240) {
				$arr[3]++;
			}
			if ($minutes > 240 && $minutes <= 360) {
				$arr[4]++;
			}
			if ($minutes > 360) {
				$arr[5]++;
			}
		}

		return $arr;
	}
}