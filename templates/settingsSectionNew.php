<?php

use FapiMember\Deprecated\FapiMemberTools;
use FapiMember\Utils\AlertProvider;

echo FapiMemberTools::heading();
?>

<div class="page both">
    <div class="withSections">
        <div class="a">
            <h3><?php echo __( 'Členské sekce/úrovně', 'fapi-member' ); ?></h3>
			<?php echo AlertProvider::showErrors(); ?>
			<?php echo FapiMemberTools::levels() ?>
        </div>
        <div class="b">
            <div class="subsubmenu">
				<?php echo FapiMemberTools::submenuItem('settingsSectionNew', 'Vytvořit novou sekci', $subpage) ?>
				<?php echo FapiMemberTools::submenuItem('settingsLevelNew', 'Vytvořit novou úroveň', $subpage) ?>
            </div>
			<?php echo FapiMemberTools::formStart('new_section') ?>
            <div class="row">
                <label for="fapiMemberSectionName"><?php echo __( 'Název členské sekce', 'fapi-member' ); ?></label>
                <input type="text" name="fapiMemberSectionName" id="fapiMemberSectionName" placeholder=""
                       value="">
            </div>
            <div class="row controls">
                <input type="submit" class="primary" name="" id="" value="<?php echo __( 'Vytvořit sekci', 'fapi-member' ); ?>">
            </div>
            </form>
        </div>
    </div>

</div>
</div>
