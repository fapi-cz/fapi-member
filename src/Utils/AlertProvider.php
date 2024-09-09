<?php

namespace FapiMember\Utils;

use FapiMember\FapiMemberPlugin;
use FapiMember\Model\Enums\Alert;
use FapiMember\Model\Enums\Types\AlertType;

class AlertProvider
{
	private static array $alerts = [
		Alert::API_FORM_EMPTY => [AlertType::ERROR, 'Zadejte e-mail i API klíč.'],
		Alert::API_FORM_SUCCESS => [AlertType::SUCCESS, 'Údaje pro API uloženy.'],
		Alert::API_FORM_ERROR => [AlertType::ERROR, 'Neplatné údaje pro API.'],
		Alert::API_FORM_CREDENTIALS_EXIST => [AlertType::ERROR, 'Zadané údaje pro API se již používají.'],
		Alert::API_FORM_TOO_MANY_CREDENTIALS => [AlertType::ERROR, 'Není možné propojit více než ' . FapiMemberPlugin::CONNECTED_API_KEYS_LIMIT . ' účtů.'],
		Alert::API_FORM_CREDENTIALS_REMOVED => [AlertType::SUCCESS, 'Účet byl odpojen.'],
		Alert::SECTION_NAME_EMPTY => [AlertType::ERROR, 'Název sekce/úrovně je prázdný.'],
		Alert::REMOVE_LEVEL_SUCCESSFUL => [AlertType::SUCCESS, 'Sekce/úroveň smazána.'],
		Alert::INTERNAL_ERROR => [AlertType::ERROR, 'Došlo k interní chybě.'],
		Alert::SETTINGS_SAVED => [AlertType::SUCCESS, 'Nastavení uložena.'],
		Alert::LEVEL_ALREADY_EXISTS => [AlertType::ERROR, 'Sekce/úroveň s tímto názvem již existuje.'],
		Alert::REORDER_FAILED => [AlertType::ERROR, 'Nelze přeřadit sekci/úroveň.'],
		Alert::MEMBERSHIP_REGISTERED_EXTENDED => [AlertType::WARNING, 'Datum registrace sekce bylo přenastaveno dle úrovně.'],
		Alert::MEMBERSHIP_UNTIL_EXTENDED => [AlertType::WARNING, 'Datum expirace sekce bylo přenastaveno dle úrovně.'],
		Alert::INVALID_EMAIL => [AlertType::ERROR, 'E-mailová adresa není validní.'],
		Alert::MISSING_EMAIL => [AlertType::ERROR, 'Zadejte prosím e-mailovou adresu.'],
		Alert::IMPORT_FAILED => [AlertType::ERROR, 'Import selhal.'],
		Alert::IMPORT_LEVEL_ID_DOESNT_EXIST => [AlertType::ERROR, 'Soubor obsahuje neplatné ID sekce/úrovně.'],
	];

	/** @return array<string> */
	public static function getError(string $key): array
	{
		$error = self::$alerts[$key];

		return [
			'type' => $error[0],
			'message' => $error[1],
		];
	}

}
