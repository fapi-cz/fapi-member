<?php

use FapiMember\FapiMemberTools;
global $FapiPlugin;
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

    <h4><?php _e( 'Čas zbývající do odemčení úrovně', 'fapi-member' ); ?></h4>
    <p><?php _e( 'Datum a čas odemčení členské úrovně ve formátu "DD.MM RRRR o hh:mm" můžete zobrazit pomocí shortcode', 'fapi-member' ); ?> <code>[fapi-member-level-unlock-date]</code>.
    <?php   _e('Tento kód doporučujeme umístit na stránku nastavenou v části "Postupné uvolňování obsahu".')?>
    </p>

    <hr>

    <h4><?php _e( 'Tlačítko pro odemčení uzamčené sekce', 'fapi-member' ); ?></h4>
    <p><?php _e( 'Zde máte možnost vygenerovat shortcode pro tlačítko pro odemknutí členských úrovní.' , 'fapi-member' ) ?>
    </p>
    <div>
    <p><label for="levels"><?php _e( 'Vyběr úrovně', 'fapi-member' ); ?></label></p>   
    </div>
        <select name="levels" id="levels">
            <?php
            $options = $FapiPlugin->getUnlockLevelsOptions();
                foreach ($options as $id => $name){
                    echo sprintf('<option value="%s">%s</option>', $id, $name);
                }
            ?>
        </select>   
    <div>
        <p>
            <label for="custom-classes" >
                <?php _e( 'Vlastní třídy CSS - jednotlivé třídy oddělte čárkou. Povolené znaky: A-Z a-z 0-9 - _ (nepovinné) ', 'fapi-member' ); ?>
            </label>
        </p>
    </div>
    <input type="text" name="custom-classes" id="custom-classes">
    <button id="generateButton"><?php _e('Generovat shortcode', 'fapi-member')?></button>
    <p><?php _e( 'Shortcode' , 'fapi-member' ) ?></p>
    <code id="level-unlock-shortcode"><?php _e( 'Zde se zobrazí shortcode' , 'fapi-member' ) ?></code>
    

</div>
</div>
