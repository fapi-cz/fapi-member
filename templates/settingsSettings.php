<?php

use FapiMember\FapiMemberTools;

echo FapiMemberTools::heading();

global $FapiPlugin;
$currentPageId = $FapiPlugin->getSetting('login_page_id');

?>

<div class="page wider">
	<?php echo FapiMemberTools::showErrors(); ?>
    <div class="onePageOther" style="max-width: 36rem">
        <h3><?php echo __('Stránka pro přihlášení', 'fapi'); ?></h3>
        <p><?php echo __('Vyberte společnou přihlašovací stránku pro všechny sekce/úrovně.', 'fapi'); ?></p>

		<?php echo FapiMemberTools::formStart('set_settings') ?>
        <div class="row submitInline noLabel">
            <select type="text" name="login_page_id" id="login_page_id">
                <option value=""><?php echo __('-- nevybrána --', 'fapi'); ?></option>
				<?php echo FapiMemberTools::allPagesAsOptions($currentPageId) ?>
            </select>
            <input type="submit" class="primary" value="<?php echo __('Uložit', 'fapi'); ?>">
        </div>
        </form>
    </div>
</div>
</div>
