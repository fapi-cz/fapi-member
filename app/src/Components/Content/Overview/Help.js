import React, {useState} from 'react';

function Help() {
    const [helpHidden, setHelpHidden]
        = useState(document.querySelector('.content').offsetWidth < 1020);

    window.addEventListener('resize', (event) => {
        setHelpHidden(document.querySelector('.content').offsetWidth < 1020);
    })

    if (helpHidden) {
        return null;
    }

  return (
      <div className="content-help">
        <div className="inner">
            <div>
                <h4>Jak propojit plugin s FAPI?</h4>
                <p>Prvním krokem ke zprovoznění členských sekcí je propojení pluginu s vaším účtem FAPI.</p>
                <a
                    className='fm-link-button center'
                    href="https://napoveda.fapi.cz/article/97-fapi-member-propojeni-s-fapi"
                    target="_blank"
                >
                    Přečíst
                </a>
                <div className='vertical-divider'/>
            </div>
            <div>
                <h4>Jak vytvořit členskou sekci?</h4>
                <p>Zde se dozvíte, co je to členská sekce nebo úroveň a jak ji správně nastavit.</p>
                <a
                    className='fm-link-button center'
                    href="https://napoveda.fapi.cz/article/98-fapi-member-nastaveni-clenske-sekce"
                    target="_blank"
                >
                    Přečíst
                </a>
                <div className='vertical-divider'/>
            </div>
            <div>
                <h4>Jak přidat uživatele do členské sekce?</h4>
                <p>Zjistěte, jak nastavit prodejní formulář FAPI, aby automaticky zakládal členství vašim klientům.</p>
                <a
                    className='fm-link-button center'
                    href="https://napoveda.fapi.cz/article/99-fapi-member-zakladani-clenstvi"
                    target="_blank"
                >
                    Přečíst
                </a>
            </div>
        </div>
    </div>
  );
}

export default Help;
