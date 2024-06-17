<?php

use FapiMember\Deprecated\FapiMemberTools;
use FapiMember\Utils\AlertProvider;

echo FapiMemberTools::heading();
$selectedLevel = (isset($_GET['level'])) ? (int) $_GET['level'] : null;
?>

<div class="page both">
    <div class="withSections">
        <div class="a">
            <h3><?php echo __( 'Členské sekce/úrovně', 'fapi-member' ); ?></h3>
			<?php echo AlertProvider::showErrors(); ?>
			<?php echo FapiMemberTools::levelsSelection() ?>
        </div>
        <div class="b">
            <div class="subsubmenu">
				<?php echo FapiMemberTools::subSubmenuItem('settingsContentAdd', 'Přiřazené stránky a příspěvky', $subpage) ?>
				<?php echo FapiMemberTools::subSubmenuItem('settingsContentRemove',
					'Úprava přiřazení',
					$subpage) ?>
            </div>

			<?php if ($selectedLevel): ?>
				<?php echo FapiMemberTools::formStart('remove_pages', ['removePagesForm', 'pages']) ?>
                <input type="hidden" name="level_id" value="<?php echo $selectedLevel ?>">
                <div class="inner">
					<?php echo FapiMemberTools::allPagesForForm($selectedLevel) ?>
                </div>
                <div class="row controls">
                    <button class="btn outline"><?php echo __( 'Uložit', 'fapi-member' ); ?></button>
                </div>
                </form>
			<?php else: ?>
                <div class="inner">
                    <p class="pleaseSelectLevel"><?php echo __( 'Prosím zvolte sekci/úroveň.', 'fapi-member' ); ?></p>
                </div>
			<?php endif; ?>

        </div>
    </div>
</div>
</div>
