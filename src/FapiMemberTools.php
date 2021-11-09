<?php

namespace FapiMember;

use WP_User;

final class FapiMemberTools
{

	/** @var array<array<string>> */
	private static $errorMap
		= [
			'apiFormEmpty' => ['error', 'Je třeba zadat jak uživatelské jméno, tak API klíč.'],
			'apiFormSuccess' => ['success', 'Údaje pro API uloženy.'],
			'apiFormError' => ['error', 'Neplatné údaje pro API.'],
			'sectionNameEmpty' => ['error', 'Název sekce je povinný.'],
			'levelNameOrParentEmpty' => ['error', 'Název úrovně a výběr sekce je povinný.'],
			'sectionNotFound' => ['error', 'Sekce nenalezena.'],
			'removeLevelSuccessful' => ['success', 'Sekce/úroveň smazána.'],
			'editLevelSuccessful' => ['success', 'Sekce/úroveň upravena.'],
			'levelIdOrToAddEmpty' => ['error', 'Zvolte prosím úroveň a stránky k přidání.'],
			'editLevelNoName' => ['error', 'Chyba změny sekce/úrovně.'],
			'editMailsRemoved' => ['success', 'Šablona emailu byla odebrána.'],
			'editMailsUpdated' => ['success', 'Šablona emailu byla upravena.'],
			'editOtherPagesRemoved' => ['success', 'Stránka byla nastavena.'],
			'editOtherPagesUpdated' => ['success', 'Stránka byla nastavena..'],
			'settingsSettingsUpdated' => ['success', 'Nastavení uložena.'],
			'settingsSettingsNoValidPage' => ['error', 'Stránka nenalezena.'],
		];

	/**
	 * @return string
	 */
	public static function showErrors()
	{
		$errorKey = self::findValidErrorKey();

		if ($errorKey) {
			$e = self::$errorMap[$errorKey];

			return sprintf('<div class="notice notice-%s is-dismissible"><p>%s</p></div>', $e[0], $e[1]);
		}

		return '';
	}

	/**
	 * @return string|null
	 */
	protected static function findValidErrorKey()
	{
		if (isset($_GET['e'], self::$errorMap[$_GET['e']]) && is_string($_GET['e'])) {
			return $_GET['e'];
		}

		return null;
	}

	/**
	 * @param string $subpage
	 * @param string $label
	 * @param string $activeSubpage
	 * @return string
	 */
	public static function subSubmenuItem($subpage, $label, $activeSubpage)
	{
		$classes = ['subsubmenuitem'];

		if ($activeSubpage === $subpage) {
			$classes[] = 'active';
		}

		$level = (isset($_GET['level'])) ? self::sanitizeLevelId($_GET['level']) : null;

		if ($level) {
			$tail = sprintf('&level=%s', $level);
		} else {
			$tail = '';
		}

		return sprintf(
			'<a href="%s%s" class="%s">%s</a>',
			self::fapilink($subpage),
			$tail,
			implode(' ', $classes),
			$label
		);
	}

	/**
	 * @param int $levelId
	 * @return int|null
	 */
	public static function sanitizeLevelId($levelId)
	{
		global $FapiPlugin;
		if (!is_numeric($levelId)) {
			return null;
		}
		$t = $FapiPlugin->levels()->loadById($levelId);
		if (!$t) {
			return null;
		}

		return (int) $levelId;
	}

	/**
	 * @param string $subpage
	 * @return string
	 */
	public static function fapilink($subpage)
	{
		return admin_url(sprintf('/admin.php?page=fapi-member-options&subpage=%s', $subpage));
	}

