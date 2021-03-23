<?php

include( __DIR__ . '/functions.php' );

echo heading();
?>

<div class="page both">
    <div class="withSections">
        <div class="a">
            <h3>Struktura uzavřených sekcí a úrovní</h3>
			<?php echo showErrors(); ?>
			<?php echo  levels() ?>
        </div>
        <div class="b">
            <div class="subsubmenu">
				<?php echo  submenuItem( 'settingsSectionNew', 'Vytvořit novou sekci', $subpage ) ?>
				<?php echo  submenuItem( 'settingsLevelNew', 'Vytvořit novou úroveň', $subpage ) ?>
            </div>
			<?php echo  formStart( 'new_section' ) ?>
            <div class="row">
                <label for="fapiMemberSectionName">Název členské sekce</label>
                <input type="text" name="fapiMemberSectionName" id="fapiMemberSectionName" placeholder=""
                       value="">
            </div>
            <div class="row controls">
                <input type="submit" class="primary" name="" id="" value="Vytvořit sekci">
            </div>
            </form>
        </div>
    </div>

</div>
</div>