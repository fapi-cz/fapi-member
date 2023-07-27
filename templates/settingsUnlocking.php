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
                        <p><?php _e('Pro nastavení odemykání členských sekcí, vyberte prosím úroveň vlevo', 'fapi-member') ?></p>

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
                    $inputVal = get_term_meta($level, FapiMemberPlugin::DAYS_TO_UNLOCK_META_KEY, true) ?
                        get_term_meta($level, FapiMemberPlugin::DAYS_TO_UNLOCK_META_KEY, true) :
                        '0';
                ?>
                    <div class="onePageOther">
                        <h3><?php _e('Počet dní od registrace uživatele, po kterých má být vybraná sekce/úroveň zpřístupněna.', 'fapi-member') ?></h3>
                        <p><?php _e('0 = Sekce bude přístupná ihned po registraci', 'fapi-member') ?></p>

                        <?php echo FapiMemberTools::formStart('set_section_unlocking') ?>
                        <input type="hidden" name="level_id" value="<?php echo $level ?>">
                        <div style="justify-content:start" class="row submitInline noLabel">
                            <input type="number" min="0" max="100" name="days_to_unlock" value="<?php echo $inputVal ?>" oninput="this.value = Math.abs(this.value)">
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