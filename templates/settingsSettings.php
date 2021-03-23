<?php

include( __DIR__ . '/functions.php' );

echo heading();

global $FapiPlugin;
$currentPageId = $FapiPlugin->getSetting( 'login_page_id' );

?>

<div class="page wider">
	<?php echo showErrors(); ?>
    <div class="onePageOther" style="max-width: 36rem">
        <h3>Stránka pro přihlášení</h3>
        <p>Stránka je společná pro všechny sekce/úrovně.</p>

		<?php echo  formStart( 'set_settings' ) ?>
        <div class="row submitInline">
            <label for="login_page_id">Vyberte stránku</label>
            <select type="text" name="login_page_id" id="login_page_id">
                <option value="">-- nevybírat</option>
				<?php echo  allPagesAsOptions( $currentPageId ) ?>
            </select>
            <input type="submit" class="primary" value="Uložit">
        </div>
        </form>
    </div>
</div>
</div>