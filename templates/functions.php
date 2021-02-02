<?php



function showErrors() {

    $errorMap = [
        'apiFormEmpty' => ['error', 'Je třeba zadat jak uživatelské jméno, tak API klíč.'],
        'apiFormSuccess' => ['success', 'Údaje pro API uloženy.'],
    ];

    if (isset($_GET['e']) && isset($errorMap[$_GET['e']])) {
        $e = $errorMap[$_GET['e']];
        return sprintf('<div class="notice notice-%s is-dismissible"><p>%s</p></div>', $e[0], $e[1]);
    }
}

function h1() {
    return '<h1>Fapi Member</h1>';
}

function nav($subpage, $areApiCredentialsSet) {

    if (!$areApiCredentialsSet) {
        return '
            <nav>
                <span class="disabled">
                    <span class="a">Nástěnka</span>
                    <span class="b">Přehled</span>
                </span>
                <span href="#" class="disabled">
                    <span class="a">Nastavení</span>
                    <span class="b">Členské sekce</span>
                </span>
                <a href="#" class="active">
                    <span class="a">Propojení</span>
                    <span class="b">Připojení k FAPI</span>
                </a>
            </nav>';
    } else {
        return '
            <nav>
                <a href="'.fapilink('index').'" '. (($subpage === 'index') ? 'class="active"' : '') .'>
                    <span class="a">Nástěnka</span>
                    <span class="b">Přehled</span>
                </a>
                <a href="'.fapilink('settings').'" '. ((strpos($subpage, 'settings') === 0) ? 'class="active"' : '') .'>
                    <span class="a">Nastavení</span>
                    <span class="b">Členské sekce</span>
                </span>
                <a href="'.fapilink('connection').'" '. (($subpage === 'connection') ? 'class="active"' : '') .'>
                    <span class="a">Propojení</span>
                    <span class="b">Připojení k FAPI</span>
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
                    '. submenuItem('settings', 'Sekce / úrovně', $subpage) .'
                    '. submenuItem('settingsContent', 'Přiřazené stránky', $subpage) .'
                    '. submenuItem('settingsEmails', 'Nastavení e-mailů', $subpage) .'
                    '. submenuItem('settingsPages', 'Ostatní stránky', $subpage) .'
                    '. submenuItem('settingsElements', 'Prvky pro web', $subpage) .'
                </div>
                ';
    }

}

function submenuItem($subpage, $label, $activeSubpage) {
    $classes = [];

    if ($activeSubpage === $subpage) {
        $classes[] = 'active';
    }

    return sprintf('<a href="%s" class="%s">%s</a>', fapilink($subpage), join(', ', $classes), $label);
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