<?php

function showErrors() {
	$errorMap = [
		'apiFormEmpty'                => [ 'error', 'Je třeba zadat jak uživatelské jméno, tak API klíč.' ],
		'apiFormSuccess'              => [ 'success', 'Údaje pro API uloženy.' ],
		'apiFormError'                => [ 'error', 'Neplatné údaje pro API.' ],
		'sectionNameEmpty'            => [ 'error', 'Název sekce je povinný.' ],
		'levelNameOrParentEmpty'      => [ 'error', 'Název úrovně a výběr sekce je povinný.' ],
		'sectionNotFound'             => [ 'error', 'Sekce nenalezena.' ],
		'removeLevelSuccessful'       => [ 'success', 'Sekce/úroveň smazána.' ],
		'editLevelSuccessful'         => [ 'success', 'Sekce/úroveň upravena.' ],
		'levelIdOrToAddEmpty'         => [ 'error', 'Zvolte prosím úroveň a stránky k přidání.' ],
		'editLevelNoName'             => [ 'error', 'Chyba změny sekce/úrovně.' ],
		'editMailsRemoved'            => [ 'success', 'Šablona emailu byla odebrána.' ],
		'editMailsUpdated'            => [ 'success', 'Šablona emailu byla upravena.' ],
		'editOtherPagesRemoved'       => [ 'success', 'Ostatní stránka byla odebrána.' ],
		'editOtherPagesUpdated'       => [ 'success', 'Ostatní stránka byla nastavena.' ],
		'settingsSettingsUpdated'     => [ 'success', 'Nastavení uložena.' ],
		'settingsSettingsNoValidPage' => [ 'error', 'Stránka nenalezena.' ],
	];

	if ( isset( $_GET['e'] ) && isset( $errorMap[ $_GET['e'] ] ) ) {
		$e = $errorMap[ $_GET['e'] ];

		return sprintf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>', $e[0], $e[1] );
	}
}

function h1() {
	$svg = file_get_contents( __DIR__ . '/../_sources/LOGO_FAPI_svg.svg' );

	return sprintf( '<div class="h1">%s</div>', $svg );
}

function nav() {
	global $FapiPlugin;
	$subpage              = $FapiPlugin->findSubpage();
	$areApiCredentialsSet = $FapiPlugin->areApiCredentialsSet();

	$c = file_get_contents( __DIR__ . '/../_sources/connect.svg' );
	$h = file_get_contents( __DIR__ . '/../_sources/home-solid.svg' );
	$p = file_get_contents( __DIR__ . '/../_sources/padlock.svg' );

	if ( ! $areApiCredentialsSet ) {
		return '
            <nav>
                <span class="disabled">
                    <span class="a">Nástěnka</span>
                    <span class="b">Přehled</span>
                    ' . $h . '
                </span>
                <span href="#" class="disabled">
                    <span class="a">Nastavení</span>
                    <span class="b">Členské sekce</span>
                    ' . $p . '
                </span>
                <a href="#" class="active">
                    <span class="a">Propojení</span>
                    <span class="b">Připojení k FAPI</span>
                    ' . $c . '
                </a>
            </nav>';
	} else {
		return '
            <nav>
                <a href="' . fapilink( 'index' ) . '" ' . ( ( $subpage === 'index' ) ? 'class="active"' : '' ) . '>
                    <span class="a">Nástěnka</span>
                    <span class="b">Přehled</span>
                    ' . $h . '
                </a>
                <a href="' . fapilink( 'settingsSectionNew' ) . '" ' . ( ( strpos( $subpage,
		                                                                           'settings' ) === 0 ) ? 'class="active"' : '' ) . '>
                    <span class="a">Nastavení</span>
                    <span class="b">Členské sekce</span>
                    ' . $p . '
                </span>
                <a href="' . fapilink( 'connection' ) . '" ' . ( ( $subpage === 'connection' ) ? 'class="active"' : '' ) . '>
                    <span class="a">Propojení</span>
                    <span class="b">Připojení k FAPI</span>
                    ' . $c . '
                </a>
            </nav>';
	}
}

