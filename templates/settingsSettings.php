<?php

use FapiMember\Deprecated\FapiMemberTools;
use FapiMember\Model\Enums\Keys\SettingsKey;
use FapiMember\Utils\AlertProvider;

echo FapiMemberTools::heading();

global $FapiPlugin;
global $settingsRepository;

$currentLoginPageId = $settingsRepository->getSetting(SettingsKey::LOGIN_PAGE);
$currentDashboardPageId = $settingsRepository->getSetting(SettingsKey::DASHBOARD_PAGE);

?>

<div class="page wider">
	<?php echo AlertProvider::showErrors(); ?>

    <div class="onePageOther" style="max-width: 36rem">
		<?php echo FapiMemberTools::formStart('set_settings') ?>

        <h3><?php echo __( 'Stránka pro přihlášení', 'fapi-member' ); ?></h3>
        <p><?php echo __( 'Vyberte společnou přihlašovací stránku pro všechny sekce/úrovně.', 'fapi-member' ); ?></p>
        <div class="row submitInline noLabel">
            <select type="text" name="login_page_id" id="login_page_id">
                <option value=""><?php echo __( '-- nevybrána --', 'fapi-member' ); ?></option>
				<?php echo FapiMemberTools::allPagesAsOptions($currentLoginPageId) ?>
            </select>
        </div>

		<h3><?php echo __( 'Nástěnka', 'fapi-member' ); ?></h3>
		<p><?php echo __( 'Vyberte společnou stránku po příhlášení tzn. nástěnku.', 'fapi-member' ); ?></p>

		<div class="row submitInline noLabel">
			<select type="text" name="dashboard_page_id" id="dashboard_page_id">
				<option value=""><?php echo __( '-- nevybrána --', 'fapi-member' ); ?></option>
				<?php echo FapiMemberTools::allPagesAsOptions($currentDashboardPageId) ?>
			</select>
		</div>

		<div class="row submitInline noLabel">
			<input type="submit" class="primary" value="<?php echo __( 'Uložit', 'fapi-member' ); ?>">
		</div>
        </form>
    </div>
</div>
</div>
