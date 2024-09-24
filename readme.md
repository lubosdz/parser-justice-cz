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

```
// inicializacia API objektu
$connector = new \lubosdz\parserJusticeCz\ConnectorJusticeCz();
```

Vyhľadávanie:
-------------

```
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
$list = $connector->findForAutocomplete('auto');

$list = array (
  0 => array (
	'value' => '26316838',
	'label' => 'AUTO a.s. (IČO: 26316838)',
  ),
  1 => array (
	'value' => '49435523',
	'label' => 'AUTO, spol. s r.o. - v likv .. (IČO: 49435523)',
  ),
  2 => array (
	'value' => '63470187',
	'label' => 'A U T O ... s.r.o. (IČO: 63470187)',
  ),
  3 => array (
	'value' => '26343860',
	'label' => 'Autoskla WRCar-servis, a.s. (IČO: 26343860)',
  ),
  4 => array (
	'value' => '26026660',
	'label' => 'AUTO-AGRO-START, s.r.o. (IČO: 26026660)',
  ),
  5 => array (
	'value' => '60826452',
	'label' => 'AUTODOPRAVA Mikeš s.r.o. (IČO: 60826452)',
  ),
  ...
)

$detail = $connector->getDetailByICO('44315945');

$detail : array (
	[name] => Jana Kudláčková
	[ico] => 44315945
	[city] => Praha
	[addr_city] => Praha 4
	[addr_zip] => 14900
	[addr_streetnr] => Filipova 2016
	[addr_full] => Praha 4, Filipova 2016, PSČ 14900
)
```


Changelog
=========

1.0.1 - 11.09.2024
------------------
* fix loading HTML into DOMDocument due to added extra script before <body> end tag
* safer REGEX expressions with unicode flag
* improved documentation

1.0.0 - 05.12.2019
------------------
* initial release
