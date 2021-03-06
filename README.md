Poznámky
========

## Vývoj

Plugin kód není moc pěkný, ale snažil jsem se maximálně využit Wordpressu a
nestavět vedle WP nějaké další (cizí) struktury a způsoby (autoloading, šablony...), 
které by ale pro větší projekt určitě byly potřeba.

Obzvláště nehezké jsou věci kolem reakce na callback a posílání mailů.
Uvažoval jsem nad možností to přepsat, ale zase jsem se neodvážil dělat 
abstrakce a předjímat něco, protože myslím, že maily (pravidla, počet...) se určitě 
budou měnit a ten systém s postupným plněním `$props` je nejpružnější, co mě napadl.

Taky je použita stará verze PHP - protože [WP](https://cs.wordpress.org/about/requirements/) 
zatím podporuje PHP 5.6.20

## Datové struktury

Plugin nedělá zásah do DB.

### Členství

Členství je uloženo jako meta k uživateli pod klíčem v `FapiMembershipLoader::MEMBERSHIP_META_KEY`.
Struktura je následující:
~~~json
[
  {
    "level":  12, 
    "registered": "2020-01-01T20:00:01", 
    "until": "2020-01-01T20:00:01",
    "isUnlimited": true
  },
  {
      "level":  10, 
      "registered": "2020-02-01T20:00:01", 
      "until": "2020-04-01T20:00:01",
      "isUnlimited": false
  }    
]
~~~

### Historie členství

Historie členství je uložena jako user meta po klíčem v `FapiMembershipLoader::MEMBERSHIP_HISTORY_META_KEY`.
Ukládá se pole serializovaných FapiMembership objektů.

### Úrovně

Úrovně jsou uloženy jako neveřejná taxonomie s názvem v `FapiLevels::TAXONOMY`.

### Nastavení úrovně

Úroveň má své stránky, nastavení emailů a ostatních stránek uloženo v 
term meta: `fapi_pages`, `fapi_email_*`, `fapi_page_*`

### Globální nastavení

Plugin si ukládá data i do options: `fapiMemberApiChecked`, `fapiSettings`, `fapiMemberApiKey`, `fapiMemberApiEmail`

## API a callback

Většinou publikované routy fungují jen s url rewritingem, tyhle ošklivé níže
by měly být univerzálnější.

### URL pro definované sekce a úrovně
~~~
[site]/?rest_route=/fapi/v1/sections
~~~

### Callback url

~~~
[site]/?rest_route=/fapi/v1/callback&level[]=1&level[]=2&days=31
~~~
V těle požadavku očekávám url encoded string jako:
~~~
id=187034262&time=1614239639&security=9edbc14e1905b61af468217f60d2406d160c4fdf
~~~
kde `id` použiju na načtení objednávky.