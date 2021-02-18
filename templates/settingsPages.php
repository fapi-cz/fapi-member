<?php
include(__DIR__ . '/functions.php');

echo heading();
?>

    <div class="page both">
        <div class="withSections">
            <div class="a">
                <h3>Struktura uzavřených sekcí a úrovní</h3>
                <?php echo showErrors(); ?>
                <?= levelsSelectionNonJs() ?>
            </div>
            <div class="b">
                <div>
                   <?php
                   $level = (isset($_GET['level'])) ? $_GET['level'] : null;
                   if ($level === null) {
                       echo '<p>Zvolte prosím sekci/úroveň vlevo.</p>';
                   } else {
                       global $FapiPlugin;
                       $fapiLevels = $FapiPlugin->levels();
                       $levelTerm = $fapiLevels->loadById($level);
                       $isSection = ($levelTerm->parent === 0) ? true : false;


                       $templates = $fapiLevels->loadEmailTemplatesForLevel($level);

                       $pages = [
                           'afterLogin' => [
                               't' => 'Stránka po přihlášení',
                               'd' => 'Nastavte stránku, která se zobrazí uživatelům po přihlášení do členské 
                                        sekce nebo úrovně.<br>Stránka nesmí být zařazena jako členská.',
                           ],
                           'noAccess'   => [
                               't' => 'Stránka, když uživatel nemá přístup',
                               'd' => 'Nastavte stránku, která se zobrazí uživateli, pokud nemá přístup na uzamčenou stránku.<br>
                                        Stránka se většinou využívá pro výzvu ke koupi nebo prodloužení členství.',
                           ],
                           'login'      => [
                               't' => 'Přihlašovací stránka (nepovinné)',
                               'd' => 'Zvolte stránku, kde budete mít umístěný přihlašovací formulář. 
                                        Toto pole nijak neovlivní funkčnost členské sekce. Slouží především pro Váš přehled.',
                           ],
                       ];

                       $currentOtherPages = $fapiLevels->loadOtherPagesForLevel($level);

                       foreach ($pages as $key => $setting) {
                           $currentPageId = isset($currentOtherPages[$key]) ? $currentOtherPages[$key] : null;
                           ?>
                           <div class="onePageOther">
                               <h3><?= $setting['t'] ?></h3>
                               <p><?= $setting['d'] ?></p>

                               <?= formStart('set_other_page') ?>
                                   <input type="hidden" name="level_id" value="<?= $level ?>">
                                   <input type="hidden" name="page_type" value="<?= $key ?>">
                                   <div class="row submitInline">
                                       <label for="page">Vyberte stránku</label>
                                       <select type="text" name="page" id="page">
                                           <option value="">-- nevybírat</option>
                                           <?= allPagesAsOptions($currentPageId) ?>
                                       </select>
                                       <input type="submit" class="primary" value="Uložit">
                                   </div>
                               </form>
                           </div>
                           <?php
                           }
                       }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>