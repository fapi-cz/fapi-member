<?php

global $FapiPlugin;

use FapiMember\FapiMemberPlugin;
use FapiMember\FapiMemberTools;

echo FapiMemberTools::heading();
?>
<div class="page">
<?php echo FapiMemberTools::showErrors(); ?>
    <h3><?php echo __( 'Propojené účty FAPI:', 'fapi-member' ); ?></h3>
    <?php echo FapiMemberTools::formStart('api_credentials_remove') ?>
        <table class="wp-list-table widefat fixed striped table-view-list">
            <tr>
                <th><?php _e( 'Uživatelské jméno (e-mail)', 'fapi-member' ); ?></th>
                <th><?php _e( 'API klíč', 'fapi-member' ); ?></th>
                <th class="disconnectColumn"></th>
            </tr>
            <?php
            $accounts = $FapiPlugin->getFapiClients()->getFapiApis();
            if ( empty( $accounts ) || $accounts[0]->getApiKey() === null ){
                echo '<tr>
                        <td></td>
                        <td>'.__( 'Nejsou propojeny žádné účty', 'fapi-member' ).'</td>
                        <td></td>
                    </tr>';
            } else {
                foreach ($accounts as $account){
                    echo '<tr>
                            <td>'.$account->getApiUser().'</td>
                            <td>'.$account->getApiKey().'</td>
                            <td>'.
                                '<button class ="btn outline" name="fapiRemoveCredentials" type="submit" value='.$account->getApiKey().'>'.
                                    __( 'Smazat propojení s FAPI' , 'fapi-member' );'.
                                </button>
                            </td>
                        </tr>';
                }
            }
            ?>
        </table>
    </form>
	<?php echo FapiMemberTools::formStart('api_credentials_submit') ;?>
    <h3><?php echo __( 'Propojit účet FAPI (max. '. $FapiPlugin::CONNECTED_API_KEYS_LIMIT.')', 'fapi-member' ); ?></h3>
    <div class="row">
        <label for="fapiMemberApiEmail"><?php echo __( 'Uživatelské jméno (e-mail)', 'fapi-member' ); ?></label>
        <input type="text" name="fapiMemberApiEmail" id="fapiMemberApiEmail" placeholder="me@example.com">
    </div>
    <div class="row">
        <label for="fapiMemberApiKey"><?php echo __( 'API klíč', 'fapi-member' ); ?></label>
        <input type="text" name="fapiMemberApiKey" id="fapiMemberApiKey">
    </div>
    <div class="row controls">
        <input type="submit" class="primary" name="" id="" value="<?php echo __( 'Propojit s FAPI', 'fapi-member' ); ?>">
    </div>
    </form>
    <p>
		<?php echo __( 'Stav propojení', 'fapi-member' ); ?>:
		<?php echo ($FapiPlugin->areApiCredentialsSet()) ? '<span class="ok">' . __( 'propojeno', 'fapi') . '</span>' : '<span class="ng">' . __('nepropojeno', 'fapi-member' ) . '</span>' ?>
    </p>

    <h3><?php echo __( 'Propojení s Integromatem', 'fapi-member' ); ?></h3>

    <p>
		<?php echo __( 'FAPI Member API Token', 'fapi-member' ); ?>: <span
                id="fapi-member-token"><?php echo get_option(FapiMemberPlugin::OPTION_KEY_TOKEN, '') ?></span>
        <img height="17" data-clipboard-action="copy" data-clipboard-target="#fapi-member-token"
             class="copy-to-clipboard"
             src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAABmJLR0QA/wD/AP+gvaeTAAABHElEQVRoge3YQQrCMBCF4b/iFSriRd16xiKKp9GVNLWt0sxLMsg8KK46k89MAy1o8xRdd+AoXtumqCDNMe9FKGo0xSghQ/LbG2tmL0JRo2fEVN8ZJQQaYs5oIdAQczHev7SrKWb1mVEdlzc0/9baeP7EKM/+B3AqBPnEzMZMffZbMb/Ws4pRQu5LDTJrfcvimCkhR+yYnJEe0hstSWscgCv5Y5b7fMohYMdk9S4BgXqY4hCog6kCgfKYahAoi6kKgXKYIpAt183Yc9K7Y0R0xmI5sfSc9N4LCsH2BVknYJadumCrBMRbAuItAfGWgHhLQLwlIN4SEG8JiLeo3hCtMb8xxo6IY/4I8Tc7EhBvCYi3pKeW/OtfzfzNjrwAb3YJtHLIRU8AAAAASUVORK5CYII="
             title="<?php echo __( 'Zkopírovat', 'fapi'); ?>" alt="<?php echo __('Zkopírovat', 'fapi-member' ); ?>"/>
    </p>
</div>
<?php echo FapiMemberTools::help() ?>
</div>
