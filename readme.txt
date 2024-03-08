=== FAPI Member ===
Contributors: Jiří Slischka, Monika Tomešková
Tags: membership, fapi
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
License: GPLv2 or later
Stable tag: 1.9.38

Plugin FAPI pro jednoduchou správu členských sekcí na webu.

== Description ==
Plugin FAPI Member umožňuje jednoduchou správu členských sekcí, tedy webových stránek přístupných jen oprávněným uživatelům. Ve spojení s aplikací FAPI tak můžete velmi snadno a automatizovaně prodávat přístup do svých on-line kurzů, klubů nebo k prémiovému obsahu na svém webu. Dále přidává jednoduchou možnost vkládání prodejního formuláře skrze Wordpress komponentu FAPI form.

== Frequently Asked Questions ==
Jak napojit FAPI a plugin na členskou sekci FAPI Member se dozvíte v nápovědě FAPI: [FAPI Member](https://napoveda.fapi.cz/category/129-fapi-member)
Máte problém s nastavením FAPI Memberu. Obrátit se můžete na naší podporu. Kontakt naleznete na naších stránkách: [FAPI](https://fapi.cz/kontakty/)

== Screenshots ==

== Ohodnoťte tento plugin a dejte nám zpětnou vazbu ==
Ohodnotit tento plugin můžete na stránkách [WordPress](https://wordpress.org/plugins/fapi-member/#reviews).

== Changelog ==
= 1.9.38 =
* Changed showing FAPI Member settings in Elementor

== Changelog ==
= 1.9.37 =
* Fixed section email template saving

== Changelog ==
= 1.9.36 =
* Fixed user shortcode overflow

= 1.9.35 =
* Updated function of Level Emails checkbox

= 1.9.34 =
* Small UI fixes

= 1.9.33 =
* Updated compatible WP version

= 1.9.32 =
* Fixed sending emails to existing users

= 1.9.31 =
* Fixed FAPI level selection DIVI bug

= 1.9.30 =
* Translations

= 1.9.29 =
* Translations

= 1.9.28 =
* Improved plugin update system
* Improved error logging

= 1.9.27 =
* Fixed multiple FAPI connections, invoice processing

= 1.9.26 =
* Enable multiple FAPI connections

= 1.9.25 =
* Fixed problem with login into FAPI Member section on DIVI theme

= 1.9.24 =
* Fixed problem with login form not redirecting to wordpress admin page if the user is admin

= 1.9.23 =
* Update

= 1.9.22 =
* Fixed function arguments

= 1.9.19 =
* Added retry logic for FAPI API calls
* Added list and export of members for sections and levels

= 1.9.16 =
* Fixed problem with redirect after logged in for WPS Hide Login plugin

= 1.9.13 =
* Fixed problem with login form redirecting to wordpress admin page instead of FAPI Member page

= 1.9.12 =
* Added public PHP functions
* \FapiMember\Utils\isIsSection( int|array<int> $sectionOrLevel ) : bool; Return TRUE if current logged user is in section or level
* \FapiMember\Utils\isNotInSection( int|array<int> $sectionOrLevel ) : bool; Return TRUE if current logged user is NOT in section or level

= 1.9.11 =
* Bugfix

= 1.9.10 =
* Bugfix

= 1.9.9 =
* Oprava zobrazení nastavených společných stránek.
* Přidán nový shortcode "[fapi-member-user-section-expiration section=x]", který vypíše datum expirace pro zvolenou členskou sekci nebo úrověň.

= 1.9.8 =
* Bugfix

= 1.9.7 =
* Bugfix

= 1.9.6 =
* Přidaná podpora pro Custom Post Types, zejména pro Elementor.

= 1.9.5 =
* Oprava funkce zobrazování části stránky pro Elementor.

= 1.9.3 =
* Oprava emailů po přidání do další členské sekce.
* Účty se nově zakládání i se jménem a přijmením pokud jsou dostupné.
* Zástupný symbol %%CLENSTVI_DO%% nově vypisuje pouze datum místo data a času platnosti přístupu.

= 1.9.2 =
* Oprava globální nástěnky.
* Minimální požadavky verze Wordpressu 5.8.
* Oprava přihlašovacího formuláře při použití pluginu, které mění výchozí přihlašovací stránku do Wordpressu (například plugin Change wp-admin login).

= 1.9.1 =
* Opravná verze.

= 1.9.0 =
* Přidaná podpora teaseru pro Elementor.
* Přidán widget prodejního formuláře do Elementoru.
* Čas registrace se nyní zakládá v časovém pásmu GTM+2 místo UTC+0.
* Oprava chyby při konkurečním vytváření člena v členské sekci.

= 1.8.21 =
* Přidáná nová komponenta FAPI formuláře, nyní se dá vkládat prodejní formulář jednoduše pomocí komponenty.

= 1.8.20 =
* Přidána možnost nastavit společnou stránku pro nástěnku, tedy stránku, kam se člen dostane po přihlášení.

= 1.8.19 =
* Vylepšení skrývání bloků, přidaná možnost zobrazit všem návštěvníkům

= 1.8.18 =
* Oprava nekompatibilního kódu na produkci

= 1.8.17 =
* Oprava chyb u bloků

= 1.8.16 =
* Oprava chyb u bloků

= 1.8.15 =
* Oprava chyb u bloků

= 1.8.14 =
* Přidána možnost zobrazování a skrývaní bloků stránky podle toho za má člen přiřazenou sekci nebo nikov.
Nově tedy jde vytvořit tzv. nástěnku, kde člen uvidí všechny sekce, které má zakoupené a sekce které nemá zakoupané se mu skryjí.
* Oprava překladů

= 1.8.13 =
* Příprava pro možnost napojení více FAPI účtů.

= 1.8.10 =
* Přidán API endpoint listování členských sekci pro jednoduší napojení na Integromat.

= 1.8.9 =
* K prodloužení nebo založení nového člena, se nově plugin pokusí najít existujícího člena pomocí e-mailové adresy a uživatelského jména.

= 1.8.8 =
* Po kliknutí na tlačítko připojit se po úspěšném napojení na FAPI zkontroluje zda je FAPI připojené na FAPI Member pokud ne vytvoří propojení automaticky.
* Oprava chyby při zakládání nových členů. Oprava serializace objektu.
* Do API odpovědi přidaná verze pluginu pro lepší dohledávání chyb pro různé verze pluginu.
* Zjednodušení a zpřehlednění kódu.

= 1.8.6 =
* Přidání zamykání příspěvků
* přidání zamykání příspěvků a stránke přimo v editoru stránky
