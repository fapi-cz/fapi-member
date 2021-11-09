<?php

use FapiMember\FapiMemberTools;

echo FapiMemberTools::heading();
$selectedLevel = (isset($_GET['level'])) ? (int) $_GET['level'] : null;
?>

<script id="LevelToPage" type="application/json"><?php echo FapiMemberTools::levelToPageJson() ?></script>


<div class="page both">
    <div class="withSections">
        <div class="a">
            <h3>Členské sekce/úrovně</h3>
			<?php echo FapiMemberTools::showErrors(); ?>
			<?php echo FapiMemberTools::levelsSelection() ?>
        </div>
        <div class="b">
            <div class="subsubmenu">

				<?php echo FapiMemberTools::subSubmenuItem('settingsContentAdd', 'Přiřazené stránky', $subpage) ?>
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
                        <p class="pleaseSelectLevel">Prosím zvolte sekci/úroveň.</p>
                    </div>
				<?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div>
