<?php

use FapiMember\FapiMemberTools;

echo FapiMemberTools::heading();
?>

<div class="page both">
    <div class="withSections">
        <div class="a">
            <h3><?php echo __('Členské sekce/úrovně', 'fapi'); ?></h3>
			<?php echo FapiMemberTools::showErrors(); ?>
			<?php echo FapiMemberTools::levels() ?>
        </div>
        <div class="b">
            <div class="subsubmenu">
				<?php echo FapiMemberTools::submenuItem('settingsSectionNew', 'Vytvořit novou sekci', $subpage) ?>
				<?php echo FapiMemberTools::submenuItem('settingsLevelNew', 'Vytvořit novou úroveň', $subpage) ?>
            </div>
			<?php echo FapiMemberTools::formStart('new_level') ?>
            <div class="row">
                <label for="fapiMemberLevelName"><?php echo __('Název členské úrovně', 'fapi'); ?></label>
                <input type="text" name="fapiMemberLevelName" id="fapiMemberLevelName" placeholder=""
                       value="">
            </div>
            <div class="row">
                <label for="fapiMemberLevelParent"><?php echo __('Zařadit do členské sekce', 'fapi'); ?></label>
                <select name="fapiMemberLevelParent" id="fapiMemberLevelParent">
					<?php echo FapiMemberTools::getLevelOptions() ?>
                </select>
            </div>
            <div class="row controls">
                <input type="submit" class="primary" name="" id="" value="<?php echo __('Vytvořit úroveň', 'fapi'); ?>">
            </div>
            </form>
        </div>
    </div>

</div>
</div>
