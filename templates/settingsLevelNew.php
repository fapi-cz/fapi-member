<?php

use FapiMember\FapiMemberTools;

echo FapiMemberTools::heading();
?>

<div class="page both">
    <div class="withSections">
        <div class="a">
            <h3>Členské sekce/úrovně</h3>
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
                <label for="fapiMemberLevelName">Název členské úrovně</label>
                <input type="text" name="fapiMemberLevelName" id="fapiMemberLevelName" placeholder=""
                       value="">
            </div>
            <div class="row">
                <label for="fapiMemberLevelParent">Zařadit do členské sekce</label>
                <select name="fapiMemberLevelParent" id="fapiMemberLevelParent">
					<?php echo FapiMemberTools::getLevelOptions() ?>
                </select>
            </div>
            <div class="row controls">
                <input type="submit" class="primary" name="" id="" value="Vytvořit úroveň">
            </div>
            </form>
        </div>
    </div>

</div>
</div>
