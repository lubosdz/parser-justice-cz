<?php
/**
* PHP parser pre vyhladanie subjektov a vypis adresy subjektu a IČO z Veřejnýho rejstříka ČR
* Lookup service for Czech Business Directory / Public Business Register (www.justice.cz)
* ------------------------------------------------------------------
* Demo:
* https://synet.sk/blog/php/365-API-justice-cz-parser-obchodniho-rejstriku
* Author (c) lubosdz@gmail.com
* ------------------------------------------------------------------
* Disclaimer / Prehlásenie:
* Kód poskytnutý je bez záruky a môže kedykoľvek prestať fungovať.
* Jeho funkčnosť je striktne naviazaná na generovanú štruktúru HTML elementov.
* Autor nie je povinný udržiavať kód aktuálny a funkčný, ani neposkytuje ku nemu žiadnu podporu.
* Autor nezodpovedá za nesprávne použitie kódu.
* ------------------------------------------------------------------
* Usage example:
* $connector = new ConnectorJusticeCz;
* $out = $connector->findByNazev('auto');
* $out = $connector->findByIco('44315945');
* echo '<pre>'.print_r($out, 1).'</pre>';
*/

namespace lubosdz\parserJusticeCz;

class ConnectorJusticeCz
{
	/**
	* @var string URL endpoint base
	*/
	const URL_SERVER = 'https://or.justice.cz/ias/ui/rejstrik-$firma';

	/**
	* @var int Max. company label length for autocomplete options
	*/
	public $labelMaxChars = 30;

	/**
	* Find company/subject by ICO (company ID), partial ICO not allowed, used by autocomplete
	* @param string $ico Full company identifier 8-digits, e.g. 29243831 or 64612023
	*/
	public function findByIco($ico)
	{
		$response = [];
		$ico = preg_replace('/[^\d]/', '', $ico);

		if (preg_match('/^\d{8}$/', $ico)) {
			$url = self::URL_SERVER.'?ico='.$ico;
			$response = file_get_contents($url);
			$response = self::extractSubjects($response);
		}

		return $response;
	}

	/**
	* Find company by partial name (fulltext)
	* @param string $nazev, partial company name e.g. "pojisteni"
	*/
	public function findByNazev($nazev)
	{
		$response = [];

		if ($nazev) {
			$nazev = trim($nazev);
			$url = self::URL_SERVER.'?nazev='.urlencode($nazev);
			$response = file_get_contents($url);
			$response = self::extractSubjects($response);
		}

		return $response;
	}

	/**
	* Find company/subject by ICO (company ID), partial ICO not allowed, used for direct query
	* @param string $ico Full company identifier 8-digits, e.g. 29243831 or 64612023
	*/
	public function getDetailByICO($ico)
	{
		$response = [];
		$ico = preg_replace('/[^\d]/', '', $ico);

		if (preg_match('/^\d{8}$/', $ico)) {
			$url = self::URL_SERVER.'?ico='.$ico;
			$response = file_get_contents($url);
			if($response){
				$response = self::extractSubjects($response);
				if(!empty($response[0])){
					$response = $response[0];
				}
			}
		}

		return $response;
	}

	/**
	* Return options for autocomplete list
	* @param string $term Searched matching string
	* @param int How many items to return, 1 - 50 (server returns max. 50 items)
	*/
	public function findForAutocomplete($term, $size = 10)
	{
		$out = [];
		$size = intval($size);

		if ($term && $size > 0 && mb_strlen($term, 'utf-8') >= 3) {
			// Justice vrati vysledok pre min. 3 znaky
			if (preg_match('/^\d{8}$/', $term)) {
				$subjects = $this->findByIco($term);
			} else {
				$subjects = $this->findByNazev($term);
			}
		}

		if (!empty($subjects) && is_array($subjects)) {

			$subjects = array_slice($subjects, 0, $size); // return first $size matches

			foreach ($subjects as &$subject) {
				$subject['shortname'] = $subject['name'];
				// cut off too long names
				if(mb_strlen($subject['shortname'], 'utf-8') > $this->labelMaxChars){
					$subject['shortname'] = mb_substr($subject['shortname'], 0, $this->labelMaxChars - 3, 'utf-8').' ..';
				}
			}

			foreach ($subjects as $subject) {
				if(!empty($subject['ico'])){
					$out[] = [
						'value' => $subject['ico'],
						'label' => "{$subject['shortname']} (IČO: {$subject['ico']})",
					];
				}
			}
		}

		return $out;
	}