	/**
	 * @return string
	 */
	public static function help()
	{
		return '
    <div class="help">
        <h3>Nápověda</h3>
        <div class="inner">
            <div>
                <h4>Jak propojit plugin s FAPI?</h4>
                <p>Prvním krokem ke zprovoznění členských sekcí je propojení pluginu s vaším účtem FAPI.</p>
                <a href="https://napoveda.fapi.cz/article/97-fapi-member-propojeni-s-fapi" target="_blank" class="btn outline">Přečíst</a>
            </div>
            <div>
                <h4>Jak vytvořit členskou sekci?</h4>
                <p>Zde se dozvíte, co je to členská sekce nebo úroveň a jak ji správně nastavit.</p>
                <a href="https://napoveda.fapi.cz/article/98-fapi-member-nastaveni-clenske-sekce" target="_blank" class="btn outline">Přečíst</a>
            </div>
            <div>
                <h4>Jak přidat uživatele do členské sekce?</h4>
                <p>Zjistěte, jak nastavit prodejní formulář FAPI, aby automaticky zakládal členství vašim klientům.</p>
                <a href="https://napoveda.fapi.cz/article/99-fapi-member-zakladani-clenstvi" target="_blank" class="btn outline">Přečíst</a>
            </div>
        </div>
    </div>
    ';
	}

	/**
	 * @return void
	 */
	public static function levels()
	{
		global $FapiPlugin;
		$envelopes = $FapiPlugin->levels()->loadAsTermEnvelopes();

		$lis = [];
		$actions = '<button class="edit"></button><button class="remove"></button><button class="up"></button><button class="down"></button>';

		foreach ($envelopes as $envelope) {
			$term = $envelope->getTerm();
			$under = [];
			if ($term->parent === 0) {
				foreach ($envelopes as $underEnvelope) {
					$underTerm = $underEnvelope->getTerm();
					if ($underTerm->parent === $term->term_id) {
						$under[] = sprintf(
							'<li data-id="%s" data-name="%s"><span>%s</span>%s</li>',
							$underTerm->term_id,
							htmlentities($underTerm->name),
							self::trimName($underTerm->name),
							$actions
						);
					}
				}
				$lis[] = sprintf(
					'<li data-id="%s" data-name="%s"><span>%s</span>%s<ol>%s</ol></li>',
					$term->term_id,
					$term->name,
					self::trimName($term->name),
					$actions,
					implode('', $under)
				);
			}
		}

		?>
        <div class="levels">
            <ol>
				<?php echo implode('', $lis) ?>
            </ol>
        </div>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="LevelRemoveForm">
            <input type="hidden" name="action" value="fapi_member_remove_level">
            <input type="hidden" name="fapi_member_remove_level_nonce"
                   value="<?php echo wp_create_nonce('fapi_member_remove_level_nonce') ?>">
            <input type="hidden" name="level_id" value="">
        </form>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="LevelEditForm">
            <input type="hidden" name="action" value="fapi_member_edit_level">
            <input type="hidden" name="fapi_member_edit_level_nonce"
                   value="<?php echo wp_create_nonce('fapi_member_edit_level_nonce') ?>">
            <input type="hidden" name="name" value="">
            <input type="hidden" name="level_id" value="">
        </form>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="LevelOrderForm">
            <input type="hidden" name="action" value="fapi_member_order_level">
            <input type="hidden" name="fapi_member_order_level_nonce"
                   value="<?php echo wp_create_nonce('fapi_member_order_level_nonce') ?>">
            <input type="hidden" name="direction" value="">
            <input type="hidden" name="id" value="">
        </form>
		<?php
	}

	/**
	 * @param string $name
	 * @param int $chars
	 * @return string
	 */
	public static function trimName($name, $chars = 30)
	{
		if (mb_strlen($name) > $chars) {
			return sprintf('%s&hellip;', mb_substr($name, 0, $chars - 1));
		}

		return $name;
	}

	/**
	 * @return void
	 */
	public static function levelsSelection()
	{
		global $FapiPlugin;
		$subpage = $FapiPlugin->findSubpage();

		$subpage = ($subpage === 'settingsContentSelect') ? 'settingsContentRemove' : $subpage;
		$selected = (isset($_GET['level'])) ? self::sanitizeLevelId($_GET['level']) : null;

		$envelopes = $FapiPlugin->levels()->loadAsTermEnvelopes();

		$lis = [];

		foreach ($envelopes as $envelope) {
			$term = $envelope->getTerm();
			$under = [];
			if ($term->parent === 0) {
				foreach ($envelopes as $underEnvelope) {
					$underTerm = $underEnvelope->getTerm();
					if ($underTerm->parent === $term->term_id) {
						$under[] = self::oneLevelSelection(
							$underTerm->term_id,
							self::fapilink($subpage) . sprintf('&level=%s', $underTerm->term_id),
							$underTerm->name,
							'',
							$underTerm->term_id === $selected
						);
					}
				}
				$lis[] = self::oneLevelSelection(
					$term->term_id,
					self::fapilink($subpage) . sprintf('&level=%s', $term->term_id),
					$term->name,
					implode('', $under),
					$term->term_id === $selected
				);
			}
		}

		?>
        <div class="levels">
            <ol>
				<?php echo implode('', $lis) ?>
            </ol>
        </div>
		<?php
	}

