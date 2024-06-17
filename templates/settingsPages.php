<?php

use FapiMember\Deprecated\FapiMemberTools;
use FapiMember\Model\Enums\Keys\SettingsKey;
use FapiMember\Utils\AlertProvider;

echo FapiMemberTools::heading();
?>

<div class="page both">
    <div class="withSections">
        <div class="a">
            <h3><?php echo __( 'Členské sekce/úrovně', 'fapi-member' ); ?></h3>
			<?php echo AlertProvider::showErrors(); ?>
			<?php echo FapiMemberTools::levelsSelectionNonJs() ?>
        </div>
        <div class="b">
            <div>
				<?php
				$level = (isset($_GET['level'])) ? FapiMemberTools::sanitizeLevelId($_GET['level']) : null;
				if ($level === null) {
					echo '<p>' . __( 'Zvolte prosím sekci/úroveň vlevo.', 'fapi-member' ) . '</p>';
				} else {
					global $FapiPlugin;
					global $settingsRepository;

					$fapiLevels = $FapiPlugin->levels();
					$levelTerm = $fapiLevels->loadById($level);
					$isSection = $levelTerm->parent === 0;
					$defaultPages = [
						'login' => $settingsRepository->getSetting(SettingsKey::LOGIN_PAGE),
						'afterLogin' => $settingsRepository->getSetting(SettingsKey::DASHBOARD_PAGE)
					];
					$templates = $fapiLevels->loadEmailTemplatesForLevel($level);

					$pages = [
						'login' => [
							't' => __( 'Přihlašovací stránka', 'fapi-member' ),
							'd' => __( 'Vyberte stránku, kde je umístěn přihlašovací formulář.', 'fapi-member' ) .
								' <br> ' . __( 'Stránka nesmí být zařazena jako členská.', 'fapi-member' ),
						],
						'afterLogin' => [
							't' => __( 'Nástěnka', 'fapi-member' ),
							'd' => __('Vyberte stránku, která se zobrazí uživatelům po přihlášení do členské 
                                        sekce nebo úrovně, tzn. nástěnka.', 'fapi'),
						],
						'noAccess' => [
							't' => __( 'Stránka, když uživatel nemá přístup', 'fapi-member' ),
							'd' => __('Vyberte stránku, která se zobrazí uživateli, pokud nemá přístup na uzamčenou stránku.') . ' <br> ' .
								__( 'Stránka se většinou využívá pro výzvu ke koupi nebo prodloužení členství.', 'fapi-member' ),
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
									if (isset($defaultPages[$key])) {
										echo '<option value="">-- ' . FapiMemberTools::getPageTitle($defaultPages[$key]) . ' --</option>';
									} else {
										echo '<option value="">' . __( '-- nevybrána --', 'fapi-member' ) . '</option>';
									}
									?>
									<?php echo FapiMemberTools::allPagesAsOptions($currentPageId) ?>
                                </select>
                                <input type="submit" class="primary" value="<?php echo __( 'Uložit', 'fapi-member' ); ?>">
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
