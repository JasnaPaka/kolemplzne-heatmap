<?php

namespace KolemPlzne;

include "UMOService.php";

/**
 * Třída ParkingWriter zapisuje informace o nových výpůjčkách do databáze. Pokud už záznam k výpůjčce existuje,
 * nepřidává se duplicitně.
 *
 * @package KolemPlzne
 * @author Pavel Cvrček
 */
class ParkingWriter {

	private $db;
	
	function __construct(ParkingDbInterface $db)
	{
		$this->db = $db;
	}

	/**
	 * Načte vstupní JSON.
	 *
	 * @return bool|mixed Načtený JSON nebo false, pokud se jej
	 * nepodařilo načíst.
	 */
	private function getJsonData() {

		// stahujeme pouze data 6 měsíců nazpět
		$dt = new \DateTime();
		$dt->modify('-6 month');

		$url = DATA;
		$url = str_replace("%s", $dt->format('Y-m-d'), $url);

		$result = file_get_contents($url);
		if (!$result) {
			print "Nepodarilo se stahnout JSON.\n";
			return false;
		}

		$obj = json_decode($result);
		if (!$obj) {
			print "Nepodarilo se dekodovat stazeny JSON.\n";
			return false;
		}

		return $obj;
	}

	public function process()
	{
		print "Probiha ziskavani JSONu s informacemi o parkovani...\n";

		$data = $this->getJsonData();
		if (!$data) {
			print ("Nepodarilo se nacist JSON.\n");
			return FALSE;
		}

		print "JSON stazen a nacten. Bude probihat zpracovani...\n";
		printf ("Celkem parkovani: %d\n", sizeof($data->items));

		$imported = 0;
		$count = 0;

		foreach ($data->items as $item) {
			if ($item->rent_end_lon == null) {
				continue;
			}

			$count++;
			if ($count % 500 == 0) {
				printf("Zpracovano: %d\n", $count);
			}

			if ($this->db->getRentExists($item->rent_id)) {
				// existující nemá smysl řešit
				continue;
			}

			// doplnime informace o mestske casti
			$item = $this->addUMO($item);

			$this->db->writeData($item);

			$imported++;
		}

		printf ("Pocet nove naimportovanych vypujcek: %d\n", $imported);

		return TRUE;
	}

	protected function addUMO($item) {
		if ($item->rent_end_lat != null && $item->rent_end_lon != null) {
			$service = new UMOService($item->rent_end_lat, $item->rent_end_lon);
			$status = $service->getStatus();
			$output = $service->getOutput();

			if (strpos($status, "200") !== false) {
				$xml = simplexml_load_string($output);
				if ($xml === FALSE) {
					die("Informaci o mestske casti se nepodarilo nacist. Chyba ve vstupnim XML.");
				}

				$item->area = (string)$xml->umo;
				$item->part = (string)$xml->part;
			} elseif (strpos($status, "404") !== false) {
				$item->area = "";
				$item->part = "";
			} else {
				die("Informaci o mestske casti se nepodarilo nacist. Chyba pri volani sluzby.");
			}
		} else {
			$item->area = "";
			$item->part = "";
		}

		return $item;
	}
}