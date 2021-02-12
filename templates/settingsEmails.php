<?php include(__DIR__ . '/functions.php') ?>

<div class="baseGrid">
    <?= h1() ?>
    <?= nav($subpage, $areApiCredentialsSet) ?>
    <?= submenu($subpage) ?>

    <div class="page both">
        <div class="withSections">
            <div class="a">
                <h3>Struktura uzavřených sekcí a úrovní</h3>
                <?php echo showErrors(); ?>
                <?= levelsSelectionNonJs($subpage) ?>
            </div>
            <div class="b">
                <div>
                   <?php
                   $level = (isset($_GET['level'])) ? $_GET['level'] : null;
                   if ($level === null) {
                       echo '<p>Zvolte prosím sekci/úroveň vlevo.</p>';
                   } else {
                       global $fapiLevels;
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
                                   <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                                       <input type="hidden" name="action" value="fapi_member_edit_email">
                                       <input type="hidden" name="level_id" value="<?= $level ?>">
                                       <input type="hidden" name="fapi_member_edit_email_nonce"
                                              value="<?php echo wp_create_nonce('fapi_member_edit_email_nonce') ?>">
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
        </div>
    </div>
</div>