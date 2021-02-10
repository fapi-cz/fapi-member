<?php
include(__DIR__ . '/functions.php');
global $fapiLevels;
?>



<div class="baseGrid">
    <?= h1() ?>
    <?= nav($subpage, $areApiCredentialsSet) ?>
    <?= submenu($subpage) ?>

    <div class="page smallerPadding">
        <h3>Přehled členských sekcí a úrovní</h3>
        <?php echo showErrors(); ?>
        <div class="sectionsOverview">
            <?php
                $levels =  $fapiLevels->loadAsTerms();

                $topLevels = array_filter($levels, function($l) {
                    return $l->parent === 0;
                });
                $pagesCount = [];
                $levelsToPages = $fapiLevels->levelsToPages();
                $levelCount = array_reduce($levels, function($carry, $one) use (&$pagesCount, $levelsToPages) {
                    $carry[$one->parent] = (isset($carry[$one->parent])) ? $carry[$one->parent] + 1 : 1;
                    if ($one->parent === 0) {
                        $pagesCount[$one->term_id] = isset($pagesCount[$one->term_id]) ? $pagesCount[$one->term_id] + count($levelsToPages[$one->term_id]) : count($levelsToPages[$one->term_id]);
                    } else {
                        $pagesCount[$one->parent] = isset($pagesCount[$one->parent]) ? $pagesCount[$one->parent] + count($levelsToPages[$one->term_id]) : count($levelsToPages[$one->term_id]);
                    }
                    return $carry;
                }, []);

                $empty = true;

                foreach ($topLevels as $level) {
                    $empty = false;
                    ?>
                        <div>
                            <div class="name"><?= $level->name ?></div>
                            <div class="levelCount">Počet úrovní: <?= (isset($levelCount[$level->term_id])) ? $levelCount[$level->term_id] : 0 ?></div>
                            <div class="membersCount">Počet registrovaných: TODO</div>
                            <div class="pagesCount">Stránek v celé sekci: <?= (isset($pagesCount[$level->term_id])) ? $pagesCount[$level->term_id] : 0 ?></div>
                        </div>
                    <?php } ?>

        </div>
        <?php if ($empty) { ?>

            <div class="emptyIndex">
                <img src="<?= plugin_dir_url(__FILE__) . '../media/membership.svg' ?>">
                <p class="gray">
                    Nemáte vytvořenou žádnou členskou sekci.<br>
                    Novou sekci můžete vytvořit na záložce Sekce / úrovně.
                </p>
                <a href="<?= fapilink('settingsSectionNew') ?>" class="btn primary">Přejít do záložky Sekce / úrovně</a>
            </div>

        <?php } ?>
    </div>
    <?= help() ?>
</div>