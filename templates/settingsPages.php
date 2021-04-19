<?php

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
				$level = ( isset( $_GET['level'] ) ) ? FapiMemberTools::sanitizeLevelId( $_GET['level'] ) : null;
				if ( $level === null ) {
					echo '<p>Zvolte prosím sekci/úroveň vlevo.</p>';
				} else {
					global $FapiPlugin;
					$fapiLevels = $FapiPlugin->levels();
					$levelTerm  = $fapiLevels->loadById( $level );
					$isSection  = ( $levelTerm->parent === 0 ) ? true : false;


					$templates = $fapiLevels->loadEmailTemplatesForLevel( $level );

					$pages = [
						'afterLogin' => [
							't' => 'Stránka po přihlášení',
							'd' => 'Vyberte stránku, která se zobrazí uživatelům po přihlášení do členské 
                                        sekce nebo úrovně.',
						],
						'noAccess'   => [
							't' => 'Stránka, když uživatel nemá přístup',
							'd' => 'Vyberte stránku, která se zobrazí uživateli, pokud nemá přístup na uzamčenou stránku.<br>
                                        Stránka se většinou využívá pro výzvu ke koupi nebo prodloužení členství.',
						],
						'login'      => [
							't' => 'Přihlašovací stránka',
							'd' => 'Vyberte stránku, kde je umístěn přihlašovací formulář. 
                                        Toto nastavení nemá vliv na funkčnost členské sekce, slouží pouze pro váš přehled.
                                        <br>Stránka nesmí být zařazena jako členská.',
						],
					];

					$currentOtherPages = $fapiLevels->loadOtherPagesForLevel( $level );

					foreach ( $pages as $key => $setting ) {
						$currentPageId = isset( $currentOtherPages[ $key ] ) ? $currentOtherPages[ $key ] : null;
						?>
                        <div class="onePageOther">
                            <h3><?php echo $setting['t'] ?></h3>
                            <p><?php echo $setting['d'] ?></p>

							<?php echo FapiMemberTools::formStart( 'set_other_page' ) ?>
                            <input type="hidden" name="level_id" value="<?php echo $level ?>">
                            <input type="hidden" name="page_type" value="<?php echo $key ?>">
                            <div class="row submitInline noLabel">
                                <select type="text" name="page" id="page">
                                    <option value="">-- nevybrána --</option>
									<?php echo FapiMemberTools::allPagesAsOptions( $currentPageId ) ?>
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