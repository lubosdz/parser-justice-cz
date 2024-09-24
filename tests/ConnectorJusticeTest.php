<?php
/**
* Tests for Lookup service for Czech Business Directory parser - justice.cz - Obchodny register SR
* Tests passing PHP 8.0 - 8.2.3 as of 09/2024
*/

use lubosdz\parserJusticeCz\ConnectorJusticeCz;
use PHPUnit\Framework\TestCase;

class ConnectorJusticeTest extends TestCase
{
	public function testSearchCompanies()
	{
		$connector = new ConnectorJusticeCz;

		/**
		Sample response:
		array =
		  0: array =
			name: string = AAA, s.r.o.
			ico: string = 46902058
			city: string = Brno
			addr_city: string = Brno
			addr_zip: string = 63900
			addr_streetnr: string = Jílová 108/2
			addr_full: string = Jílová 108/2, Štýřice, 639 00 Brno
		  1: array =
			name: string = AAA BYTY.CZ akciová společnost
			ico: string = 63999234
			city: string = Praha
			addr_city: string = Praha 1
			addr_zip: string = 11000
			addr_streetnr: string = Na struze 227/1
			addr_full: string = Na struze 227/1, Nové Město, 110 00 Praha 1
			....
		*/
		$data = $connector->findByNazev('AAA');
		$this->assertTrue($data && is_array($data) && !empty($data[0]['name']) && false !== stripos($data[0]['name'], 'AAA'));

		/*
		Sample response:
		array =
		  0: array =
			name: string = Petr Novák s.r.o.
			ico: string = 26072947
			city: string = Praha
			addr_city: string = Praha 19 - Kbely
			addr_zip: string = 19700
			addr_streetnr: string = Toužimská 588/70
			addr_full: string = Praha 19 - Kbely, Toužimská 588/70, okres Hlavní město Praha, PSČ 19700
		  1: array =
			name: string = Petr Novakovský s.r.o.
			ico: string = 06940714
			city: string = Chomutov
			addr_city: string = Chomutov
			addr_zip: string = 43001
			addr_streetnr: string = Revoluční 45/7
			addr_full: string = Revoluční 45/7, 430 01 Chomutov
		*/
		$data = $connector->findByNazev('Petr Novák');
		$this->assertTrue($data && is_array($data) && !empty($data[0]['name']) && false !== stripos($data[0]['name'], 'Novák'));

		/*
		Sample response:
		array =
		  0: array =
			name: string = Jana Kudláčková
			ico: string = 44315945
			city: string = Praha
			addr_city: string = Praha 4
			addr_zip: string = 14900
			addr_streetnr: string = Filipova 2016
			addr_full: string = Praha 4, Filipova 2016, PSČ 14900
		*/
		$data = $connector->findByIco('44 315 945'); // ICO = 8 digits, autostrip spaces
		$this->assertTrue($data && is_array($data) && !empty($data[0]['ico']) && '44315945' == $data[0]['ico']);
	}

	public function testFindSingleCompany()
	{
		$connector = new ConnectorJusticeCz;

		/*
		Sample response:
		---------------
		: array =
		  name: string = Jana Kudláčková
		  ico: string = 44315945
		  city: string = Praha
		  addr_city: string = Praha 4
		  addr_zip: string = 14900
		  addr_streetnr: string = Filipova 2016
		  addr_full: string = Praha 4, Filipova 2016, PSČ 14900
		*/
		$data = $connector->getDetailByICO("44315945"); // Jana Kudláčková
		$this->assertTrue(!empty($data['name']) && false !== stripos($data['name'], 'Kudl'));
		$this->assertTrue(!empty($data['addr_zip']) && false !== stripos($data['addr_zip'], '14900'));
	}

}
