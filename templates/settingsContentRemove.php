<?php
include(__DIR__ . '/functions.php');

echo heading();
?>

<script id="LevelToPage" type="application/json"><?= levelToPageJson() ?></script>


    <div class="page both">
        <div class="withSections">
            <div class="a">
                <h3>Struktura uzavřených sekcí a úrovní</h3>
                <?php echo showErrors(); ?>
                <?= levelsSelection() ?>
            </div>
            <div class="b">
                <div class="subsubmenu">
                    <?= subSubmenuItem('settingsContentRemove', 'Obsah sekce/Odebírání stránek', $subpage) ?>
                    <?= subSubmenuItem('settingsContentAdd', 'Přiřazení stránek', $subpage) ?>
                </div>
                <?= formStart('remove_pages', ['removePagesForm', 'pages']) ?>
                    <input type="hidden" name="level_id" value="">
                    <div class="inner">
                        <p>Prosím zvolte sekci/úroveň.</p>
                    </div>
                    <div class="row controls"><button class="btn danger outline">Odstranit vybrané</button></div>
                </form>
            </div>
        </div>
    </div>
</div>