<?php

namespace KolemPlzne;

use PHPUnit\Framework\TestCase;
use KolemPlzne\ParkingStats;

include ROOT_PROJECT."/KolemPlzne/ParkingStats.php";

class ParkingStatsTest extends TestCase
{

    public function testGetStatsByDayOfWeek() {
        $data = array();

        $item = new \stdClass();
        $item->rent_start_datetime = "2019-04-01 13:28:30";
        $data[] = $item;

        $stats = new ParkingStats($data, 2019);
        $this->assertNotNull($stats->getStatsByDayOfWeek()[1]);
    }
}