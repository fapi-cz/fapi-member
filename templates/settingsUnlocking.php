<?php

use FapiMember\FapiMemberPlugin;
use FapiMember\FapiMemberTools;

echo FapiMemberTools::heading();
?>

<div class="page both">
    <div class="withSections">
        <div class="a">
            <h3><?php echo __('Členské sekce/úrovně', 'fapi-member'); ?></h3>
            <?php echo FapiMemberTools::showErrors(); ?>
            <?php echo FapiMemberTools::levelsSelectionNonJs() ?>
        </div>
        <div class="b">
            <div>
                <?php
                $level = (isset($_GET['level'])) ? FapiMemberTools::sanitizeLevelId($_GET['level']) : null;
                global $FapiPlugin;
				$term = get_term($level);

                if (!is_wp_error($term)) {
                    $parent_term_id = $term->parent;
                }

                if ($level === null) {
                	$currentTimeLockedPageId = $FapiPlugin->getSetting('time_locked_page_id');
                ?>
                    <div class="onePageOther">
                        <h3><?php _e('Stránka, která se zobrazí, když obsah ještě nebyl odemčen.', 'fapi-member') ?></h3>
                        <p><?php _e('Úroveň musí mít povoleno časově omezené odemykání, aby byl uživatel přesměrován na tuto stránku.', 'fapi-member') ?></p>

                        <?php echo FapiMemberTools::formStart('set_section_unlocking') ?>
                        <input type="hidden" name="level_id" value="<?php echo $level ?>">
                        <div style="justify-content:start" class="row submitInline noLabel">
                            <select type="text" name="time_locked_page_id" id="time_locked_page_id">
                                <option value=""><?php echo __('-- nevybrána --', 'fapi-member'); ?></option>
                                <?php echo FapiMemberTools::allPagesAsOptions($currentTimeLockedPageId) ?>
                            </select>
                            <input type="submit" class="primary" value="<?php _e('Uložit', 'fapi-member'); ?>">
                        </div>
                        </form>
                    </div>
                <?php
					} elseif ($parent_term_id === 0) {
                ?>
                    <h3><?php _e('Zvolili jste členskou sekci, prosím zvolte úroveň.', 'fapi-member') ?></h3>
                <?php
					} else {
						$daysToUnlockVal = get_term_meta($level, FapiMemberPlugin::DAYS_TO_UNLOCK_META_KEY, true) ?
							get_term_meta($level, FapiMemberPlugin::DAYS_TO_UNLOCK_META_KEY, true) :
							'0';

						$timeUnlockVal = get_term_meta($level, FapiMemberPlugin::TIME_UNLOCK_META_KEY, true) ?
							get_term_meta($level, FapiMemberPlugin::TIME_UNLOCK_META_KEY, true) :
							false;

						$buttonUnlockVal = get_term_meta($level, FapiMemberPlugin::BUTTON_UNLOCK_META_KEY, true) ?
							get_term_meta($level, FapiMemberPlugin::BUTTON_UNLOCK_META_KEY, true) :
							false;
                ?>
                    <div class="onePageOther">
                        <?php echo FapiMemberTools::formStart('set_section_unlocking') ?>

						<h3><?php _e('Odemknutí tlačítkem', 'fapi-member') ?></h3>
						<label>Povolit: </label>
						<input type="checkbox" name="button_unlock" <?php if ($buttonUnlockVal) echo 'checked'; ?>>
                        <div id="button_unlock_settings">
							<p><?php _e('K odemčení úrovně musí uživatel již mít přístup do dané sekce.', 'fapi-member') ?></p>
							<label><?php _e('Shrotcode talčítka pro uvolnění obsahu: ', 'fapi-member') ?></label>
							<code><?= '[fapi-member-unlock-level level=' . $level . ']' ?></code>
						</div>

						<br><br>

						<h3><?php _e('Časově omezené odemykání úrovně', 'fapi-member') ?></h3>
						<label>Povolit: </label>
						<input type="checkbox" name="time_unlock" <?php if ($timeUnlockVal) echo 'checked'; ?>>

						<div id="time_lock_settings">
							<p><?php _e('Počet dní od registrace uživatele do členské sekce, po kterých má být vybraná sekce/úroveň zpřístupněna.', 'fapi-member') ?></p>
								<input type="hidden" name="level_id" value="<?php echo $level ?>">
								<input type="number" min="0" max="100" name="days_to_unlock" value="<?php echo $daysToUnlockVal ?>" oninput="this.value = Math.abs(this.value)">
								<p><?php _e('0 = Sekce bude přístupná ihned po registraci', 'fapi-member') ?></p>
							</div>
							<div style="justify-content:start" class="row submitInline noLabel">
								<input type="submit" class="primary" value="<?php _e('Uložit', 'fapi-member'); ?>">
							</div>
                        </form>
                    </div>
                <?php } ?>

            </div>
        </div>
    </div>
</div>
</div>

<script>
	var timeLockCheckbox = document.getElementsByName('time_unlock')[0];
	toggleSettings(timeLockCheckbox.checked, 'time_lock_settings');

	var buttonUnlockCheckbox = document.getElementsByName('button_unlock')[0];
	toggleSettings(buttonUnlockCheckbox.checked, 'button_unlock_settings');

	timeLockCheckbox.addEventListener('change', function(e) {
        toggleSettings(e.target.checked, 'time_lock_settings');
    });

	buttonUnlockCheckbox.addEventListener('change', function(e) {
        toggleSettings(e.target.checked, 'button_unlock_settings');
    });

	function toggleSettings(show, id) {
		if (show) {
			document.getElementById(id).style.display = 'block';
		} else {
			document.getElementById(id).style.display = 'none';
		}
	}
</script>
