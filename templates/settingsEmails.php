<?php

use FapiMember\Deprecated\FapiMemberTools;
use FapiMember\Utils\AlertProvider;

echo FapiMemberTools::heading();
?>

<div class="page both">
    <div class="withSections">
        <div class="a">
            <h3><?php echo __( 'Členské sekce/úrovně', 'fapi-member' ); ?></h3>
			<?php echo AlertProvider::showErrors(); ?>
			<?php echo FapiMemberTools::levelsSelectionNonJs() ?>
        </div>
        <div class="b">
            <div>
				<?php
				$level = (isset($_GET['level'])) ? FapiMemberTools::sanitizeLevelId($_GET['level']) : null;
				if ($level === null) {
					echo '<p>' . __( 'Zvolte prosím sekci/úroveň vlevo.', 'fapi-member' ) . '</p>';
				} else {
					global $FapiPlugin;
					$fapiLevels = $FapiPlugin->levels();
					$levelTerm = $fapiLevels->loadById($level);
					$isSection = ($levelTerm->parent === 0) ? true : false;

					$templates = $fapiLevels->loadEmailTemplatesForLevel($level);

					if ($isSection) {
						$emails = [
							'afterRegistration' => __( 'E-mail po registraci do sekce', 'fapi-member' ),
							'afterMembershipProlonged' => __( 'E-mail po prodloužení členství v sekci', 'fapi-member' ),
							'afterAdding' => __( 'E-mail po přidání do sekce', 'fapi-member' ),
						];
					} else {
						$emails = [
							'afterRegistration' => __( 'E-mail po registraci do úrovně', 'fapi-member' ),
							'afterMembershipProlonged' => __( 'E-mail po prodloužení členství v úrovni', 'fapi-member' ),
							'afterAdding' => __( 'E-mail po přidání do úrovně', 'fapi-member' ),
						];
					}

					foreach ($emails as $key => $title) {
						$hasContentSet = isset($templates[$key]);
						$emailIsCascaded = $levelTerm->parent !== 0 && !$hasContentSet;
						?>
                        <div class="oneEmail">
                            <div class="header">
                                <h3><?php echo $title ?></h3>
                                <span class="carret"></span>
                            </div>
                            <div class="body">
								<?php if ($emailIsCascaded) {
									?>
                                    <p>
										<?php echo __( 'E-mail je převzat z nastavení členské sekce, do které úroveň spadá.', 'fapi-member' ); ?>
                                    </p>
									<?php
								}
								?>
								<?php echo FapiMemberTools::formStart('edit_email') ?>
                                <input type="hidden" name="level_id" value="<?php echo $level ?>">
                                <input type="hidden" name="email_type" value="<?php echo $key ?>">
                                <?php if ($isSection) { ?>
                                    <input type="checkbox" style="display:none" name="specify_level_emails" id="specify_level_emails[<?php echo $key ?>]"
                                        <?php echo 'checked'?>
                                    >
								<?php } ?>
								<?php if (!$isSection) { ?>
                                    <div class="row">
                                        <label for="specify_level_emails[<?php echo $key ?>]">
                                            <input type="checkbox" name="specify_level_emails" id="specify_level_emails[<?php echo $key ?>]"
                                                   class="specifyLevelEmailCheckbox"
												<?php echo ($hasContentSet) ? 'checked' : '' ?>
                                            >
											<?php echo __( 'Nastavit vlastní e-mail pro úroveň', 'fapi-member' ); ?>
                                        </label>
                                    </div>
								<?php } ?>
                                <div class="inputs <?php echo ($hasContentSet || $isSection) ? '' : 'collapsed' ?>">
                                    <div class="row">
                                        <label for="mail_subject"><?php echo __( 'Předmět e-mailu', 'fapi-member' ); ?></label>
                                        <input type="text" name="mail_subject" id="mail_subject"
											<?php echo ($emailIsCascaded) ? 'readonly' : '' ?>
											<?php echo ($hasContentSet) ? sprintf('value="%s"',
												htmlspecialchars($templates[$key]['s'])) : '' ?>
                                        >
                                    </div>
                                    <div class="row">
                                        <label for="mail_body"><?php echo __( 'Obsah e-mailu', 'fapi-member' ); ?></label>
                                        <textarea name="mail_body"
                                                  id="mail_body" <?php echo ($emailIsCascaded) ? 'readonly' : '' ?>><?php echo ($hasContentSet) ? $templates[$key]['b'] : '' ?></textarea>
                                    </div>

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
        <div class="shortcodes open">
            <h3><?php echo __( 'Dostupné proměnné', 'fapi-member' ); ?> <span class="carret"></span></h3>
            <div class="tableBox">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                    <tr>
                        <th rowspan="2" style="width: 200px"><?php echo __( 'Kód', 'fapi-member' ); ?></th>
                        <th rowspan="2"><?php echo __( 'Popis', 'fapi-member' ); ?></th>
                        <th rowspan="2"><? echo __( 'Příklad', 'fapi-member' ); ?></th>
                        <th colspan="3" style="width:300px"><?php echo __( 'Dostupné při', 'fapi-member' ); ?></th>
                    </tr>
                    <tr>
                        <th><?php echo __( 'registraci nového člena', 'fapi-member' ); ?></th>
                        <th><?php echo __( 'prodloužení/přidání sekce', 'fapi-member' ); ?>
                        </th>
                        <th><?php echo __( 'prodloužení/přidání sekce', 'fapi-member' ); ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><code>%%SEKCE%%</code></td>
                        <td><?php echo __( 'Název sekce', 'fapi-member' ); ?></td>
                        <td><?php echo __( 'Italská kuchyně', 'fapi-member' ); ?></td>
                        <td></td>
                        <td>&checkmark;</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><code>%%UROVEN%%</code></td>
                        <td><?php echo __( 'Název úrovně', 'fapi-member' ); ?></td>
                        <td><?php echo __( 'Začátečník', 'fapi-member' ); ?></td>
                        <td></td>
                        <td></td>
                        <td>&checkmark;</td>
                    </tr>
                    <tr>
                        <td><code>%%DNI%%</code></td>
                        <td><?php echo __( "Počet zakoupených dní nebo 'neomezeně'", 'fapi-member' ); ?></td>
                        <td>31</td>
                        <td></td>
                        <td>&checkmark;</td>
                        <td>&checkmark;</td>
                    </tr>
                    <tr>
                        <td><code>%%CLENSTVI_DO%%</code></td>
                        <td><?php echo __( "Datum konce členství nebo 'neomezené'", 'fapi-member' ); ?></td>
                        <td>12. 1. 2022</td>
                        <td></td>
                        <td>&checkmark;</td>
                        <td>&checkmark;</td>
                    </tr>
                    <tr>
                        <td><code>%%PRIHLASENI_ODKAZ%%</code></td>
                        <td><?php echo __( 'Odkaz na přihlášení (z nastavení) pouze URL', 'fapi-member' ); ?></td>
                        <td>https://www.example.com/login</td>
                        <td>&checkmark;</td>
                        <td>&checkmark;</td>
                        <td>&checkmark;</td>
                    </tr>
                    <tr>
                        <td><code>%%PRIHLASOVACI_JMENO%%</code></td>
                        <td><?php echo __( 'Přihlašovací jméno uživatele', 'fapi-member' ); ?></td>
                        <td>jan@example.com</td>
                        <td>&checkmark;</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><code>%%HESLO%%</code></td>
                        <td><?php echo __( 'Přihlašovací heslo uživatele', 'fapi-member' ); ?></td>
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
