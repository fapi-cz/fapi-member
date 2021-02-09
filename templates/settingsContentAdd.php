<?php include(__DIR__ . '/functions.php') ?>

<script id="LevelToPage" type="application/json"><?= levelToPageJson() ?></script>
<div class="baseGrid">
    <?= h1() ?>
    <?= nav($subpage, $areApiCredentialsSet) ?>
    <?= submenu($subpage) ?>

    <div class="page both">
        <div class="withSections">
            <div class="a">
                <h3>Struktura uzavřených sekcí a úrovní</h3>
                <?php echo showErrors(); ?>
                <?= levelsSelection($subpage) ?>
            </div>
            <div class="b">
                <div class="subsubmenu">
                    <?= submenuItem('settingsContentRemove', 'Obsah sekce/Odebírání stránek', $subpage) ?>
                    <?= submenuItem('settingsContentAdd', 'Přiřazení stránek', $subpage) ?>
                </div>
                <div>
                    <form class="addPagesForm pages" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                        <input type="hidden" name="action" value="fapi_member_add_pages">
                        <input type="hidden" name="level_id" value="">
                        <input type="hidden" name="fapi_member_add_pages_nonce" value="<?php echo wp_create_nonce('fapi_member_add_pages_nonce') ?>">
                        <div class="inner">
                            <?= allPagesForForm() ?>
                        </div>
                        <div class="row controls">
                            <button class="btn primary">Přiřadit vybrané</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>