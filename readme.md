Parser obchodního rejstříku České republiky
===========================================

> *Disclaimer / Prehlásenie*:
>
> Kód poskytnutý je bez záruky a môže kedykoľvek prestať fungovať.
> Jeho funkčnosť je striktne naviazaná na generovanú DOM štruktúru HTML elementov.
> Autor nie je povinný udržiavať kód aktuálny a funkčný, neposkytuje ku nemu žiadnu oficiálnu podporu a nezodpovedá za nesprávne použitie kódu.


Licencia
========

Kód obsiahnutý v súbore `ConnectorJusticeCz.php` je voľne distribuovateľný a modifikovateľný na súkromné ako aj komerčné účely.


Inštalácia, dependencie, demo
=============================

* Kód je obsiahnutý v jedinom PHP súbore `ConnectorJusticeCz.php`.
* Potrebné PHP rozšírenia: `tidy`, `dom`, `mbstring`
* Demo: [https://synet.sk/blog/php/365-API-justice-cz-parser-obchodniho-rejstriku](https://synet.sk/blog/php/365-API-justice-cz-parser-obchodniho-rejstriku)
* install manually or via composer:

```bash
$ composer require "lubosdz/parser-justice-cz" : "~1.0.0"
```

Použitie / API / Usage
======================

```php
// inicializacia API objektu
$connector = new \lubosdz\parserJusticeCz\ConnectorJusticeCz();
```

Vyhľadávanie:
-------------

```php
// vyhľadanie zoznamu subjektov podľa mena/názvu/IČO:
$list = $connector->findByNazev('auto');
$list = $connector->findByNazev('Jan Novák');
$list = $connector->findForAutocomplete('Jan Novák');
$list = $connector->findByIco('44315945');

// vyhľadanie detailu subjektu podľa IČO:
$detail = $connector->getDetailByICO('44315945');
```

Príklad odpovede
----------------

```

// Example #1
// ----------
$list = $connector->findForAutocomplete('AAA');

// the "value" contains serialized JSON with all attributes - so no further request is needed
$list = array (
  0: array =
	label: string = "AAA, s.r.o. (IČO: 46902058)"
	value: string = {"name":"AAA, s.r.o.","ico":"46902058","city":"Brno","addr_city":"Brno",.."}
  1: array =
	label: string = "AAA první stavební, s.r.o. (IČO: 27726053)"
	value: string = {"name":"AAA první stavební, s.r.o.","ico":"27726053","city":"Brno","addr_city":"Brno",.."}
  2: array =
	label: string = "AA - ATELIER ALFA s.r.o. (IČO: 47782307)"
	value: string = {"name":"AA - ATELIER ALFA s.r.o.","ico":"47782307","city":"Ústí nad Labem","addr_city":"Ústí nad Labem",.."}
  3: array =
	label: string = "AAAYacht, s.r.o. (IČO: 27758656)"
	value: string = {"name":"AAAYacht, s.r.o.","ico":"27758656","city":"Třebíč","addr_city":"Třebíč","addr_zip":"67401",.."}
  4: array =
	label: string = "AA ART SMITH, s.r.o. v likv.. (IČO: 25893912)"
	value: string = {"name":"AA ART SMITH, s.r.o. v likvidaci","ico":"25893912","city":"Ostrava","addr_city":"Ostrava","addr_zip":"70200",.."}
  5: array =
	label: string = "AAA STUDIO, s.r.o. (IČO: 25340603)"
	value: string = {"name":"AAA STUDIO, s.r.o.","ico":"25340603","city":"Brno","addr_city":"Brno","addr_zip":"60200",.."}
  ...
)

// now use javascript to decode JSON into attributes (see demo):
// data = $.parseJSON( ui.item.value ) // jQuery
// data = JSON.parse( ui.item.value ) // vanilla


// Example #2
// ----------
$detail = $connector->getDetailByICO('44315945');

// all extracted attributes
$detail : array (
	[name] => "Jana Kudláčková"
	[ico] => "44315945"
	[city] => "Praha"
	[addr_city] => "Praha 4"
	[addr_zip] => "14900"
	[addr_streetnr] => "Filipova 2016"
	[addr_full] => "Praha 4, Filipova 2016, PSČ 14900"
	[den_zapisu_num] => "1992-08-26"
	[den_zapisu_txt] => "26. srpna 1992"
	[spis_znacka] => "A 6887 vedená u Městského soudu v Praze"
	[urlPlatnych] => "https://or.justice.cz/ias/ui/rejstrik-firma.vysledky?subjektId=431803&typ=PLATNY"
	[urlUplny] => "https://or.justice.cz/ias/ui/rejstrik-firma.vysledky?subjektId=431803&typ=UPLNY"
	[urlSbirkaListin] => "https://or.justice.cz/ias/ui/vypis-sl-firma?subjektId=431803"
)
```


Changelog
=========

1.0.3 - 21.10.2024
------------------
* findForAutocomplete now returns by default serialized JSON with all attibutes
* extract also related URL links (urlPlatnych, urlUplny, urlSbirkaListin)
* updated documentation - jQuery autocomplete [example](https://synet.sk/blog/php/365-API-justice-cz-parser-obchodniho-rejstriku)


1.0.2 - 13.10.2024
------------------
* extract more attributes (spis_znacka, den_zapisu_txt, den_zapisu_num)
* all REGEX use unicode flag /u


1.0.1 - 11.09.2024
------------------
* fix loading HTML into DOMDocument due to added extra script before <body> end tag
* safer REGEX expressions with unicode flag
* improved documentation


1.0.0 - 05.12.2019
------------------
* initial release
