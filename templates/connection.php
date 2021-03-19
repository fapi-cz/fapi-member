<?php

include( __DIR__ . '/functions.php' );
global $FapiPlugin;

echo heading();
?>
<div class="page">
    <h3>Propojení s Vaším účtem FAPI</h3>
	<?php echo showErrors(); ?>
	<?= formStart( 'api_credentials_submit' ) ?>
    <div class="row">
        <label for="fapiMemberApiEmail">Uživatelské jméno (e-mail)</label>
        <input type="text" name="fapiMemberApiEmail" id="fapiMemberApiEmail" placeholder="me@example.com"
               value="<?php echo get_option( 'fapiMemberApiEmail', '' ) ?>">
    </div>
    <div class="row">
        <label for="fapiMemberApiKey">API klíč</label>
        <input type="text" name="fapiMemberApiKey" id="fapiMemberApiKey" placeholder=""
               value="<?php echo get_option( 'fapiMemberApiKey', '' ) ?>">
    </div>
    <div class="row controls">
        <input type="submit" class="primary" name="" id="" value="Propojit s FAPI">
    </div>
    </form>
    <p>
        Stav propojení:
		<?= ( $FapiPlugin->recheckApiCredentials() ) ? '<span class="ok">propojeno</span>' : '<span class="ng">nepropojeno</span>' ?>
        .
    </p>
</div>
<?= help() ?>
</div>