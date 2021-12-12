<?php

use FapiMember\FapiMemberTools;

echo FapiMemberTools::heading();
?>

<div class="page both">
    <div class="withSections">
        <div class="a">
            <h3><?php echo __('Členské sekce/úrovně', 'fapi'); ?></h3>
			<?php echo FapiMemberTools::showErrors(); ?>
			<?php echo FapiMemberTools::levelsSelectionNonJs() ?>
        </div>
        <div class="b">
            <div>
				<?php
				$level = (isset($_GET['level'])) ? FapiMemberTools::sanitizeLevelId($_GET['level']) : null;
				if ($level === null) {
					echo '<p>' . __('Zvolte prosím sekci/úroveň vlevo.', 'fapi') . '</p>';
				} else {
					global $FapiPlugin;
					$fapiLevels = $FapiPlugin->levels();
					$levelTerm = $fapiLevels->loadById($level);
					$isSection = ($levelTerm->parent === 0) ? true : false;
					$defaultLoginPageId = $FapiPlugin->getSetting('login_page_id');

					$templates = $fapiLevels->loadEmailTemplatesForLevel($level);

					$pages = [
						'login' => [
							't' => __('Přihlašovací stránka', 'fapi'),
							'd' => __('Vyberte stránku, kde je umístěn přihlašovací formulář.', 'fapi') .
								' <br> ' . __('Stránka nesmí být zařazena jako členská.', 'fapi'),
						],
						'afterLogin' => [
							't' => __('Stránka po přihlášení', 'fapi'),
							'd' => __('Vyberte stránku, která se zobrazí uživatelům po přihlášení do členské 
                                        sekce nebo úrovně.', 'fapi'),
						],
						'noAccess' => [
							't' => __('Stránka, když uživatel nemá přístup', 'fapi'),
							'd' => __('Vyberte stránku, která se zobrazí uživateli, pokud nemá přístup na uzamčenou stránku.') . ' <br> ' .
								__('Stránka se většinou využívá pro výzvu ke koupi nebo prodloužení členství.', 'fapi'),
						],
					];

					$currentOtherPages = $fapiLevels->loadOtherPagesForLevel($level);

					foreach ($pages as $key => $setting) {
						$currentPageId = isset($currentOtherPages[$key]) ? $currentOtherPages[$key] : null;
						?>
                        <div class="onePageOther">
                            <h3><?php echo $setting['t'] ?></h3>
                            <p><?php echo $setting['d'] ?></p>

							<?php echo FapiMemberTools::formStart('set_other_page') ?>
                            <input type="hidden" name="level_id" value="<?php echo $level ?>">
                            <input type="hidden" name="page_type" value="<?php echo $key ?>">
                            <div class="row submitInline noLabel">
                                <select type="text" name="page" id="page">
									<?php
									if ($defaultLoginPageId && $key === 'login') {
										echo '<option value="">-- ' . FapiMemberTools::getPageTitle($defaultLoginPageId) . ' --</option>';
									} else {
										echo '<option value="">' . __('-- nevybrána --', 'fapi') . '</option>';
									}
									?>
									<?php echo FapiMemberTools::allPagesAsOptions($currentPageId) ?>
                                </select>
                                <input type="submit" class="primary" value="<?php echo __('Uložit', 'fapi'); ?>">
                            </div>
                            </form>
                        </div>
						<?php
					}
				}
				?>
            </div>
        </div>
    </div>
</div>
</div>
