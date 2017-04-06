<?php

namespace KolemPlzne;

interface ParkingDbInterface
{
	public function getRentExists($rent_id);
	public function writeData($data);

	public function getCount();
	public function getDataAll();
	public function getDataYear($year);
	public function getYears();
}