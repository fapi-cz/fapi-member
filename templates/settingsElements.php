<?php include(__DIR__ . '/functions.php') ?>

<div class="baseGrid">
    <?= h1() ?>
    <?= nav($subpage, $areApiCredentialsSet) ?>
    <?= submenu($subpage) ?>

    <div class="page wider">
        <h3>Prvky pro web</h3>
        <h4>Formulář pro přihlášení</h4>
        <p>Formulář vložte do svého webu na požadovaná místa pomocí shortcode <code>[fapi-member-login]</code>.</p>
        <p>Formulář se zobrazí pro znovu načtení stránky.</p>

        <div class="showcase">
            FORM
        </div>

        <hr>

        <h4>Uživatelské okénko</h4>
        <p>Okénko vložte do svého webu na požadovaná místa pomocí shortcode <code>[fapi-member-user]</code>.</p>
        <p>Uživatelské okénko bude funkční pro uživatele všech členských sekcí a úrovní.</p>

        <div class="showcase">
            USER
        </div>
    </div>
</div>