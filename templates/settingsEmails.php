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
                       $emails = [
                               'afterRegistration' => 'Nastavení emailu po registraci do úrovně',
                               'afterMembershipProlonged' => 'Nastavení emailu při prodloužení členství v úrovni',
                               'afterAdding' => 'Nastavení emailu při přidání do členské úrovně',
                       ];
                       foreach ($emails as $key => $title) {
                           ?>
                           <div class="oneEmail">
                               <div class="header">
                                   <h3><?= $title ?></h3>
                                   <span class="carret"></span>
                               </div>
                               <div class="body">
                                   <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                                       <input type="hidden" name="action" value="fapi_member_edit_email">
                                       <input type="hidden" name="level_id" value="">
                                       <input type="hidden" name="fapi_member_edit_email_nonce"
                                              value="<?php echo wp_create_nonce('fapi_member_edit_email_nonce') ?>">
                                       <input type="hidden" name="email_type" value="<?= $key ?>">
                                       <div class="row">
                                           <label for="mail_subject">Předmět e-mailu</label>
                                           <input type="text" name="mail_subject" id="mail_subject">
                                       </div>
                                       <div class="row">
                                           <label for="mail_body">Obsah e-mailu</label>
                                           <textarea name="mail_body" id="mail_body"></textarea>
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