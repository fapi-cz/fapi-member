<?php

global $FapiPlugin;

use FapiMember\FapiMemberPlugin;
use FapiMember\FapiMemberTools;

echo FapiMemberTools::heading();
?>
<div class="page">
    <h3>Propojený účet FAPI</h3>
	<?php echo FapiMemberTools::showErrors(); ?>
	<?php echo FapiMemberTools::formStart('api_credentials_submit') ?>
    <div class="row">
        <label for="fapiMemberApiEmail">Uživatelské jméno (e-mail)</label>
        <input type="text" name="fapiMemberApiEmail" id="fapiMemberApiEmail" placeholder="me@example.com"
               value="<?php echo get_option(FapiMemberPlugin::OPTION_KEY_API_USER, '') ?>">
    </div>
    <div class="row">
        <label for="fapiMemberApiKey">API klíč</label>
        <input type="text" name="fapiMemberApiKey" id="fapiMemberApiKey" placeholder=""
               value="<?php echo get_option(FapiMemberPlugin::OPTION_KEY_API_KEY, '') ?>">
    </div>
    <div class="row controls">
        <input type="submit" class="primary" name="" id="" value="Propojit s FAPI">
    </div>
    </form>
    <p>
        Stav propojení:
		<?php echo ($FapiPlugin->recheckApiCredentials()) ? '<span class="ok">propojeno</span>' : '<span class="ng">nepropojeno</span>' ?>
    </p>
    <p>
        FAPI Member token: <span id="fapi-member-token"><?php echo get_option(FapiMemberPlugin::OPTION_KEY_TOKEN, '') ?></span>
        <img height="17" data-clipboard-action="copy" data-clipboard-target="#fapi-member-token" class="copy-to-clipboard" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAABmJLR0QA/wD/AP+gvaeTAAABHElEQVRoge3YQQrCMBCF4b/iFSriRd16xiKKp9GVNLWt0sxLMsg8KK46k89MAy1o8xRdd+AoXtumqCDNMe9FKGo0xSghQ/LbG2tmL0JRo2fEVN8ZJQQaYs5oIdAQczHev7SrKWb1mVEdlzc0/9baeP7EKM/+B3AqBPnEzMZMffZbMb/Ws4pRQu5LDTJrfcvimCkhR+yYnJEe0hstSWscgCv5Y5b7fMohYMdk9S4BgXqY4hCog6kCgfKYahAoi6kKgXKYIpAt183Yc9K7Y0R0xmI5sfSc9N4LCsH2BVknYJadumCrBMRbAuItAfGWgHhLQLwlIN4SEG8JiLeo3hCtMb8xxo6IY/4I8Tc7EhBvCYi3pKeW/OtfzfzNjrwAb3YJtHLIRU8AAAAASUVORK5CYII=" title="Zkopírovat" alt="Zkopírovat"/>
    </p>
</div>
<?php echo FapiMemberTools::help() ?>
</div>
