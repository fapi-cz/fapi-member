<?php

include( __DIR__ . '/functions.php' );

echo FapiMemberTools::heading();
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

				<?php echo FapiMemberTools::subSubmenuItem( 'settingsContentAdd', 'Přiřazení stránek', $subpage ) ?>
				<?php echo FapiMemberTools::subSubmenuItem( 'settingsContentRemove',
				                                            'Obsah sekce/Odebírání stránek',
				                                            $subpage ) ?>
            </div>
            <div>
				<?php echo FapiMemberTools::formStart( 'add_pages', [ 'addPagesForm', 'pages' ] ) ?>
                <input type="hidden" name="level_id" value="">
                <div class="inner">
					<?php echo FapiMemberTools::allPagesForForm() ?>
                </div>
                <div class="row controls">
                    <button class="btn primary">Přiřadit vybrané</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>