	/**
	 * @param int $id
	 * @param string $link
	 * @param string $name
	 * @param string $children
	 * @param bool $highlight
	 * @return string
	 */
	public static function oneLevelSelection($id, $link, $name, $children = '', $highlight = false)
	{
		$c = ($highlight) ? 'class="selected"' : '';
		$ch = (!empty($children)) ? sprintf('<ol>%s</ol>', $children) : '';

		return sprintf(
			'<li data-id="%s" %s><a href="%s">%s</a>%s</li>',
			$id,
			$c,
			$link,
			$name,
			$ch
		);
	}

	/**
	 * @return void
	 */
	public static function levelsSelectionNonJs()
	{
		global $FapiPlugin;
		$subpage = $FapiPlugin->findSubpage();

		$subpage = ($subpage === 'settingsContentSelect') ? 'settingsContentRemove' : $subpage;
		$selected = (isset($_GET['level'])) ? self::sanitizeLevelId($_GET['level']) : null;

		$evnelopes = $FapiPlugin->levels()->loadAsTermEnvelopes();

		$lis = [];

		foreach ($evnelopes as $envelope) {
			$term = $envelope->getTerm();
			$under = [];
			if ($term->parent === 0) {
				foreach ($evnelopes as $underEnvelope) {
					$underTerm = $underEnvelope->getTerm();
					if ($underTerm->parent === $term->term_id) {
						$under[] = self::oneLevelSelection(
							$underTerm->term_id,
							self::fapilink($subpage) . sprintf('&level=%s', $underTerm->term_id),
							self::trimName($underTerm->name),
							'',
							$underTerm->term_id === $selected
						);
					}
				}
				$lis[] = self::oneLevelSelection(
					$term->term_id,
					self::fapilink($subpage) . sprintf('&level=%s', $term->term_id),
					self::trimName($term->name),
					implode('', $under),
					$term->term_id === $selected
				);
			}
		}

		?>
        <div class="levelsNonJs">
            <ol>
				<?php echo implode('', $lis) ?>
            </ol>
        </div>
		<?php
	}

	/**
	 * @return string
	 */
	public static function getLevelOptions()
	{
		global $FapiPlugin;
		$t = $FapiPlugin->levels()->loadAsTermEnvelopes();

		$options = [];

		foreach ($t as $termEnvelope) {
			$term = $termEnvelope->getTerm();
			if ($term->parent === 0) {
				$options[] = sprintf('<option value="%s">%s</option>', $term->term_id, $term->name);
			}
		}

		return implode('', $options);
	}

	/**
	 * @param int $levelId
	 * @return string
	 */
	public static function allPagesForForm($levelId)
	{
		global $FapiPlugin;
		$posts = get_posts(['post_type' => 'page', 'post_status' => ['publish'], 'numberposts' => -1]);
		$levelTerm = $FapiPlugin->levels()->loadById($levelId);
		$postsInLevel = $FapiPlugin->levels()->pageIdsForLevel($levelTerm);
		$o = array_map(
			function ($p) use ($postsInLevel) {
				$checked = (in_array($p->ID, $postsInLevel)) ? ' checked ' : '';

				return sprintf(
					'<div class="onePage"><input type="checkbox" name="selection[]" value="%s" %s> %s</div>',
					$p->ID,
					$checked,
					$p->post_title
				);
			},
			$posts
		);

		return implode('', $o);
	}

