<?php

include_once dirname(__FILE__) . '/app/property.php';

class PropertyTest extends PHPUnit_Framework_TestCase {

	public function testCalcRent() {
		$property = new Property(1, 2, 3, 4, 5, 6, 7, 8,
			9, 10, 11, 12, 13, 14);
		$this->assertTrue(true, 'OMG TRUE ISN\'T TRUE!!!');
	}

}
