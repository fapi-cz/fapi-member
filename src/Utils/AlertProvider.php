<?php

namespace FapiMember\Utils;

use FapiMember\FapiMemberPlugin;
use FapiMember\Model\Enums\Alert;
use FapiMember\Model\Enums\Types\AlertType;

class AlertProvider
{
	private static array $alerts = array(
		Alert::API_FORM_EMPTY => array(AlertType::ERROR, 'Je třeba zadat jak uživatelské jméno, tak API klíč.' ),
		Alert::API_FORM_SUCCESS => array(AlertType::SUCCESS, 'Údaje pro API uloženy.' ),
		Alert::API_FORM_ERROR => array(AlertType::ERROR, 'Neplatné údaje pro API.' ),
		Alert::API_FORM_CREDENTIALS_EXIST => array(AlertType::ERROR, 'Zadané údaje pro API se již používají.' ),
		Alert::API_FORM_TOO_MANY_CREDENTIALS => array(AlertType::ERROR, 'Není možné propojit více než ' . FapiMemberPlugin::CONNECTED_API_KEYS_LIMIT . ' účtů.' ),
		Alert::API_FORM_CREDENTIALS_REMOVED => array(AlertType::SUCCESS, 'Účet byl odpojen.' ),
		Alert::SECTION_NAME_EMPTY => array(AlertType::ERROR, 'Název sekce je povinný.' ),
		Alert::LEVEL_NAME_OR_PARENT_EMPTY => array(AlertType::ERROR, 'Název úrovně a výběr sekce je povinný.' ),
		Alert::SECTION_NOT_FOUND => array(AlertType::ERROR, 'Sekce nenalezena.' ),
		Alert::REMOVE_LEVEL_SUCCESSFUL => array(AlertType::SUCCESS, 'Sekce/úroveň smazána.' ),
		Alert::EDIT_LEVEL_SUCCESSFUL => array(AlertType::SUCCESS, 'Sekce/úroveň upravena.' ),
		Alert::LEVEL_ID_OR_TO_ADD_EMPTY => array(AlertType::ERROR, 'Zvolte prosím úroveň a stránky k přidání.' ),
		Alert::EDIT_LEVEL_NO_NAME => array(AlertType::ERROR, 'Chyba změny sekce/úrovně.' ),
		Alert::EDIT_MAILS_REMOVED => array(AlertType::SUCCESS, 'Šablona emailu byla odebrána.' ),
		Alert::EDIT_MAILS_UPDATED => array(AlertType::SUCCESS, 'Šablona emailu byla upravena.' ),
		Alert::EDIT_OTHER_PAGES_REMOVED => array(AlertType::SUCCESS, 'Stránka byla nastavena.' ),
		Alert::EDIT_OTHER_PAGES_UPDATED => array(AlertType::SUCCESS, 'Stránka byla nastavena.' ),
		Alert::SETTINGS_SETTINGS_UPDATED => array(AlertType::SUCCESS, 'Nastavení uložena.' ),
		Alert::SETTINGS_SETTINGS_NO_VALID_PAGE => array(AlertType::ERROR, 'Stránka nenalezena.' ),
	);

	public static function showErrors(): string
	{
		$errorKey = self::findValidErrorKey();

		if ($errorKey !== null) {
			$error = self::getError($errorKey);

			return sprintf('<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
				$error[0],
				$error[1],
			);
		}

		return '';
	}

	private static function findValidErrorKey(): string|null {
		if ( isset( $_GET['e'], self::$alerts[ $_GET['e'] ] ) && is_string( $_GET['e'] ) ) {
			return $_GET['e'];
		}

		return null;
	}

	/** @return array<string> */
	private static function getError(string $key): array
	{
		return self::$alerts[$key];
	}

}