function submenu() {
	global $FapiPlugin;
	$subpage = $FapiPlugin->findSubpage();

	switch ( true ) {
		case ( $subpage === 'index' ):
			return '
                <div class="submenu">
                    <span class="active">Přehled</a>
                </div>
                ';
		case ( $subpage === 'connection' ):
			return '
                <div class="submenu">
                    <span class="active">Propojení</a>
                </div>
                ';
		case ( mb_strpos( $subpage, 'settings' ) === 0 ):
			return '
                <div class="submenu">
                    ' . submenuItem( 'settingsSectionNew', 'Sekce / úrovně', $subpage, [ 'settingsLevelNew' ] ) . '
                    ' . submenuItem( 'settingsContentAdd',
			                         'Přiřazené stránky',
			                         $subpage,
			                         [ 'settingsContentRemove' ] ) . '
                    ' . submenuItem( 'settingsEmails', 'Nastavení e-mailů', $subpage ) . '
                    ' . submenuItem( 'settingsPages', 'Ostatní stránky', $subpage ) . '
                    ' . submenuItem( 'settingsElements', 'Prvky pro web', $subpage ) . '
                    ' . submenuItem( 'settingsSettings', 'Nastavení', $subpage ) . '
                </div>
                ';
	}
}

function submenuItem( $subpage, $label, $activeSubpage, $otherChildren = null ) {
	$classes = [];

	if ( $activeSubpage === $subpage ) {
		$classes[] = 'active';
	}
	if ( $otherChildren !== null && in_array( $activeSubpage, $otherChildren ) ) {
		$classes[] = 'active';
	}

	return sprintf( '<a href="%s" class="%s">%s</a>', fapilink( $subpage ), join( ' ', $classes ), $label );
}

function subSubmenuItem( $subpage, $label, $activeSubpage ) {
	$classes = [ 'subsubmenuitem' ];

	if ( $activeSubpage === $subpage ) {
		$classes[] = 'active';
	}

	if ( isset( $_GET['level'] ) ) {
		$tail = sprintf( '&level=%s', $_GET['level'] );
	} else {
		$tail = '';
	}

	return sprintf( '<a href="%s%s" class="%s">%s</a>', fapilink( $subpage ), $tail, join( ' ', $classes ), $label );
}

function help() {
	return '
    <div class="help">
        <h3>Nápověda</h3>
        <div class="inner">
            <div>
                <h4>Jak získám API klíč?</h4>
                <p>Přečtěte si naší nápovědu, kde se dozvíte, jak si můžete vygenerovat API klíč v aplikaci FAPI.</p>
                <a href="https://fapi.cz/plugin/napoveda-1" target="_blank" class="btn outline">Přečíst</a>
            </div>
            <div>
                <h4>Jak vytvořit členskou sekci?</h4>
                <p>Podívejte se na základní kroky v pluginu FAPI Member, abyste si mohli vytvořit svou členskou sekci.</p>
                <a href="https://fapi.cz/plugin/napoveda-2" target="_blank" class="btn outline">Přečíst</a>
            </div>
            <div>
                <h4>Jak vygeneruji přístupy?</h4>
                <p>Podívejte se, jak si nastavit prodejní formulář FAPI, aby dokázal komunikovat s pluginem a vytvářel přístupy vašim klientům.</p>
                <a href="https://fapi.cz/plugin/napoveda-3" target="_blank" class="btn outline">Přečíst</a>
            </div>
        </div>
    </div>
    ';
}

function fapilink( $subpage ) {
	return admin_url( sprintf( '/admin.php?page=fapi-member-options&subpage=%s', $subpage ) );
}

function levels() {
	global $FapiPlugin;
	$t = $FapiPlugin->levels()->loadAsTerms();

	$lis     = [];
	$actions = '<button class="edit"></button><button class="remove"></button>';

	foreach ( $t as $term ) {
		$under = [];
		if ( $term->parent === 0 ) {
			foreach ( $t as $underTerm ) {
				if ( $underTerm->parent === $term->term_id ) {
					$under[] = sprintf( '<li data-id="%s"><span>%s</span>%s</li>',
					                    $underTerm->term_id,
					                    $underTerm->name,
					                    $actions );
				}
			}
			$lis[] = sprintf( '<li data-id="%s"><span>%s</span>%s<ol>%s</ol></li>',
			                  $term->term_id,
			                  $term->name,
			                  $actions,
			                  join( '', $under ) );
		}
	}

	?>
    <div class="levels">
        <ol>
			<?php echo  join( '', $lis ) ?>
        </ol>
    </div>
    <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" id="LevelRemoveForm">
        <input type="hidden" name="action" value="fapi_member_remove_level">
        <input type="hidden" name="fapi_member_remove_level_nonce"
               value="<?php echo wp_create_nonce( 'fapi_member_remove_level_nonce' ) ?>">
        <input type="hidden" name="level_id" value="">
    </form>
    <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" id="LevelEditForm">
        <input type="hidden" name="action" value="fapi_member_edit_level">
        <input type="hidden" name="fapi_member_edit_level_nonce"
               value="<?php echo wp_create_nonce( 'fapi_member_edit_level_nonce' ) ?>">
        <input type="hidden" name="name" value="">
        <input type="hidden" name="level_id" value="">
    </form>
	<?php
}

