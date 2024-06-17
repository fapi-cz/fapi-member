<?php

use FapiMember\Container\Container;
use FapiMember\Deprecated\FapiMemberTools;
use FapiMember\Model\Enums\Keys\MetaKey;
use FapiMember\Repository\PageRepository;
use FapiMember\Utils\AlertProvider;

echo FapiMemberTools::heading();
?>

<div class="page both">
    <div class="withSections">
        <div class="a">
            <h3><?php echo __('Členské sekce/úrovně', 'fapi-member'); ?></h3>
            <?php echo AlertProvider::showErrors(); ?>
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
					$pageRepository = Container::get(PageRepository::class);
                	$currentTimeLockedPageId = $pageRepository->getTimedUnlockNoAccessPageId();;
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
						$daysToUnlockVal = get_term_meta($level, MetaKey::DAYS_TO_UNLOCK, true) ?
							get_term_meta($level, MetaKey::DAYS_TO_UNLOCK, true) :
							'0';

						$dateUnlockVal = get_term_meta($level, MetaKey::DATE_UNLOCK, true) ?
							get_term_meta($level, MetaKey::DATE_UNLOCK, true) :
							null;

						$timeUnlockVal = get_term_meta($level, MetaKey::TIME_UNLOCK, true) ?
							get_term_meta($level, MetaKey::TIME_UNLOCK, true) :
							false;

						$buttonUnlockVal = get_term_meta($level, MetaKey::BUTTON_UNLOCK, true) ?
							get_term_meta($level, MetaKey::BUTTON_UNLOCK, true) :
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
						<div>
							<input type="radio" name="time_unlock" value="disallow" id="disallow"
								<?php if ($timeUnlockVal === 'disallow') echo 'checked'; ?>
							>
							<label for="disallow">Nepovolovat</label>
						</div>
						<div>
							<input type="radio" name="time_unlock" value="date" id="date"
								<?php if ($timeUnlockVal === 'date') echo 'checked'; ?>
							>
							<label for="date">Od pevného data</label>
						</div>
						<div>
							<input type="radio" name="time_unlock" value="days" id="days"
								<?php if ($timeUnlockVal === 'days') echo 'checked'; ?>
							>
							<label for="days">Počet dní od registrace</label>
						</div>

						<div id="date_settings_content">
							<p><?php _e('Datum kdy bude sekce/úroveň odemčena pro všechny uživatele.', 'fapi-member') ?></p>
							<input type="hidden" name="level_id" value="<?php echo $level ?>">
							<input type="date" min="0" max="100" name="unlock_date" value="<?php echo $dateUnlockVal ?>"">
						</div>
						<div id="days_settings_content">
							<p><?php _e('Počet dní od registrace uživatele do členské sekce, po kterých má být vybraná sekce/úroveň zpřístupněna.', 'fapi-member') ?></p>
							<input type="hidden" name="level_id" value="<?php echo $level ?>">
							<input type="number" min="0" max="100" name="days_to_unlock" value="<?php echo $daysToUnlockVal ?>" oninput="this.value = Math.abs(this.value)">
							<p><?php _e('0 = Sekce bude přístupná ihned po registraci', 'fapi-member') ?></p>
							<p><?php _e('3 = Sekce bude přístupná 3 den po registraci', 'fapi-member') ?></p>
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
	var timeLockRadios = document.getElementsByName('time_unlock');
	var selectedRadioValue = document.querySelector('input[name="time_unlock"]:checked')?.value;

	var buttonUnlockCheckbox = document.getElementsByName('button_unlock')[0];
	toggleSettings(buttonUnlockCheckbox.checked, 'button_unlock_settings');

	timeLockRadios.forEach(function(radio) {
		toggleSettings(
			radio.value === selectedRadioValue,
			radio.value + '_settings_content',
		);

		radio.addEventListener('change', function(e) {
			selectedRadioValue = document.querySelector('input[name="time_unlock"]:checked')?.value;
			timeLockRadios.forEach(function(radio) {
				toggleSettings(
				radio.value === selectedRadioValue,
				radio.value + '_settings_content',
			);
			});
		});
	});

	buttonUnlockCheckbox.addEventListener('change', function(e) {
        toggleSettings(e.target.checked, 'button_unlock_settings');
    });

	function toggleSettings(show, id) {
		console.log(show);
		var element = document.getElementById(id);

		if (element == undefined) {
			return;
		}

		if (show) {
			element.style.display = 'block';
		} else {
			element.style.display = 'none';
		}
	}

	function getSelectedOption(radios) {
		for (let i = 0; i < radios.length; i++) {
			if (radios[i].checked) {
				return radios[i].value;
			}
		}
	}
</script>