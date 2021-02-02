<?php include(__DIR__ . '/functions.php') ?>

<div class="baseGrid">
    <?= h1() ?>
    <?= nav($subpage, $areApiCredentialsSet) ?>
    <?= submenu($subpage) ?>

    <div class="page both">
        <div class="withSections">
            <div class="a">
                <h3>Struktura uzavřených sekcí a úrovní</h3>
                <?= levels() ?>
            </div>
            <div class="b">
                <div class="subsubmenu">
                    <?= submenuItem('settingsSectionNew', 'Vytvořit novou sekci', $subpage) ?>
                    <?= submenuItem('settingsLevelNew', 'Vytvořit novou úroveň', $subpage) ?>
                </div>
                <?php echo showErrors(); ?>
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="fapi_member_new_section">
                    <input type="hidden" name="fapi_member_new_section_nonce" value="<?php echo wp_create_nonce('fapi_member_new_section_nonce') ?>">
                    <div class="row">
                        <label for="fapiMemberSectionName">Název členské sekce</label>
                        <input type="text" name="fapiMemberSectionName" id="fapiMemberSectionName" placeholder=""
                               value="">
                    </div>
                    <div class="row controls">
                        <input type="submit" class="primary" name="" id="" value="Vytvořit sekci">
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>