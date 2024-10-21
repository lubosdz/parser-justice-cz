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
			den_zapisu_num: string = 1992-05-22
			den_zapisu_txt: string = 22. května 1992
			spis_znacka: string = C 5856 vedená u Krajského soudu v Brně
			urlPlatnych: string = https://or.justice.cz/ias/ui/rejstrik-firma.vysledky?subjektId=561565&typ=PLATNY
			urlUplny: string = https://or.justice.cz/ias/ui/rejstrik-firma.vysledky?subjektId=561565&typ=UPLNY
			urlSbirkaListin: string = https://or.justice.cz/ias/ui/vypis-sl-firma?subjektId=561565
		  1: array =
			name: string = AAA první stavební, s.r.o.
			ico: string = 27726053
			city: string = Brno
			addr_city: string = Brno
			addr_zip: string = 60200
			addr_streetnr: string = Příkop 843/4
			addr_full: string = Příkop 843/4, Zábrdovice, 602 00 Brno
			den_zapisu_num: string = 2007-04-26
			den_zapisu_txt: string = 26. dubna 2007
			spis_znacka: string = C 54914 vedená u Krajského soudu v Brně
			urlPlatnych: string = https://or.justice.cz/ias/ui/rejstrik-firma.vysledky?subjektId=672582&typ=PLATNY
			urlUplny: string = https://or.justice.cz/ias/ui/rejstrik-firma.vysledky?subjektId=672582&typ=UPLNY
			urlSbirkaListin: string = https://or.justice.cz/ias/ui/vypis-sl-firma?subjektId=672582
		2: array = ...
		*/
		$data = $connector->findByNazev('AAA');
		$this->assertTrue($data && is_array($data) && !empty($data[0]['name']) && false !== stripos($data[0]['name'], 'AAA'));
		$this->assertTrue($data && is_array($data) && !empty($data[0]['urlUplny']) && false !== stripos($data[0]['urlUplny'], '561565'));

		// by nazev
		$data = $connector->findByNazev('Petr Novák');
		$this->assertTrue($data && is_array($data) && !empty($data[0]['name']) && false !== stripos($data[0]['name'], 'Novák'));

		// for autocomplete
		/**
		sample response:
		array =
		  0: array =
			label: string = AAA, s.r.o. (IČO: 46902058)
			value: string = {"name":"AAA, s.r.o.","ico":"46902058","city":"Brno","addr_city":"Brno","addr_zip":"63900",.."}
		  1: array =
			label: string = AAA první stavební, s.r.o. (IČO: 27726053)
			value: string = {"name":"AAA první stavební, s.r.o.","ico":"27726053","city":"Brno","addr_city":"Brno","addr_zip":"60200",.."}
		*/
		$data = $connector->findForAutocomplete('AAA', 2);
		$this->assertTrue(!empty($data[1]['label']) && false !== stripos($data[1]['label'], '27726053'));
		$this->assertTrue(!empty($data[1]['value']) && ($json = json_decode($data[1]['value'], true)));
		$this->assertTrue(!empty($json['name']) && false !== stripos($json['name'], 'AAA'));
$data = $connector->findForAutocomplete('AAA');
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
			den_zapisu_num: string = 1992-08-26
			den_zapisu_txt: string = 26. srpna 1992
			spis_znacka: string = A 6887 vedená u Městského soudu v Praze
			urlPlatnych: string = https://or.justice.cz/ias/ui/rejstrik-firma.vysledky?subjektId=431803&typ=PLATNY
			urlUplny: string = https://or.justice.cz/ias/ui/rejstrik-firma.vysledky?subjektId=431803&typ=UPLNY
			urlSbirkaListin: string = https://or.justice.cz/ias/ui/vypis-sl-firma?subjektId=431803
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
			den_zapisu_num: string = 1992-08-26
			den_zapisu_txt: string = 26. srpna 1992
			spis_znacka: string = A 6887 vedená u Městského soudu v Praze
			urlPlatnych: string = https://or.justice.cz/ias/ui/rejstrik-firma.vysledky?subjektId=431803&typ=PLATNY
			urlUplny: string = https://or.justice.cz/ias/ui/rejstrik-firma.vysledky?subjektId=431803&typ=UPLNY
			urlSbirkaListin: string = https://or.justice.cz/ias/ui/vypis-sl-firma?subjektId=431803
		*/
		$data = $connector->getDetailByICO("44315945"); // Jana Kudláčková
		$this->assertTrue(!empty($data['name']) && false !== stripos($data['name'], 'Kudl'));
		$this->assertTrue(!empty($data['addr_zip']) && false !== stripos($data['addr_zip'], '14900'));
		$this->assertTrue(!empty($data['urlSbirkaListin']) && false !== stripos($data['urlSbirkaListin'], '431803'));
	}

}
