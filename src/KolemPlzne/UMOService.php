<?php

namespace KolemPlzne;


class UMOService
{
	private $lat;
	private $long;

	private $output;
	private $headers;

	function __construct($lat, $long)
	{
		$this->lat = $lat;
		$this->long = $long;

		$this->callRemote();
	}

	protected function callRemote()
	{
		$output = @file_get_contents(sprintf(UMO_SERVICE . "?lat=%f&long=%f",
			$this->lat, $this->long));
		$this->output = $output;
		$this->headers = $http_response_header;
	}

	public function getStatus()
	{
		$headers = $this->headers;
		return $headers[0];
	}

	public function getOutput() {
		return $this->output;
	}
}