import React from 'react';
import loginImage from 'Images/elements/login.png';
import userWindowImage from 'Images/elements/user-window.png';
import expirationExampleImage from 'Images/elements/section-expiration-shortcode-example.png';
import expirationResultImage from 'Images/elements/section-expiration-shortcode-result.png';

function Elements() {
  return (
      <div className="levels-content">
        <h3>Formulář pro přihlášení <code>[fapi-member-login]</code></h3>
        <p>Vloží na web přihlašovací formulář</p>
        <p>- Formulář se zobrazí po znovunačtení stránky.</p>
        <img src={loginImage} alt="Přihlačovací formulář" height="200px"/>
          
        <div className="vertical-divider"/>
      
        <h3>Uživatelské okénko <code>[fapi-member-user]</code></h3>
        <p>Okénko vložte do svého webu na požadovaná místa pomocí shortcode.</p>
        <p>Uživatelské okénko bude funkční pro uživatele všech členských sekcí a úrovní.</p>
        <img src={userWindowImage} alt="Přihlášený uživatel"/>

        <div className="vertical-divider"/>

        <h3>Datum expirace pro členskou sekci nebo úrověň <code>[fapi-member-user-section-expiration section=x]</code></h3>
        <p>Vypíše datum expirace zvolené členské sekce nebo úrovně.</p>
	    <p>Místo proměnné "x" zadejte ID  členské sekce a místo tohoto shortcodu se uživateli vypíše datum do kdy má přístup do dané členské sekce nebo úrovně</p>

	    <h5>Jak shortcode použít</h5>
        <img src={expirationExampleImage} alt="Snímek obrazovky" height="60px"/>


        <h5>Jak to poté vidí člen</h5>
        <img src={expirationResultImage} alt="Snímek obrazovky" height="50px"/>

        <div className="vertical-divider"/>

        <h3>Odemčení úrovně tlačítkem <code>[fapi-member-unlock-level level=x page=y]</code></h3>
        <p>
            Jde o tlačítko, kterým uživatel může odeknou úroveň.
            Parametr "page" representuje ID stránky, na kterou bude uživatel přesměrován, po odemčení úrovně. Tento parametr je volitelný. Pokud nebude zadán, uživatel bude odkázán na jednu ze stránek přiřazených k úrovni.
        </p>

        <div className="vertical-divider"/>

        <h3>Čas zbývající do odemčení úrovně <code>[fapi-member-level-unlock-date level=x]</code></h3>
        <p>
            Můžete zobrazit datum odemčení členské úrovně ve formátu "01.01.2023".
            Tento kód doporučujeme umístit na stránku nastavenou v části "Postupné uvolňování obsahu
        </p>

      </div>
  );
}

export default Elements;
