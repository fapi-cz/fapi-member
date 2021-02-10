<?php include(__DIR__ . '/functions.php') ?>

<div class="baseGrid">
    <?= h1() ?>
    <?= nav($subpage, $areApiCredentialsSet) ?>
    <?= submenu($subpage) ?>

    <?php
    $topic = (isset($_GET['topic'])) ? $_GET['topic'] : null;
    $path = ($topic) ? sprintf('%s/help/%s.php', __DIR__, $topic) : null;
    if ($path && file_exists($path)) {
        include $path;
    } else {
        include __DIR__ . '/help/_none.php';
    } ?>
    <?= help() ?>
</div>