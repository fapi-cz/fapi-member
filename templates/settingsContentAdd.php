<?php

use FapiMember\Deprecated\FapiMemberTools;
use FapiMember\Utils\AlertProvider;

echo FapiMemberTools::heading();
$selectedLevel = (isset($_GET['level'])) ? (int) $_GET['level'] : null;
?>

<script id="LevelToPage" type="application/json"><?php echo FapiMemberTools::levelToPageJson() ?></script>


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
            <div>
				<?php if ($selectedLevel): ?>
                    <div class="inner">
						<?php echo FapiMemberTools::allPagesInLevel($selectedLevel) ?>
                    </div>
				<?php else: ?>
                    <div class="inner">
                        <p class="pleaseSelectLevel"><?php echo __( 'Prosím zvolte sekci/úroveň.', 'fapi-member' ); ?></p>
                    </div>
				<?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div>
