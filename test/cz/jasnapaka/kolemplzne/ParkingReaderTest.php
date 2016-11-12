<?php

use cz\jasnapaka\kolemplzne\ParkingReader;

include_once ROOT_PROJECT."/cz/jasnapaka/kolemplzne/ParkingReader.php";

class ParkingReaderTest extends PHPUnit_Framework_TestCase
{

	public function testFailLoad() {
		$reader = new ParkingReader("neexistujici.json");
		$this->assertFalse($reader->getHeatmapData());
		$this->assertFalse($reader->getCount());
		$this->assertFalse($reader->getData());
	}

	public function testSimple() {
		$reader = new ParkingReader(TEST_DATA_DIR."simple.json");
		$this->assertEquals(1, $reader->getCount());
		$this->assertEquals(1, sizeof($reader->getData()));
	}
}