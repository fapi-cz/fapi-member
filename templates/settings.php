<?php include(__DIR__ . '/functions.php') ?>

<div class="baseGrid">
    <?= h1() ?>
    <?= nav($subpage, $areApiCredentialsSet) ?>
    <?= submenu($subpage) ?>

    <div class="page">
        <h3>Propojení s Vaším účtem FAPI</h3>
        <?php echo showErrors(); ?>
        SETTINGS
    </div>
    <?= help() ?>
</div>