	/**
	* Find list of matching subjects with ICO and the address
	* @param string $html HTML response from server justice.cz
	* @return array(name, ico, city, addr_city, ..)
	*/
	protected static function extractSubjects($html)
	{
		if (!$html) {
			throw new \Exception("No HTML content.");
		}

		// ensure valid XHTML markup
		if (!extension_loaded('tidy')) {
			throw new \Exception('Missing extension [tidy].');
		}

		$tidy = new \tidy();
		$html = $tidy->repairString($html, array(
			'output-xhtml' => true,
			'show-body-only' => true,
		), 'utf8');

		// fix: wrap up HTML into <div> tag to avoid error "Extra content at the end of the document"
		//      when loading into DOMDocument. Extra script was added in cca 2023 (?).
		$html = "<div>{$html}</div>";

		// purify whitespaces - vkladaju \n alebo &nbsp;
		$html = strtr($html, [
			'&nbsp;' => ' ',
		]);
		$html = preg_replace('/\s+/u', ' ', $html);
		$html = trim($html);

		// load XHTML into DOM document
		$xml = new \DOMDocument('1.0', 'utf-8');
		$xml->loadXML($html);
		$xpath = new \DOMXPath($xml);
		$rows = $xpath->query('//table[@class="result-details"]/tbody');

		$out = [];

		if ($rows->length) {

			foreach ($rows as $row) {

				// Nazev
				$nodeList = $xpath->query("./tr[1]/td[1]", $row);
				if(!$nodeList->length){
					continue; // nazev je povinny
				}
				$name = $nodeList->item(0)->nodeValue;
				$name = preg_replace('/\s+/', ' ', $name); // viacnasobne inside spaces

				// ICO
				$nodeList = $xpath->query("./tr[1]/td[2]", $row);
				$ico = $nodeList->length ? $nodeList->item(0)->nodeValue : '';

				// adresa - neda sa spolahnut na poradie prvkov :-(
				$city = '';
				$nodeList = $xpath->query("./tr[3]/td[1]", $row);
				if ($nodeList->length) {

					$addr = trim($nodeList->item(0)->nodeValue);

					if (preg_match('/,\s*(\d{3} ?\d{2})\s+(.+)$/u', $addr, $match)) {
						// Příborská 597, Místek, 738 01 Frýdek-Místek - nazov obce za PSC, prva je ulice a cislo
						$city = $addr_city = $match[2];
						list($addr_streetnr) = explode(',', $addr);
						$addr_zip = $match[1];
					} elseif (preg_match('/,\s*PSČ\s+(\d{3} ?\d{2})$/u', $addr, $match)) {
						// Řevnice, ČSLA 118, okres Praha-západ, PSČ 25230 - PSC na konci, obec je prva, ulice a cislo druha
						list($city, $addr_streetnr) = explode(',', $addr);
						$addr_city = $city;
						$addr_zip = $match[1];
					} elseif(!preg_match('/\d{3} ?\d{2}/u', $addr, $match)) {
						// Ústí nad Labem, Masarykova 74 - bez PSC - obec, ulice a cislo
						$addr_streetnr = $addr_zip = '';
						if (false !== strpos($addr, ',')) {
							list($city, $addr_streetnr) = explode(',', $addr);
						} else {
							list($city) = explode(',', $addr);
						}
						$addr_city = $city;
					}

					// "Praha 10 - Dolní Měcholupy" -> Praha 10, pozn: Frydek-Mistek nema medzeru okolo pomlcky
					// whoops, avsak ani Ostrana-Hontice a dalsie .. :-( Pre city potrebujeme kratky nazov do 10-15 pismen
					list($city) = explode('-', $city);
					// Praha 5 -> Praha
					$city = preg_replace('/\d/', '', $city);
					// viacnasobne spaces
					$city = preg_replace('/\s+/u', ' ', $city);
				}

				$out[] = [
					'name' => self::trimQuotes($name),
					'ico' => preg_replace('/[^\d]/', '', $ico),
					'city' => self::trimQuotes($city),
					// pre polia s adresou, konzistentne so smartform naseptavacem
					'addr_city' => self::trimQuotes($addr_city),
					'addr_zip' => preg_replace('/[^\d]/', '', $addr_zip),
					'addr_streetnr' => self::trimQuotes($addr_streetnr),
					// len pre kontrolu - plna povodna adresa
					'addr_full' => self::trimQuotes($addr),
				];
			}
		}

		return $out;
	}

	/**
	* Vyhodi quotes z textu, aby neposkodilo HTML atributy
	* @param string $s
	*/
	protected static function trimQuotes($s)
	{
		return trim(strtr($s, ['"' => '', "'" => '']));
	}

}
