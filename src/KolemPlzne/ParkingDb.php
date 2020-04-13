<?php

namespace KolemPlzne;

use PDO;

include_once "ParkingDbInterface.php";

class ParkingDb implements ParkingDbInterface
{
	private $connectionCache;


	private function getPDOConnection() {
		try {
			if ($this->connectionCache == null) {
				$this->connectionCache = new PDO(sprintf("mysql:dbname=%s;host=%s",
					MYSQL_DB, MYSQL_SERVER), MYSQL_USER, MYSQL_PASS,
					array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
			}

			return $this->connectionCache;
		} catch (PDOException $e) {
			die('Chyba pri pripojeni do databaze: ' . $e->getMessage());
		}
	}


	public function getRentExists($rent_id)
	{
		$pdo = $this->getPDOConnection();
		$query = $pdo->prepare("SELECT 1 FROM rents WHERE rent_id = :rent_id");
		$query->execute(array(
			":rent_id" => $rent_id
		));

		return ($query->fetchColumn() ? true : false);
	}

	public function writeData($data)
	{
		$pdo = $this->getPDOConnection();
		$query = $pdo->prepare("INSERT into rents (rent_end_datetime, bike_label, user_first_name, rent_end_lon, 
				user_username, rent_start_lat, bike_code, user_id, rent_start_datetime, rent_id, user_last_name, 
				bike_id, rent_end_lat, rent_start_lon, area, part) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $data->rent_start_datetime = str_replace("CET", "", $data->rent_start_datetime);
        $data->rent_end_datetime = str_replace("CET", "", $data->rent_end_datetime);

		return $query->execute(array($data->rent_end_datetime, $data->bike_label,
            isset($data->user_first_name) ? $data->user_first_name : "", $data->rent_end_lon,
			isset($data->user_username) ? $data->user_username : "", $data->rent_start_lat, isset($data->bike_code) ? $data->bike_code : "", $data->user_id, $data->rent_start_datetime,
			$data->rent_id, isset($data->user_last_name) ? $data->user_last_name : "", $data->bike_id,
            $data->rent_end_lat, $data->rent_start_lon, $data->area, $data->part));
	}

	public function getCount()
	{
		$pdo = $this->getPDOConnection();
		$query = $pdo->query("SELECT count(*) FROM rents WHERE rent_end_datetime IS NOT NULL");
		return $query->fetchColumn();
	}

	public function getDataAll()
	{
		$pdo = $this->getPDOConnection();
		$query = $pdo->query("SELECT * FROM rents WHERE rent_end_datetime IS NOT NULL");
		return $query->fetchAll(PDO::FETCH_CLASS);
	}

	public function getDataYear($year)
	{
		$pdo = $this->getPDOConnection();
		$query = $pdo->prepare("SELECT * FROM rents WHERE year(rent_end_datetime) = ?");
		$query->execute(array($year));
		return $query->fetchAll(PDO::FETCH_CLASS);
	}

	public function getYears()
	{
		$pdo = $this->getPDOConnection();
		$query = $pdo->query("SELECT DISTINCT year(rent_end_datetime) as rok FROM rents ORDER BY rok DESC");
		return $query->fetchAll(PDO::FETCH_COLUMN, 0);
	}

}