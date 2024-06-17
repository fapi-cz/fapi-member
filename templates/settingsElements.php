<?php

use FapiMember\Deprecated\FapiMemberTools;

echo FapiMemberTools::heading();
?>

<div class="page wider">
    <h4><?php echo __( 'Formulář pro přihlášení', 'fapi-member' ); ?></h4>
    <p><?php echo __( 'Formulář vložte do svého webu na požadovaná místa pomocí shortcode', 'fapi-member' ); ?> <code>[fapi-member-login]</code>.
    </p>
    <p><?php echo __( 'Formulář se zobrazí po znovunačtení stránky.', 'fapi-member' ); ?></p>

    <div class="showcase">
        <img src="<?php echo plugins_url('fapi-member/media/login.png') ?>"
             alt="<?php echo __( 'Snímek obrazovky', 'fapi-member' ); ?>">
    </div>

    <hr>

    <h4><?php echo __( 'Uživatelské okénko', 'fapi-member' ); ?></h4>
    <p><?php echo __( 'Okénko vložte do svého webu na požadovaná místa pomocí shortcode', 'fapi-member' ); ?> <code>[fapi-member-user]</code>.
    </p>
    <p><?php echo __( 'Uživatelské okénko bude funkční pro uživatele všech členských sekcí a úrovní.', 'fapi-member' ); ?></p>

    <div class="showcase">
        <img src="<?php echo plugins_url('fapi-member/media/user-window-both.png') ?>"
             alt="<?php echo __( 'Snímek obrazovky', 'fapi-member' ); ?>">
    </div>

	<h4><?php echo __( 'Datum expirace pro členskou sekci nebo úrověň', 'fapi-member' ); ?></h4>
	<p><?php echo __( 'Vypíše datum expirace zvolené členské sekce nebo úrovně', 'fapi-member' ); ?> <code>[fapi-member-user-section-expiration section=x]</code>.
	</p>
	<p><?php echo __( 'Místo proměnné "x" zadejte ID  členské sekce a místo tohoto shortcodu se uživateli vypíše datum do kdy má přístup do dané členské sekce nebo úrovně', 'fapi-member' ); ?></p>

	<h4><?php echo __( 'Jak shortcode použít', 'fapi-member' ); ?></h4>
	<div class="showcase">
		<img src="<?php echo plugins_url('fapi-member/media/images/fapi-member-user-section-expiration-shortcode-example.png') ?>"
			 alt="<?php echo __( 'Snímek obrazovky', 'fapi-member' ); ?>">
	</div>

	<h4><?php echo __( 'Jak to poté vidí člen', 'fapi-member' ); ?></h4>
	<div class="showcase">
	<img src="<?php echo plugins_url('fapi-member/media/images/fapi-member-user-section-expiration-shortcode-result.png') ?>"
		 alt="<?php echo __( 'Snímek obrazovky', 'fapi-member' ); ?>">
	</div>

	<hr>
 	<h4><?php _e( 'Odemčení úrovně tlačítkem', 'fapi-member' ); ?></h4>
    <p><?php _e( 'Tlačítko kterým uživatel může odeknou úroveň', 'fapi-member' ); ?> <code>[fapi-member-unlock-level level=8 page=2]</code>.
    <?php   _e('Parametr "page" representuje ID stránky, na kterou bude uživatel přesměrován, po odemčení úrovně. Tento parametr je volitelný. Pokud nebude zadán, uživatel bude odkázán na jednu ze stránek přiřazených k úrovni.', 'fapi-member')?>
    </p>

    <h4><?php _e( 'Čas zbývající do odemčení úrovně', 'fapi-member' ); ?></h4>
    <p><?php _e( 'Datum odemčení členské úrovně ve formátu "01.01.2023" můžete zobrazit pomocí shortcode', 'fapi-member' ); ?> <code>[fapi-member-level-unlock-date]</code>.
    <?php   _e('Tento kód doporučujeme umístit na stránku nastavenou v části "Postupné uvolňování obsahu".')?>
    </p>
</div>
</div>
