<?php

namespace FapiMember\Utils;

use FapiMember\Model\Enums\Types\EmailType;
use FapiMember\Model\MemberLevel;

final class EmailHelper
{
	const FAPI_EMAILS
		= array(
			'section' => array(
				EmailType::AFTER_REGISTRATION => array(
					's' => 'Posílám vám klíče od %%SEKCE%%',
					'b' => 'Krásný den,
chci vám osobně poděkovat za registraci a přivítat vás mezi námi.

Stojíte před dveřmi do sekce %%SEKCE%%

Zde jsou vaše přihlašovací údaje: 
uživatelské jméno: %%PRIHLASOVACI_JMENO%%
heslo: %%HESLO%%

Vstoupit můžete tudy: %%PRIHLASENI_ODKAZ%%. 

Přístup máte platný do %%CLENSTVI_DO%%.',
				),
				EmailType::AFTER_MEMBERSHIP_PROLONGED => array(
					's' => 'Díky za to, že u nás v %%SEKCE%% zůstáváte',
					'b' => 'Krásný den,
moc mě těší, že u nás v sekci %%SEKCE%% zůstáváte déle.

Nyní máte vstup prodloužený do %%CLENSTVI_DO%%.',
				),
				EmailType::AFTER_ADDING=> array(
					's' => 'Teď už můžete i do %%SEKCE%%',
					'b' => 'Krásný den, 
těší mě, že máte o mou práci takový zájem.

Právě Vám otvírám dveře do sekce %%SEKCE%%.

Co je uvnitř, uvidíte ve svém účtu: %%PRIHLASENI_ODKAZ%%.

Přístup máte platný do %%CLENSTVI_DO%%.',
				),
			),
			'level'   => array(
				EmailType::AFTER_REGISTRATION => array(
					's' => 'Posílám vám klíče od %%UROVEN%%',
					'b' => 'Krásný den,
chci vám osobně poděkovat za registraci a přivítat vás mezi námi.

Stojíte před dveřmi do sekce %%UROVEN%%

Zde jsou vaše přihlašovací údaje: 
uživatelské jméno: %%PRIHLASOVACI_JMENO%%
heslo: %%HESLO%%

Vstoupit můžete tudy: %%PRIHLASENI_ODKAZ%%. 

Přístup máte platný do %%CLENSTVI_DO%%.',
				),
				EmailType::AFTER_MEMBERSHIP_PROLONGED => array(
					's' => 'Díky za to, že u nás v %%UROVEN%% zůstáváte',
					'b' => 'Krásný den,
moc mě těší, že u nás v sekci %%UROVEN%% zůstáváte déle.

Nyní máte vstup prodloužený do %%CLENSTVI_DO%%.',
				),
				EmailType::AFTER_ADDING => array(
					's' => 'Teď už můžete i do %%UROVEN%%',
					'b' => 'Krásný den, 
těší mě, že máte o mou práci takový zájem.

Právě Vám otvírám dveře do sekce %%UROVEN%%.

Co je uvnitř, uvidíte ve svém účtu: %%PRIHLASENI_ODKAZ%%.

Přístup máte platný do %%CLENSTVI_DO%%.',
				),
			),
		);

	public static function getEmail(MemberLevel $level, string $emailType): array
	{
		$emailKind = 'section';

		if (!$level->isSection()) {
			$emailKind = 'level';
		}

		return self::FAPI_EMAILS[$emailKind][$emailType];
	}

	public static function replaceShortcodes(string $text, array $props): string
	{
		$map = array(
			'%%SEKCE%%'              => self::getSectionValue( $props ),
			'%%UROVEN%%'             => self::getLevelValue( $props ),
			'%%DNI%%'                => self::getDaysValue( $props ),
			'%%CLENSTVI_DO%%'        => self::getExpirationDateValue( $props ),
			'%%PRIHLASENI_ODKAZ%%'   => $props['login_link_url'],
			'%%PRIHLASOVACI_JMENO%%' => isset( $props['login'] ) ? $props['login'] : '',
			'%%HESLO%%'              => isset( $props['password'] ) ? $props['password'] : '',
		);

		foreach ( $map as $key => $value ) {
			$text = str_replace( $key, $value, $text );
		}

		return $text;
	}

	private static function getSectionValue(array $props): string
	{
		if ( ( isset( $props['membership_level_added_is_section'] ) && $props['membership_level_added_is_section'] === false )
			|| ( isset( $props['membership_prolonged_is_section'] ) && $props['membership_prolonged_is_section'] === false )
		) {
			return '';
		}

		if ( isset( $props['membership_prolonged_level_name'] ) ) {
			return $props['membership_prolonged_level_name'];
		}

		if ( isset( $props['membership_level_added_level_name'] ) ) {
			return $props['membership_level_added_level_name'];
		}

		return '';
	}

	private static function getLevelValue(array $props): string
	{
		if ( ( isset( $props['membership_level_added_is_section'] ) && $props['membership_level_added_is_section'] === true )
			|| ( isset( $props['membership_prolonged_is_section'] ) && $props['membership_prolonged_is_section'] === true )
		) {
			return '';
		}

		if ( isset( $props['membership_prolonged_level_name'] ) ) {
			return $props['membership_prolonged_level_name'];
		}

		if ( isset( $props['membership_level_added_level_name'] ) ) {
			return $props['membership_level_added_level_name'];
		}

		return '';
	}

	private static function getDaysValue(array $props): int|string
	{
		if ( isset( $props['membership_prolonged_days'] ) ) {
			return $props['membership_prolonged_days'];
		}

		if ( isset( $props['membership_level_added_days'] ) ) {
			return $props['membership_level_added_days'];
		}

		if ( isset( $props['membership_prolonged_to_unlimited'] ) || isset( $props['membership_level_added_unlimited'] ) ) {
			return 'neomezeně';
		}

		return '';
	}

	private static function getExpirationDateValue(array $props): string
	{
		if ( isset( $props['membership_prolonged_until'] ) ) {
			return $props['membership_prolonged_until']->format( 'j. n. Y' );
		}

		if ( isset( $props['membership_level_added_until'] ) ) {
			return $props['membership_level_added_until']->format( 'j. n. Y' );
		}

		if ( isset( $props['membership_prolonged_to_unlimited'] ) || isset( $props['membership_level_added_unlimited'] ) ) {
			return 'neomezené';
		}

		return '';
	}

}
