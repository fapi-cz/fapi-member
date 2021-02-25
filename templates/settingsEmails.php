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

                       $emails = [
                               'afterRegistration' => 'Nastavení emailu po registraci do úrovně',
                               'afterMembershipProlonged' => 'Nastavení emailu při prodloužení členství v úrovni',
                               'afterAdding' => 'Nastavení emailu při přidání do členské úrovně',
                       ];
                       foreach ($emails as $key => $title) {
                           $hasContentSet = (isset($templates[$key])) ? true : false;
                           $emailIsCascaded = ($levelTerm->parent !== 0 && !$hasContentSet) ? true : false;
                           ?>
                           <div class="oneEmail">
                               <div class="header">
                                   <h3><?= $title ?></h3>
                                   <span class="carret"></span>
                               </div>
                               <div class="body">
                                   <?php if ($emailIsCascaded) {
                                        ?>
                                       <p>
                                            E-mail je převzat z nastavení členské sekce, do které úroveň spadá.
                                        </p>
                                       <?php
                                   }
                                   ?>
                                   <?= formStart('edit_email') ?>
                                       <input type="hidden" name="level_id" value="<?= $level ?>">
                                       <input type="hidden" name="email_type" value="<?= $key ?>">
                                       <?php if (!$isSection) { ?>
                                       <div class="row">
                                            <label for="SpecifyLevelEmails[<?= $key ?>]">
                                                <input type="checkbox" id="SpecifyLevelEmails[<?= $key ?>]" class="specifyLevelEmailCheckbox"
                                                    <?= ($hasContentSet) ? 'checked' : '' ?>
                                                >
                                                Nastavit vlastní e-mail pro úroveň
                                            </label>
                                        </div>
                                        <?php } ?>
                                       <div class="row">
                                           <label for="mail_subject">Předmět e-mailu</label>
                                           <input type="text" name="mail_subject" id="mail_subject"
                                            <?= ($emailIsCascaded) ? 'readonly':'' ?>
                                            <?= ($hasContentSet) ? sprintf('value="%s"', $templates[$key]['s']) : '' ?>
                                           >
                                       </div>
                                       <div class="row">
                                           <label for="mail_body">Obsah e-mailu</label>
                                           <textarea name="mail_body" id="mail_body" <?= ($emailIsCascaded) ? 'readonly':'' ?>><?= ($hasContentSet) ? $templates[$key]['b'] : '' ?></textarea>
                                       </div>
                                       <div class="row controls">
                                           <input type="submit" class="primary" value="Uložit">
                                       </div>
                                   </form>
                               </div>
                           </div>
                           <?php
                            }
                       }

                    ?>

                </div>

            </div>
            <div class="shortcodes">
                <h3>Dostupné proměnné <span class="carret"></span></h3>
                <div class="tableBox">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th rowspan="2" style="width: 200px">Kód</th>
                                <th rowspan="2">Popis</th>
                                <th rowspan="2">Příklad</th>
                                <th colspan="3" style="width:300px">Dostupné pro</th>
                            </tr>
                            <tr>
                                <th>registrace</th>
                                <th>prodloužení</th>
                                <th>přidání úrovně</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>%%SEKCE%%</code></td>
                                <td>Název sekce</td>
                                <td>Italská kuchyně</td>
                                <td>&checkmark;</td>
                                <td>&checkmark;</td>
                                <td>&checkmark;</td>
                            </tr>
                            <tr>
                                <td><code>%%UROVEN%%</code></td>
                                <td>Název úrovně</td>
                                <td>Začátečník</td>
                                <td>&checkmark;</td>
                                <td>&checkmark;</td>
                                <td>&checkmark;</td>
                            </tr>
                            <tr>
                                <td><code>%%DNI%%</code></td>
                                <td>Počet zakoupených dní</td>
                                <td>31</td>
                                <td>&checkmark;</td>
                                <td>&checkmark;</td>
                                <td>&checkmark;</td>
                            </tr>
                            <tr>
                                <td><code>%%CLENSTVI_DO%%</code></td>
                                <td>Datum konce členství</td>
                                <td>12. 1. 2022</td>
                                <td>&checkmark;</td>
                                <td>&checkmark;</td>
                                <td>&checkmark;</td>
                            </tr>
                            <tr>
                                <td><code>%%PRIHLASENI_ODKAZ_ZDE%%</code></td>
                                <td>Odkaz na přihlášení (z nastavení) s&nbsp;textem &bdquo;zde&ldquo;</td>
                                <td>zde</td>
                                <td>&checkmark;</td>
                                <td>&checkmark;</td>
                                <td>&checkmark;</td>
                            </tr>
                            <tr>
                                <td><code>%%PRIHLASENI_ODKAZ%%</code></td>
                                <td>Odkaz na přihlášení (z nastavení) pouze URL</td>
                                <td>https://www.example.com/login</td>
                                <td>&checkmark;</td>
                                <td>&checkmark;</td>
                                <td>&checkmark;</td>
                            </tr>
                            <tr>
                                <td><code>%%PRIHLASOVACI_JMENO%%</code></td>
                                <td>Přihlašovací jméno uživatele</td>
                                <td>jan@example.com</td>
                                <td>&checkmark;</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td><code>%%HESLO%%</code></td>
                                <td>Přihlašovací heslo uživatele</td>
                                <td>)7PQll6Pw)HN7%w8ddES!ues</td>
                                <td>&checkmark;</td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>