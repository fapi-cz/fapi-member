<?php

use FapiMember\FapiMemberTools;

echo FapiMemberTools::heading();
?>

<div class="page both">
    <div class="withSections">
        <div class="a">
            <h3>Členské sekce/úrovně</h3>
			<?php echo FapiMemberTools::showErrors(); ?>
			<?php echo FapiMemberTools::levelsSelectionNonJs() ?>
        </div>
        <div class="b">
            <div>
				<?php
				$level = (isset($_GET['level'])) ? FapiMemberTools::sanitizeLevelId($_GET['level']) : null;
				if ($level === null) {
					echo '<p>Zvolte prosím sekci/úroveň vlevo.</p>';
				} else {
					global $FapiPlugin;
					$fapiLevels = $FapiPlugin->levels();
					$levelTerm = $fapiLevels->loadById($level);
					$isSection = ($levelTerm->parent === 0) ? true : false;

					$templates = $fapiLevels->loadEmailTemplatesForLevel($level);

					if ($isSection) {
						$emails = [
							'afterRegistration' => 'E-mail po registraci do sekce',
							'afterMembershipProlonged' => 'E-mail po prodloužení členství v sekci',
							'afterAdding' => 'E-mail po přidání do sekce',
						];
					} else {
						$emails = [
							'afterRegistration' => 'E-mail po registraci do úrovně',
							'afterMembershipProlonged' => 'E-mail po prodloužení členství v úrovni',
							'afterAdding' => 'E-mail po přidání do úrovně',
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
                                        E-mail je převzat z nastavení členské sekce, do které úroveň spadá.
                                    </p>
									<?php
								}
								?>
								<?php echo FapiMemberTools::formStart('edit_email') ?>
                                <input type="hidden" name="level_id" value="<?php echo $level ?>">
                                <input type="hidden" name="email_type" value="<?php echo $key ?>">
								<?php if (!$isSection) { ?>
                                    <div class="row">
                                        <label for="SpecifyLevelEmails[<?php echo $key ?>]">
                                            <input type="checkbox" id="SpecifyLevelEmails[<?php echo $key ?>]"
                                                   class="specifyLevelEmailCheckbox"
												<?php echo ($hasContentSet) ? 'checked' : '' ?>
                                            >
                                            Nastavit vlastní e-mail pro úroveň
                                        </label>
                                    </div>
								<?php } ?>
                                <div class="inputs <?php echo ($hasContentSet || $isSection) ? '' : 'collapsed' ?>">
                                    <div class="row">
                                        <label for="mail_subject">Předmět e-mailu</label>
                                        <input type="text" name="mail_subject" id="mail_subject"
											<?php echo ($emailIsCascaded) ? 'readonly' : '' ?>
											<?php echo ($hasContentSet) ? sprintf('value="%s"',
												htmlspecialchars($templates[$key]['s'])) : '' ?>
                                        >
                                    </div>
                                    <div class="row">
                                        <label for="mail_body">Obsah e-mailu</label>
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
            <h3>Dostupné proměnné <span class="carret"></span></h3>
            <div class="tableBox">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                    <tr>
                        <th rowspan="2" style="width: 200px">Kód</th>
                        <th rowspan="2">Popis</th>
                        <th rowspan="2">Příklad</th>
                        <th colspan="3" style="width:300px">Dostupné při</th>
                    </tr>
                    <tr>
                        <th>registraci nového člena</th>
                        <th>prodloužení/
                            <wbr>
                            přidání sekce
                        </th>
                        <th>prodloužení/
                            <wbr>
                            přidání úrovně
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><code>%%SEKCE%%</code></td>
                        <td>Název sekce</td>
                        <td>Italská kuchyně</td>
                        <td></td>
                        <td>&checkmark;</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><code>%%UROVEN%%</code></td>
                        <td>Název úrovně</td>
                        <td>Začátečník</td>
                        <td></td>
                        <td></td>
                        <td>&checkmark;</td>
                    </tr>
                    <tr>
                        <td><code>%%DNI%%</code></td>
                        <td>Počet zakoupených dní nebo 'neomezeně'</td>
                        <td>31</td>
                        <td></td>
                        <td>&checkmark;</td>
                        <td>&checkmark;</td>
                    </tr>
                    <tr>
                        <td><code>%%CLENSTVI_DO%%</code></td>
                        <td>Datum konce členství nebo 'neomezené'</td>
                        <td>12. 1. 2022</td>
                        <td></td>
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