	/**
	 * @param int $levelId
	 * @return string
	 */
	public static function allPagesInLevel($levelId)
	{
		global $FapiPlugin;
		$levelTerm = $FapiPlugin->levels()->loadById($levelId);
		$pageIds = $FapiPlugin->levels()->pageIdsForLevel($levelTerm);

		if (count($pageIds) === 0) {
			return '';
		}

		$posts = get_posts(
			[
				'post_type' => 'page',
				'post_status' => ['publish'],
				'numberposts' => -1,
				'include' => $pageIds,
			]
		);

		$o = array_map(
			function ($p) {
				return sprintf(
					'<div class="onePage">%s</div>',
					$p->post_title
				);
			},
			$posts
		);

		return implode('', $o);
	}

	/**
	 * @param int $pageId
	 * @return string|null
	 */
	public static function getPageTitle($pageId)
	{
		$posts = get_posts(['post_type' => 'page', 'post_status' => ['publish'], 'numberposts' => -1]);

		foreach ($posts as $post) {
			if ((int) $post->ID !== $pageId) {
				continue;
			}

			return $post->post_title;
		}

		return null;
	}

	/**
	 * @param int $currentId
	 * @return string
	 */
	public static function allPagesAsOptions($currentId)
	{
		$posts = get_posts(['post_type' => 'page', 'post_status' => ['publish'], 'numberposts' => -1]);
		$output = [];

		foreach ($posts as $post) {
			$selected = ($currentId === $post->ID) ? 'selected' : '';

			$output[] = sprintf('<option value="%s" %s>%s</option>\n', $post->ID, $selected, $post->post_title);
		}

		return implode(' ', $output);
	}

	/**
	 * @return string
	 */
	public static function levelToPageJson()
	{
		global $FapiPlugin;

		return json_encode($FapiPlugin->levels()->levelsToPages());
	}

	/**
	 * @return string
	 */
	public static function shortcodeLoginForm()
	{
		return '
        <div class="fapiShortcodeLoginForm">
            <form method="post" action="/wp-login.php">
                <div class="f-m-row">
                    <label for="log">' . __('Přihlašovací jméno') . '</label>
                    <input type="text" name="log" id="user_login" value="" size="20">
                </div>
                <div class="f-m-row">
                    <label for="pwd">' . __('Heslo') . '</label>
                    <input type="password" name="pwd" id="user_pass" value="" size="20">
                </div>
                <div class="f-m-row">
                <a href="/wp-login.php?action=lostpassword">' . __('Zapomněli jste heslo?') . '</a>
                </div>
                <div class="f-m-row controls">
                    <input type="submit" class="primary" value="' . __('Přihlásit se') . '">
                </div>
            </form>
        </div>     
    ';
	}

