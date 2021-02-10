<?php

function showErrors() {

    $errorMap = [
        'apiFormEmpty' => ['error', 'Je třeba zadat jak uživatelské jméno, tak API klíč.'],
        'apiFormSuccess' => ['success', 'Údaje pro API uloženy.'],
        'sectionNameEmpty' => ['error', 'Název sekce je povinný.'],
        'levelNameOrParentEmpty' => ['error', 'Název úrovně a výběr sekce je povinný.'],
        'sectionNotFound' => ['error', 'Sekce nenalezena.'],
        'removeLevelSuccessful' => ['success', 'Sekce/úroveň smazána.'],
        'editLevelSuccessful' => ['success', 'Sekce/úroveň upravena.'],
        'levelIdOrToAddEmpty' => ['error', 'Zvolte prosím úroveň a stránky k přidání.'],
        'editLevelNoName' => ['error', 'Chyba změny sekce/úrovně.'],
    ];

    if (isset($_GET['e']) && isset($errorMap[$_GET['e']])) {
        $e = $errorMap[$_GET['e']];
        return sprintf('<div class="notice notice-%s is-dismissible"><p>%s</p></div>', $e[0], $e[1]);
    }
}

function h1() {
    $svg = file_get_contents(__DIR__ . '/../_sources/LOGO_FAPI_svg.svg');
    return sprintf('<div class="h1">%s</div>', $svg);
}

function nav($subpage, $areApiCredentialsSet) {

    $c = file_get_contents(__DIR__ . '/../_sources/connect.svg');
    $h = file_get_contents(__DIR__ . '/../_sources/home-solid.svg');
    $p = file_get_contents(__DIR__ . '/../_sources/padlock.svg');

    if (!$areApiCredentialsSet) {
        return '
            <nav>
                <span class="disabled">
                    <span class="a">Nástěnka</span>
                    <span class="b">Přehled</span>
                    '. $h .'
                </span>
                <span href="#" class="disabled">
                    <span class="a">Nastavení</span>
                    <span class="b">Členské sekce</span>
                    '. $p .'
                </span>
                <a href="#" class="active">
                    <span class="a">Propojení</span>
                    <span class="b">Připojení k FAPI</span>
                    '. $c .'
                </a>
            </nav>';
    } else {
        return '
            <nav>
                <a href="'.fapilink('index').'" '. (($subpage === 'index') ? 'class="active"' : '') .'>
                    <span class="a">Nástěnka</span>
                    <span class="b">Přehled</span>
                    '. $h .'
                </a>
                <a href="'.fapilink('settingsSectionNew').'" '. ((strpos($subpage, 'settings') === 0) ? 'class="active"' : '') .'>
                    <span class="a">Nastavení</span>
                    <span class="b">Členské sekce</span>
                    '. $p .'
                </span>
                <a href="'.fapilink('connection').'" '. (($subpage === 'connection') ? 'class="active"' : '') .'>
                    <span class="a">Propojení</span>
                    <span class="b">Připojení k FAPI</span>
                    '. $c .'
                </a>
            </nav>';
    }

}

function submenu($subpage) {
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
                    '. submenuItem('settingsSectionNew', 'Sekce / úrovně', $subpage, ['settingsLevelNew']) .'
                    '. submenuItem('settingsContentRemove', 'Přiřazené stránky', $subpage, ['settingsContentAdd']) .'
                    '. submenuItem('settingsEmails', 'Nastavení e-mailů', $subpage) .'
                    '. submenuItem('settingsPages', 'Ostatní stránky', $subpage) .'
                    '. submenuItem('settingsElements', 'Prvky pro web', $subpage) .'
                </div>
                ';
    }

}

function submenuItem($subpage, $label, $activeSubpage, $otherChildren = null) {
    $classes = [];

    if ($activeSubpage === $subpage) {
        $classes[] = 'active';
    }
    if ($otherChildren !== null && in_array($activeSubpage, $otherChildren)) {
        $classes[] = 'active';
    }

    return sprintf('<a href="%s" class="%s">%s</a>', fapilink($subpage), join(' ', $classes), $label);
}

function subSubmenuItem($subpage, $label, $activeSubpage) {
    $classes = ['subsubmenuitem'];

    if ($activeSubpage === $subpage) {
        $classes[] = 'active';
    }

    if (isset($_GET['level'])) {
        $tail = sprintf('&level=%s', $_GET['level']);
    } else {
        $tail = '';
    }

    return sprintf('<a href="%s%s" class="%s">%s</a>', fapilink($subpage), $tail, join(' ', $classes), $label);
}

