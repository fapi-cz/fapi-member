<?php include(__DIR__ . '/functions.php') ?>

<div class="baseGrid">
    <?= h1() ?>
    <?= nav($subpage, $areApiCredentialsSet) ?>
    <?= submenu($subpage) ?>

    <div class="page smallerPadding">
        <h3>Přehled členských sekcí a úrovní</h3>
        <?php echo showErrors(); ?>
        <div class="sectionsOverview">
            <?php
                $levels =  get_terms(
                    [
                        'taxonomy' => 'fapi_levels',
                        'hide_empty' => false
                    ]
                );

                $topLevels = array_filter($levels, function($l) {
                    return $l->parent === 0;
                });
                $levelCount = array_reduce($levels, function($carry, $one) {
                    $carry[$one->parent] = (isset($carry[$one->parent])) ? $carry[$one->parent] + 1 : 1;
                    return $carry;
                }, []);

                foreach ($topLevels as $level) {
            ?>
                    <div>
                        <div class="name"><?= $level->name ?></div>
                        <div class="levelCount">Počet úrovní: <?= (isset($levelCount[$level->term_id])) ? $levelCount[$level->term_id] : 0 ?></div>
                        <div class="membersCount">Počet registrovaných: TODO</div>
                        <div class="pagesCount">Stránek v celé sekci: TODO</div>
                    </div>
            <?php } ?>
        </div>
    </div>
    <?= help() ?>
</div>