function oneLevelSelection( $id, $link, $name, $children = '', $highlight = false ) {
	$c  = ( $highlight ) ? 'class="selected"' : '';
	$ch = ( ! empty( $children ) ) ? sprintf( '<ol>%s</ol>', $children ) : '';

	return sprintf(
		'<li data-id="%s" %s><a href="%s">%s</a>%s</li>',
		$id,
		$c,
		$link,
		$name,
		$ch
	);
}

function levelsSelection() {
	global $FapiPlugin;
	$subpage = $FapiPlugin->findSubpage();

	$subpage  = ( $subpage === 'settingsContentSelect' ) ? 'settingsContentRemove' : $subpage;
	$selected = ( isset( $_GET['level'] ) ) ? (int) $_GET['level'] : null;

	$t = $FapiPlugin->levels()->loadAsTerms();

	$lis = [];

	foreach ( $t as $term ) {
		$under = [];
		if ( $term->parent === 0 ) {
			foreach ( $t as $underTerm ) {
				if ( $underTerm->parent === $term->term_id ) {
					$under[] = oneLevelSelection(
						$underTerm->term_id,
						fapilink( $subpage ) . sprintf( '&level=%s', $underTerm->term_id ),
						$underTerm->name,
						'',
						( $underTerm->term_id === $selected ) ? true : false
					);
				}
			}
			$lis[] = oneLevelSelection(
				$term->term_id,
				fapilink( $subpage ) . sprintf( '&level=%s', $term->term_id ),
				$term->name,
				join( '', $under ),
				( $term->term_id === $selected ) ? true : false
			);
		}
	}

	?>
    <div class="levels">
        <ol>
			<?php echo  join( '', $lis ) ?>
        </ol>
    </div>
	<?php
}

function levelsSelectionNonJs() {
	global $FapiPlugin;
	$subpage = $FapiPlugin->findSubpage();

	$subpage  = ( $subpage === 'settingsContentSelect' ) ? 'settingsContentRemove' : $subpage;
	$selected = ( isset( $_GET['level'] ) ) ? (int) $_GET['level'] : null;

	$t = $FapiPlugin->levels()->loadAsTerms();

	$lis = [];

	foreach ( $t as $term ) {
		$under = [];
		if ( $term->parent === 0 ) {
			foreach ( $t as $underTerm ) {
				if ( $underTerm->parent === $term->term_id ) {
					$under[] = oneLevelSelection(
						$underTerm->term_id,
						fapilink( $subpage ) . sprintf( '&level=%s', $underTerm->term_id ),
						$underTerm->name,
						'',
						( $underTerm->term_id === $selected ) ? true : false
					);
				}
			}
			$lis[] = oneLevelSelection(
				$term->term_id,
				fapilink( $subpage ) . sprintf( '&level=%s', $term->term_id ),
				$term->name,
				join( '', $under ),
				( $term->term_id === $selected ) ? true : false
			);
		}
	}

	?>
    <div class="levelsNonJs">
        <ol>
			<?php echo  join( '', $lis ) ?>
        </ol>
    </div>
	<?php
}

function getLevelOptions() {
	global $FapiPlugin;
	$t = $FapiPlugin->levels()->loadAsTerms();

	$options = [];

	foreach ( $t as $term ) {
		if ( $term->parent === 0 ) {
			$options[] = sprintf( '<option value="%s">%s</option>', $term->term_id, $term->name );
		}
	}

	return join( '', $options );
}

function allPagesForForm() {
	$posts = get_posts( [ 'post_type' => 'page', 'post_status' => [ 'publish' ], 'numberposts' => - 1 ] );

	$o = array_map( function ( $p ) {
		return sprintf( '<div class="onePage"><input type="checkbox" name="toAdd[]" value="%s"> %s</div>',
		                $p->ID,
		                $p->post_title );
	},
		$posts );

	return join( '', $o );
}

