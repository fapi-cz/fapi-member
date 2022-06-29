<?php

use FapiMember\FapiMemberTools;

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
</div>
</div>
