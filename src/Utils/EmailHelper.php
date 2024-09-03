<?php

namespace FapiMember\Utils;

use FapiMember\Container\Container;
use FapiMember\Model\Enums\Format;
use FapiMember\Model\Enums\Types\EmailType;
use FapiMember\Model\MemberLevel;
use FapiMember\Model\Membership;
use FapiMember\Model\User;
use FapiMember\Repository\LevelRepository;
use FapiMember\Repository\UserRepository;
use FapiMember\Service\LevelService;

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
		/** @var LevelRepository $levelRepository */
		$levelRepository = Container::get(LevelRepository::class);
		/** @var LevelService $levelService */
		$levelService = Container::get(LevelService::class);
		/** @var UserRepository $userRepository */
		$userRepository = Container::get(UserRepository::class);
		/** @var Membership $oldMembership */
		$oldMembership = $props['oldMembership'];
		/** @var Membership $newMembership */
		$newMembership = $props['newMembership'];
		/** @var User $user */
		$user = $userRepository->getUserById($newMembership->getUserId());


		$level = $levelRepository->getLevelById($newMembership->getLevelId());

		$map = array(
			'%%SEKCE%%' => self::getSectionValue($level),
			'%%UROVEN%%' => self::getLevelValue($level),
			'%%DNI%%' => self::getDaysValue($oldMembership, $newMembership),
			'%%CLENSTVI_DO%%' => self::getExpirationDateValue($newMembership),
			'%%PRIHLASENI_ODKAZ%%' => $levelService->getLoginUrl($level->getId()),
			'%%PRIHLASOVACI_JMENO%%' => $user->getLogin(),
			'%%HESLO%%' => isset($props['password']) ? $props['password'] : '',
		);

		foreach ( $map as $key => $value ) {
			$text = str_replace( $key, $value, $text );
		}

		return $text;
	}

	private static function getSectionValue(MemberLevel $level): string
	{
		if ($level->isSection()) {
			return $level->getName();
		}

		return '';
	}

	private static function getLevelValue(MemberLevel $level): string
	{
		if (!$level->isSection()) {
			return $level->getName();
		}

		return '';
	}

	private static function getDaysValue(Membership|null $oldMembership, Membership $newMembership): int|string
	{

		if ($oldMembership === null) {
			$from = $newMembership->getRegistered();
		} else {
			$from = $oldMembership->getUntil();
		}

		$to = $newMembership->getUntil();

		if ($to === null || $from === null) {
			return 'neomezené';
		}

		return DateTimeHelper::getDaysDifference($from, $to);
	}

	private static function getExpirationDateValue(Membership $membership): string
	{
		$expiration = $membership->getUntil();

		if ($expiration === null) {
			return 'neomezeně';
		}

		return $expiration->format(Format::DATE_CZECH);
	}

}