	/**
	 * @return string
	 */
	public static function shortcodeUser()
	{
		global $FapiPlugin;

		$u = wp_get_current_user();
		if ($u instanceof WP_User && is_user_logged_in()) {
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
            <span class="l">' . self::trimName($u->user_login, 8) . '</span>
            <div class="f-m-submenu">
                <a href="' . wp_logout_url(get_permalink()) . '">Odhlásit se</a>
            </div>
        </div>    
    ';
		}

		$setLoginPageId = $FapiPlugin->getSetting('login_page_id');

		if ($setLoginPageId === null) {
			$url = wp_login_url();
		} else {
			$url = get_permalink($setLoginPageId);
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

	/**
	 * @param string $hook
	 * @param array<string> $formClasses
	 * @return string
	 */
	public static function formStart($hook, $formClasses = [])
	{
		$class = (empty($formClasses)) ? '' : sprintf(' class="%s"', implode(' ', $formClasses));

		return '
    <form ' . $class . ' method="post" action="' . admin_url('admin-post.php') . '">
        <input type="hidden" name="action" value="fapi_member_' . $hook . '">
        <input type="hidden" name="fapi_member_' . $hook . '_nonce"
               value="' . wp_create_nonce('fapi_member_' . $hook . '_nonce') . '">
    ';
	}

	/**
	 * @return string
	 */
	public static function heading()
	{
		return sprintf(
			'%s<div class="baseGrid">%s%s%s',
			self::resolutionMessage(),
			self::h1(),
			self::nav(),
			self::submenu()
		);
	}

	/**
	 * @return string
	 */
	public static function resolutionMessage()
	{
		return '<p class="resolutionAlert">Tento doplněk není optimalizován pro telefony a malé monitory.</p>';
	}

	/**
	 * @return string
	 */
	public static function h1()
	{
		$svg = file_get_contents(__DIR__ . '/../_sources/LOGO_FAPI_svg.svg');

		return sprintf('<div class="h1"><a href="https://web.fapi.cz">%s</a></div>', $svg);
	}

	/**
	 * @return string
	 */
	public static function nav()
	{
		global $FapiPlugin;
		$subpage = $FapiPlugin->findSubpage();
		$areApiCredentialsSet = $FapiPlugin->areApiCredentialsSet();

		$c = file_get_contents(__DIR__ . '/../_sources/connect.svg');
		$h = file_get_contents(__DIR__ . '/../_sources/home-solid.svg');
		$p = file_get_contents(__DIR__ . '/../_sources/padlock.svg');

		$testActionLink = '';
		if ($FapiPlugin::isDevelopment()) {
			$testActionLink = '
            <a href="' . self::fapilink('test') . '" ' . (($subpage === 'test') ? 'class="active"' : '') . '>
                <span class="a" style="color: #9a1818;">Testovací akce</span>
            </a>';
		}

		if (!$areApiCredentialsSet) {
			return '
            <nav>
                <span class="disabled">
                    <span class="a">Přehled</span>
                    ' . $h . '
                </span>
                <span class="disabled">
                    <span class="a">Členské sekce</span>
                    ' . $p . '
                </span>
                <a href="#" class="active">
                    <span class="a">Propojení s FAPI</span>
                    ' . $c . '
                </a>
                ' . $testActionLink . '
            </nav>';
		}

		return '
        <nav>
            <a href="' . self::fapilink('index') . '" ' . (($subpage === 'index') ? 'class="active"' : '') . '>
                <span class="a">Přehled</span>
                ' . $h . '
            </a>
            <a href="' . self::fapilink('settingsSectionNew') . '" ' . ((strpos(
					$subpage,
					'settings'
				) === 0) ? 'class="active"' : '') . '>
                <span class="a">Členské sekce</span>
                ' . $p . '
            </a>
            <a href="' . self::fapilink('connection') . '" ' . (($subpage === 'connection') ? 'class="active"' : '') . '>
                <span class="a">Propojení s FAPI</span>
                ' . $c . '
            </a>
            ' . $testActionLink . '
        </nav>';
	}

	/**
	 * @return string
	 */
	public static function submenu()
	{
		global $FapiPlugin;
		$subpage = $FapiPlugin->findSubpage();

		switch (true) {
			case ($subpage === 'index'):
				return '
                <div class="submenu">
                    <span class="active">Přehled</a>
                </div>
                ';
			case ($subpage === 'connection'):
				return '
                <div class="submenu">
                    <span class="active">Propojení</a>
                </div>
                ';
			case (mb_strpos($subpage, 'settings') === 0):
				return '
                <div class="submenu">
                    ' . self::submenuItem('settingsSectionNew', 'Sekce / úrovně', $subpage, ['settingsLevelNew']) . '
                    ' . self::submenuItem(
						'settingsContentAdd',
						'Přiřazené stránky',
						$subpage,
						['settingsContentRemove']
					) . '
                    ' . self::submenuItem('settingsEmails', 'E-maily', $subpage) . '
                    ' . self::submenuItem('settingsPages', 'Servisní stránky', $subpage) . '
                    ' . self::submenuItem('settingsElements', 'Prvky pro web', $subpage) . '
                    ' . self::submenuItem('settingsSettings', 'Společné', $subpage) . '
                </div>
                ';
		}

		return '';
	}

	/**
	 * @param string $subpage
	 * @param string $label
	 * @param string $activeSubpage
	 * @param array<string> $otherChildren
	 * @return string
	 */
	public static function submenuItem($subpage, $label, $activeSubpage, $otherChildren = null)
	{
		$classes = [];

		if ($activeSubpage === $subpage) {
			$classes[] = 'active';
		}
		if ($otherChildren !== null && in_array($activeSubpage, $otherChildren, true)) {
			$classes[] = 'active';
		}

		return sprintf('<a href="%s" class="%s">%s</a>', self::fapilink($subpage), implode(' ', $classes), $label);
	}

}
