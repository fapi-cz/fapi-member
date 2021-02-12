<?php
include(__DIR__ . '/functions.php');

echo heading();
?>
    <div class="page both">
        <div class="withSections">
            <div class="a">
                <h3>Struktura uzavřených sekcí a úrovní</h3>
                <?php echo showErrors(); ?>
                <?= levelsSelection() ?>
            </div>
            <div class="b">
            </div>
        </div>
    </div>
</div>