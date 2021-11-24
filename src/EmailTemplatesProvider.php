<?php

namespace FapiMember;

final class EmailTemplatesProvider
{

	const FAPI_EMAILS
		= [
			'section' => [
				FapiLevels::EMAIL_TYPE_AFTER_REGISTRATION => [
					's' => 'Posílám vám klíče od %%SEKCE%%',
					'b' => 'Krásný den,
chci vám osobně poděkovat za registraci a přivítat vás mezi námi.

Stojíte před dveřmi do sekce %%SEKCE%%

Zde jsou vaše přihlašovací údaje: 
uživatelské jméno: %%PRIHLASOVACI_JMENO%%
heslo: %%HESLO%%

Vstoupit můžete tudy: %%PRIHLASENI_ODKAZ%%. ',
				],
				FapiLevels::EMAIL_TYPE_AFTER_MEMBERSHIP_PROLONGED => [
					's' => 'Díky za to, že u nás v %%SEKCE%% zůstáváte',
					'b' => 'Krásný den,
moc mě těší, že u nás v sekci %%SEKCE%% zůstáváte déle.

Nyní máte vstup prodloužený do %%CLENSTVI_DO%%.',
				],
				FapiLevels::EMAIL_TYPE_AFTER_ADDING => [
					's' => 'Teď už můžete i do %%SEKCE%%',
					'b' => 'Krásný den, 
těší mě, že máte o mou práci takový zájem.

Právě Vám otvírám dveře do sekce %%SEKCE%%.

Co je uvnitř, uvidíte ve svém účtu: %%PRIHLASENI_ODKAZ%%.',
				],
			],
			'level' => [
				FapiLevels::EMAIL_TYPE_AFTER_REGISTRATION => [
					's' => 'Posílám vám klíče od %%UROVEN%%',
					'b' => 'Krásný den,
chci vám osobně poděkovat za registraci a přivítat vás mezi námi.

Stojíte před dveřmi do sekce %%UROVEN%%

Zde jsou vaše přihlašovací údaje: 
uživatelské jméno: %%PRIHLASOVACI_JMENO%%
heslo: %%HESLO%%

Vstoupit můžete tudy: %%PRIHLASENI_ODKAZ%%. ',
				],
				FapiLevels::EMAIL_TYPE_AFTER_MEMBERSHIP_PROLONGED => [
					's' => 'Díky za to, že u nás v %%UROVEN%% zůstáváte',
					'b' => 'Krásný den,
moc mě těší, že u nás v sekci %%UROVEN%% zůstáváte déle.

Nyní máte vstup prodloužený do %%CLENSTVI_DO%%.',
				],
				FapiLevels::EMAIL_TYPE_AFTER_ADDING => [
					's' => 'Teď už můžete i do %%UROVEN%%',
					'b' => 'Krásný den, 
těší mě, že máte o mou práci takový zájem.

Právě Vám otvírám dveře do sekce %%UROVEN%%.

Co je uvnitř, uvidíte ve svém účtu: %%PRIHLASENI_ODKAZ%%.',
				],
			],
		];

}
