<?php

include( __DIR__ . '/functions.php' );

echo heading();
?>

<div class="page both">
    <div class="withSections">
        <div class="a">
            <h3>Struktura uzavřených sekcí a úrovní</h3>
			<?php echo showErrors(); ?>
			<?= levels() ?>
        </div>
        <div class="b">
            <div class="subsubmenu">
				<?= submenuItem( 'settingsSectionNew', 'Vytvořit novou sekci', $subpage ) ?>
				<?= submenuItem( 'settingsLevelNew', 'Vytvořit novou úroveň', $subpage ) ?>
            </div>
			<?= formStart( 'new_level' ) ?>
            <div class="row">
                <label for="fapiMemberLevelName">Název členské úrovně</label>
                <input type="text" name="fapiMemberLevelName" id="fapiMemberLevelName" placeholder=""
                       value="">
            </div>
            <div class="row">
                <label for="fapiMemberLevelParent">Zařadit do členské sekce</label>
                <select name="fapiMemberLevelParent" id="fapiMemberLevelParent">
					<?= getLevelOptions() ?>
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