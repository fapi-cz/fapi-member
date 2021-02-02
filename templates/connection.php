<?php include(__DIR__ . '/functions.php') ?>

<div class="baseGrid">
    <?= h1() ?>
    <?= nav($subpage, $areApiCredentialsSet) ?>
    <?= submenu($subpage) ?>
    <div class="page">
        <h3>Propojení s Vaším účtem FAPI</h3>
        <?php echo showErrors(); ?>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="fapi_member_api_credentials_submit">
            <input type="hidden" name="fapi_member_api_credentials_submit_nonce" value="<?php echo wp_create_nonce('fapi_member_api_credentials_submit_nonce') ?>">
            <div class="row">
                <label for="fapiMemberApiEmail">Uživatelské jméno (e-mail)</label>
                <input type="text" name="fapiMemberApiEmail" id="fapiMemberApiEmail" placeholder="me@example.com"
                       value="<?php echo get_option('fapiMemberApiEmail', '') ?>">
            </div>
            <div class="row">
                <label for="fapiMemberApiKey">API klíč</label>
                <input type="text" name="fapiMemberApiKey" id="fapiMemberApiKey" placeholder=""
                       value="<?php echo get_option('fapiMemberApiKey', '') ?>">
            </div>
            <div class="row controls">
                <input type="submit" class="primary" name="" id="" value="Propojit s FAPI">
            </div>
        </form>
        <p>
            Stav propojení: <span class="ok">propojeno</span>.
        </p>
    </div>
    <?= help() ?>
</div>