function allPagesAsOptions( $currentId ) {
	$posts = get_posts( [ 'post_type' => 'page', 'post_status' => [ 'publish' ], 'numberposts' => - 1 ] );

	$o = array_map( function ( $p ) use ( $currentId ) {
		$selected = ( $currentId === $p->ID ) ? 'selected' : '';

		return sprintf( '<option value="%s" %s>%s</option>', $p->ID, $selected, $p->post_title );
	},
		$posts );

	return join( '', $o );
}

function levelToPageJson() {
	global $FapiPlugin;

	return json_encode( $FapiPlugin->levels()->levelsToPages() );
}

function shortcodeLoginForm() {
	return '
        <div class="fapiShortcodeLoginForm">
            <form method="post" action="/wp-login.php">
                <div class="row">
                    <label for="log">Zadejte uživatelské jméno</label>
                    <input type="text" name="log" id="log">
                </div>
                <div class="row">
                    <label for="pwd">Zadejte heslo</label>
                    <input type="password" name="pwd" id="pwd">
                </div>
                <div class="row controls">
                    <input type="submit" class="primary" value="Přihlásit se">
                </div>
            </form>
        </div>    
    ';
}

function shortcodeUser() {
	global $FapiPlugin;

	$u = wp_get_current_user();
	if ( $u instanceof WP_User && is_user_logged_in() ) {
		return '
        <div class="fapiShortcodeUser">
            <span class="i">
                <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                     viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
                <g>
                    <g>
                        <path d="M437.02,330.98c-27.883-27.882-61.071-48.523-97.281-61.018C378.521,243.251,404,198.548,404,148
                            C404,66.393,337.607,0,256,0S108,66.393,108,148c0,50.548,25.479,95.251,64.262,121.962
                            c-36.21,12.495-69.398,33.136-97.281,61.018C26.629,379.333,0,443.62,0,512h40c0-119.103,96.897-216,216-216s216,96.897,216,216
                            h40C512,443.62,485.371,379.333,437.02,330.98z M256,256c-59.551,0-108-48.448-108-108S196.449,40,256,40
                            c59.551,0,108,48.448,108,108S315.551,256,256,256z"/>
                    </g>
                </g>
                </svg>
            </span>
            <span class="h">Uživatel</span>
            <span class="l">' . $u->user_login . '</span>
            <div class="submenu">
                <a href="' . wp_logout_url( get_permalink() ) . '">Odhlásit se</a>
            </div>
        </div>    
    ';
	} else {
		$setLoginPageId = $FapiPlugin->getSetting( 'login_page_id' );
		if ( $setLoginPageId === null ) {
			$url = wp_login_url();
		} else {
			$url = get_permalink( $setLoginPageId );
		}

		return '
        <div class="fapiShortcodeUser notLogged">
            <span class="i">
                <svg id="bold" enable-background="new 0 0 24 24" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="m18.75 9h-.75v-3c0-3.309-2.691-6-6-6s-6 2.691-6 6v3h-.75c-1.24 0-2.25 1.009-2.25 2.25v10.5c0 1.241 1.01 2.25 2.25 2.25h13.5c1.24 0 2.25-1.009 2.25-2.25v-10.5c0-1.241-1.01-2.25-2.25-2.25zm-10.75-3c0-2.206 1.794-4 4-4s4 1.794 4 4v3h-8zm5 10.722v2.278c0 .552-.447 1-1 1s-1-.448-1-1v-2.278c-.595-.347-1-.985-1-1.722 0-1.103.897-2 2-2s2 .897 2 2c0 .737-.405 1.375-1 1.722z"/></svg>
            </span>
            <span class="l"><a href="' . $url . '">Přihlásit se</a></span>
        </div>
        ';
	}
}

function formStart( $hook, $formClasses = [] ) {
	$class = ( empty( $formClasses ) ) ? '' : sprintf( ' class="%s"', join( ' ', $formClasses ) );

	return '
    <form ' . $class . ' method="post" action="' . admin_url( 'admin-post.php' ) . '">
        <input type="hidden" name="action" value="fapi_member_' . $hook . '">
        <input type="hidden" name="fapi_member_' . $hook . '_nonce"
               value="' . wp_create_nonce( 'fapi_member_' . $hook . '_nonce' ) . '">
    ';
}

function resolutionMessage() {
	return '<p class="resolutionAlert">Tento doplněk není optimalizován pro telefony a malé monitory.</p>';
}

function heading() {
	return sprintf( '%s<div class="baseGrid">%s%s%s', resolutionMessage(), h1(), nav(), submenu() );
}