function help() {
    return '
    <div class="help">
        <h3>Nápověda</h3>
        <div class="inner">
            <div>
                <h4>Jak vytvořit členskou sekci</h4>
                <p>Nevíte si rady, podívejte se na náš fantastický návod kudy do toho, pak bude vše jasnější.</p>
                <a href="" class="btn outline">Přečíst</a>
            </div>
        </div>
    </div>
    ';
}

function fapilink($subpage) {
    return admin_url(sprintf('/options-general.php?page=fapi-member-options&subpage=%s', $subpage));
}

function levels() {
    global $fapiLevels;
    $t = $fapiLevels->loadAsTerms();

    $lis = [];
    $actions = '<button class="edit"></button><button class="remove"></button>';

    foreach ($t as $term) {
        $under = [];
        if ($term->parent === 0) {
            foreach ($t as $underTerm) {
                if ($underTerm->parent === $term->term_id) {
                    $under[] = sprintf('<li data-id="%s"><span>%s</span>%s</li>', $underTerm->term_id, $underTerm->name, $actions);
                }
            }
            $lis[] = sprintf('<li data-id="%s"><span>%s</span>%s<ol>%s</ol></li>', $term->term_id, $term->name, $actions, join('',$under));
        }
    }

    ?>
    <div class="levels">
        <ol>
            <?= join('', $lis) ?>
        </ol>
    </div>
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="LevelRemoveForm">
        <input type="hidden" name="action" value="fapi_member_remove_level">
        <input type="hidden" name="fapi_member_remove_level_nonce" value="<?php echo wp_create_nonce('fapi_member_remove_level_nonce') ?>">
        <input type="hidden" name="level_id" value="">
    </form>
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="LevelEditForm">
        <input type="hidden" name="action" value="fapi_member_edit_level">
        <input type="hidden" name="fapi_member_edit_level_nonce" value="<?php echo wp_create_nonce('fapi_member_edit_level_nonce') ?>">
        <input type="hidden" name="name" value="">
        <input type="hidden" name="level_id" value="">
    </form>
    <?php
}

function oneLevelSelection($id, $link, $name, $children = '', $highlight = false)
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

function levelsSelection($subpage) {
    global $fapiLevels;

    $subpage = ($subpage === 'settingsContentSelect') ? 'settingsContentRemove' : $subpage;
    $selected = (isset($_GET['level'])) ? (int)$_GET['level'] : null;

    $t = $fapiLevels->loadAsTerms();

    $lis = [];

    foreach ($t as $term) {
        $under = [];
        if ($term->parent === 0) {
            foreach ($t as $underTerm) {
                if ($underTerm->parent === $term->term_id) {
                    $under[] = oneLevelSelection(
                        $underTerm->term_id,
                        fapilink($subpage) . sprintf('&level=%s', $underTerm->term_id),
                        $underTerm->name,
                        '',
                        ($underTerm->term_id === $selected) ? true : false
                    );
                }
            }
            $lis[] = oneLevelSelection(
                $term->term_id,
                fapilink($subpage) . sprintf('&level=%s', $term->term_id),
                $term->name,
                join('',$under),
                ($term->term_id === $selected) ? true : false
            );
        }
    }

    ?>
    <div class="levels">
        <ol>
            <?= join('', $lis) ?>
        </ol>
    </div>
    <?php
}

function getLevelOptions() {

    global $fapiLevels;
    $t = $fapiLevels->loadAsTerms();

    $options = [];

    foreach ($t as $term) {
        if ($term->parent === 0) {
            $options[] = sprintf('<option value="%s">%s</option>', $term->term_id, $term->name);
        }
    }

    return join('', $options);
}

function allPagesForForm()
{
    $posts = get_posts(['post_type' => 'page', 'post_status' => ['publish']]);

    $o = array_map(function($p) {
        return sprintf('<div class="onePage"><input type="checkbox" name="toAdd[]" value="%s"> %s</div>', $p->ID, $p->post_title);
    }, $posts);

    return join('', $o);

}

function levelToPageJson()
{
    global $fapiLevels;

    return json_encode($fapiLevels->levelsToPages());
}