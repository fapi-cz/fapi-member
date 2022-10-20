## Start up:
1. run `make -C .docker-fapi-member dc-up-front`
2. open browser and go to page `http://localhost:8080`
3. install wordpress
4. enable FAPI Member plugin in plugin section
5. develop plugin

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
kde `id` použiju na načtení objednávky, `time` a `security` pro validaci 

#### Chybové kódy

Callback vrací při chybě status code `400` a chybový text, jsou "ošetřeny" následující stavy:

- Nepodařilo se načíst invoice/voucher z API
- Voucher status není `applied`
- Nelze najít email zákazníka v API response
- V get parametrech callbacku chybý proměnná `level`
- Některá sekce/úroveň z callbacku neexistuje ve WordPressu
- Invoice / voucher security hash nesedí
- a další

## Odinstalace pluginu

Plugin při deaktivaci nedělá nic. Při smazání pluginu před administraci WP se:
 - odebere taxonomie
 - odeberou options
 - odeberou user_meta všech uživatelů 
 
## Transpilace JS a CSS
 
Css je zapsáno v scss (https://sass-lang.com/documentation/cli/dart-sass), pro kompilaci do css:
~~~
npm run css
~~~

Javascript je transpilován webpackem, pro kompilaci po úpravách:
~~~
npx webpack
~~~

## Testovací akce

Pokud ve `wp_options` nastavíte klíč `fapiIsDevelopment` na hodnotu `1`, pak se
v menu pluginu objeví červená možnost Testovací akce, která umožní spustit obsah souboru `templates/test.php`,
to je možné využít při vývoji na testování např. zakládání uživatelů, posílání mailů atd.

# Build a nasazení na WP
## POUZE POKUD VÍŠ CO DĚLÁŠ
1. Je potřeba udělat build jak js tak css souborů
2. `docker exec node /bin/sh -c 'yarn --cwd multiple-blocks build'`
3. Dále je potřeba změnit verzi pluginu, přidat setinkovou verzi pokud se jedná o opravu, pokud se přidává nová funkce přidat desetinku a pokud jedná o úplně novou verzi přidat zvyšit major verzi o jedno
4. Aktualizovat composer `composer dump-autoload`
5. Vytvořit produkční build `make build -i`
5. Vytvořit složku `wp-svn`
6. Stáhnout repozitář z WP `svn co https://plugins.svn.wordpress.org/fapi-member wp-svn`
7. Vyvořit složku z verzi ve složce `wp-svn/tags/X.X.X`
8. Nahrát obsah z `wp-build` do vytvořené složky v přechozím bodě
9. Smazat obsah ze složky `wp-svn/trunk`
10. A nahrát obsah ze složky `wp-svn` do složky `wp-svn/trunk`
11. Vše dát do track stavu `svn add --force * --auto-props --parents --depth infinity -q`
12. A vše commitnout `svn ci -m 'Adding first version of my plugin' --username your_username --password your_password`
