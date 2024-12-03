=== FAPI Member ===
Contributors: Jiří Slischka, Monika Tomešková, Marek Klein
Tags: membership, fapi, member
Requires at least: 5.9
Tested up to: 6.4
Requires PHP: 8.1
License: GPLv2 or later
Stable tag: 2.2.9

Plugin FAPI pro jednoduchou správu členských sekcí na webu.

== Description ==
Plugin FAPI Member umožňuje jednoduchou správu členských sekcí, tedy webových stránek přístupných jen oprávněným uživatelům. Ve spojení s aplikací FAPI tak můžete velmi snadno a automatizovaně prodávat přístup do svých on-line kurzů, klubů nebo k prémiovému obsahu na svém webu. Dále přidává jednoduchou možnost vkládání prodejního formuláře skrze Wordpress komponentu FAPI form.

Seznam nekompatibilních pluginů:
- WP Cerber Security, Anti-spam & Malware Scan => Zakazuje FM vytvořit uživatele

== Frequently Asked Questions ==
Jak napojit FAPI a plugin na členskou sekci FAPI Member se dozvíte v nápovědě FAPI: [FAPI Member](https://napoveda.fapi.cz/category/129-fapi-member)
Máte problém s nastavením FAPI Memberu. Obrátit se můžete na naší podporu. Kontakt naleznete na naších stránkách: [FAPI](https://fapi.cz/kontakty/)

== Screenshots ==

== Ohodnoťte tento plugin a dejte nám zpětnou vazbu ==
Ohodnotit tento plugin můžete na stránkách [WordPress](https://wordpress.org/plugins/fapi-member/#reviews).

== Changelog ==

= 2.2.9 =
* Fapi Member Pro
	* Added information about last member login date in member detail

= 2.2.8 =
* Fixed automatic unlocking redirect error

= 2.2.7 =
* Increased maximum of automatic unlocking by days to 730

= 2.2.6 =
* Added link to FAPI Member section settings in users table and user profile

= 2.2.4 =
* Allow seamless migration from the SimpleShop plugin to FAPI Member

= 2.2.3 =
* Bug fix - Fixed user creation from voucher FAPI actions

= 2.2.2 =
* Fixed updating password for members from wp administration

= 2.2.1 =
* Fapi Member Pro
	* Added average churn rate period graph

= 2.2.0 =
* Fapi Member Pro
	* Tracking of statistics like user activity, churn and acquisition rates, membership ownership over time or gained vs lost members over a period
	* Member history - displays a history of changes for every member

= 2.1.18 =
* Added support for Mioweb editor (hiding/showing elements based user's memberships)

= 2.1.17 =
* Added the option to create new members directly in the plugin UI

= 2.1.16 =
* Made it possible to send automatic emails when importing users
* Fixed email sending process

= 2.1.15 =
* Added bulk member import
* Refactored create membership API endpoint
* Refactored email shortcodes

= 2.1.14 =
* Added members tab
* Added bulk member export

= 2.1.13 =
* Added alerts to user settings
* Added UI elements communicating additional information about automatic level unlocking
* When a level is automatically unlocked, the expiration date is now set to the expiration date of it's section (instead of "Bez expirace")
* Membership now expires at the end of the expiration date

= 2.1.12 =
* Added support for Divi Builder (hiding/showing elements based user's memberships)

= 2.1.11 =
* Internal fixes

= 2.1.10 =
* Internal fixes

= 2.1.9 =
* Added internal debugging features

= 2.1.8 =
* Bug fix - page/post editor - assign sections/levels

= 2.1.7 =
* Bug fix - safari user settings bug

= 2.1.6 =
* Api bug fix

= 2.1.5 =
* Reworked Page, posts and cpts - Added ordering, filtering and pagination

= 2.1.4 =
* Added option to disallow automatic email when user is added to a section/level using API (parameter 'send_email' = true/false)

= 2.1.3 =
* Added option to disallow automatic unlocking when user is registered after unlock date

= 2.1.2 =
* Now you can specify in which hour automatic level unlocking will happen

= 2.1.1 =
* Fixed user section settings not showing up for users with timezones based on a city

= 2.1.0 =
* Entirely new and reworked UI
* New internal API

= 2.0.12 =
* Bug fixes

= 2.0.11 =
* Bug fix - Member levels now can't have longer expiration times than member sections

= 2.0.10 =
* Bug fix - when using repayment and FM API, memberships are now prolonged by a portion of the total days based on the repayment count

= 2.0.09 =
* Bug fix - fixed admin login redirect

= 2.0.08 =
* Minor bug fix

= 2.0.07 =
* Bug fix - removed delay when automatic level unlocking is set to 0 days

= 2.0.06 =
* Bug fixes

= 2.0.05 =
* Fixed login redirect bug

= 2.0.04 =
* Fixed registration emails not being sent

= 2.0.03 =
* Bug fixes

= 2.0.02 =
* Bug fixes

= 2.0.01 =
* Redactored FM backend

= 1.9.47 =
* Bug Fixed

= 1.9.45 =
* Made it possible to hide elements with type 'container' based on membership ownership in elementor

= 1.9.44 =
* Bugfix - levels now automatically unlock according to the timezone set in the WordPress settings

= 1.9.43 =
* Fixed element display conditions for elementor

= 1.9.42 =
* Gradual level release fixes

= 1.9.41 =
* Added gradual level release
* Fixed too many redirects error

= 1.9.40 =
* Fixed type error

= 1.9.39 =
* Fixed elementor update error

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
