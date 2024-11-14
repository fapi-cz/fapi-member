import React, {useEffect, useState} from 'react';
import 'Styles/connection.css';
import Help from 'Components/Content/Overview/Help';
import SubmitButton from 'Components/Elements/SubmitButton';
import Loading from 'Components/Elements/Loading';
import MemberSectionClient from 'Clients/MemberSectionClient';
import MembershipClient from 'Clients/MembershipClient';
import PageClient from 'Clients/PageClient';

function SimpleShopToFAPIMember() {
    const [loading, setLoading] = useState(true);
    const [sections, setSections] = useState(null);
    const [ssSections, setSsSections] = useState(null);
    const [pagesGroups, setPagesGroups] = useState(null);
    const [history, setHistory] = useState([]);
    const [migrating, setMigrating] = useState(false);

    const memberSectionClient = new MemberSectionClient();
    const membershipClient = new MembershipClient();
    const pageClient = new PageClient();

    useEffect(() => {
        const fetchInitialData = async () => {
            setSsSections(window.ssSections);
            setPagesGroups(window.ssPagesGroups);

            const updatedSections = await memberSectionClient.getAll();
            setSections(updatedSections);
            setLoading(false);
        };

        if (loading) {
            fetchInitialData();
        }
    }, [loading]);

    const updateHistory = (message) => {
        setHistory((prevHistory) => [...prevHistory, {description: message}]);
    };

    const mapSections = async (ssToFmMap, ssSectionKeyValue, fmSectionKeyValue) => {
        for (const ssSection of ssSections) {
            const ssSectionName = ssSection.name;
            ssSectionKeyValue[ssSection.id] = ssSectionName;

            const select = document.querySelector(`select[id="ss-${ssSection.id}"]`);
            const selected = select.value;
            const name = select.options[select.selectedIndex].text;

            if (selected === 'new') {
                const fmSection = await memberSectionClient.create(ssSectionName);
                ssToFmMap[ssSection.id] = fmSection.data.id;
                fmSectionKeyValue[fmSection.data.id] = ssSectionName;
                updateHistory(`Vytvořena nová sekce ${ssSectionName} (${ssSection.id}) v FAPI Member.`);
            } else if (selected) {
                ssToFmMap[ssSection.id] = Number(selected);
                fmSectionKeyValue[selected] = name;
            } else {
                updateHistory(`Sekce ${ssSectionName} (${ssSection.id}) nebude migrována.`);
            }
        }
    };

    const handleUserMigration = async (ssToFmMap, fmSectionKeyValue) => {
        for (const ssSection of ssSections) {
            const fmSectionId = ssToFmMap[ssSection.id];

            if (!fmSectionId) {
                continue;
            }

            for (const user of ssSection.users) {
                await membershipClient.create({level: fmSectionId, send_email: false, email: user.email});

                updateHistory(`Uživatel ${user.email} byl přidán do členské sekce ${fmSectionKeyValue[fmSectionId]} (${fmSectionId})`);
            }
        }
    };

    const handlePageMigration = async (ssToFmMap, fmSectionKeyValue) => {
        for (const pageGroups of pagesGroups) {
            const pageId = pageGroups.id;
            const name = pageGroups.name;
            const ssSectionIds = pageGroups.groups;

            for (const ssSectionId of ssSectionIds) {
                const fmSectionId = ssToFmMap[ssSectionId];

                if (!fmSectionId) {
                    continue;
                }

                await pageClient.addPagesToLevel(Number(fmSectionId), [Number(pageId)]);

                updateHistory(`Stránka ${name} ${pageId} byla přiřazena pod členskou sekci ${fmSectionKeyValue[fmSectionId]} (${fmSectionId})`);
            }
        }
    };

    const handleStartMigration = async () => {
        sessionStorage.setItem('fmLastAlertMessage', 'Migrace spuštěná');
        sessionStorage.setItem('fmLastAlertType', 'success');
        setMigrating(true);
        setHistory([{description: 'Začátek migrace! Tuto stránku nezavírejte dokud migrace neskončí.'}]);

        const ssSectionKeyValue = {};
        const fmSectionKeyValue = {};
        const ssToFmMap = {};

        await mapSections(ssToFmMap, ssSectionKeyValue, fmSectionKeyValue);

        for (const [ssId, fmId] of Object.entries(ssToFmMap)) {
            updateHistory(`Mapování sekce SimpleShop: ${ssSectionKeyValue[ssId]} (${ssId}) na FAPI Member: ${fmSectionKeyValue[fmId]} (${fmId}).`);
        }

        await handleUserMigration(ssToFmMap, fmSectionKeyValue);
        await handlePageMigration(ssToFmMap, fmSectionKeyValue);

        updateHistory('Migrace dokončena!');
        updateHistory('Zkontrolujte správnost přiřazení uživatelů a stránek.');
        updateHistory('Pokud je vše v pořádku, můžete deaktivovat plugin SimpleShop.');
        updateHistory('Otestujte si přihlášení a přístup k stránkám.');
        setMigrating(false);
    };

    if (sections === null) {
        return <Loading/>;
    }

    const findDefaultValue = (ssSection) => {
        const found = sections.find((section) =>
            section.name.localeCompare(ssSection.name, undefined, {sensitivity: 'base'}) === 0
        );
        return found ? found.id : '';
    };

    return (
        <div className="content-connection">
            <div>
                <h2>Přechod ze SimpleShop pluginu na FAPI Member</h2>

                <h3>Jak migrace funguje:</h3>
                <ol>
                    <li>Vytvoří nové členské sekce na základě stávajících nastavení v SimpleShopu.</li>
                    <li>Přiřadí uživatele do odpovídajících členských sekcí ve FAPI Member.</li>
                    <li>Přiřadí stránky do nových členských sekcí ve FAPI Member.</li>
                </ol>
                <p>Stačí vybrat odpovídající členskou sekci FAPI Member pro každou stávající členskou sekci
                    SimpleShopu.</p>

                <h3>Doporučení před spuštěním migrace:</h3>
                <ul>
                    <li><strong>Vytvořte si zálohu webu</strong>. Pro zálohu webu lze použít známé a často používané
                        pluginy pro WordPress, jako například <em>UpdraftPlus</em>, <em>All-in-One WP
                            Migration</em> nebo <em>Duplicator</em>. Záloha umožní obnovit původní stav v případě
                        problémů.
                    </li>
                    <li><strong>Časový rámec migrace</strong> se liší v závislosti na počtu členských sekcí, uživatelů,
                        stránek a na rychlosti serveru.
                    </li>
                    <li><strong>Postupné uvolňování obsahu</strong>: Funkce automatického uvolňování obsahu zajišťuje,
                        že se členům postupně zpřístupňují další úrovně členství (např. každý den nebo podle termínu).
                        Tato funkce se však nepřenáší a je třeba ji znovu nastavit v FAPI Member.
                    </li>
                    <li>Pro otestování migrace si vytvořte testovacího uživatele s členským účtem (např. na e-mailovou
                        adresu, která není administrátorská) a přiřaďte jej k několika členským sekcím SimpleShopu. Po
                        migraci tímto účtem ověřte, zda se dostane ke správným stránkám.
                    </li>
                </ul>

                <h3>Migrace probíhá následovně:</h3>
                <ol>
                    <li>Vytvoření nových členských sekcí na základě nastavení v SimpleShopu.</li>
                    <li>Přiřazení uživatelů do nových členských sekcí.</li>
                    <li>Přiřazení stránek do příslušných členských sekcí.</li>
                </ol>

                <h3>Důležité kroky po migraci:</h3>
                <ul>
                    <li><strong>Nastavení prodeje a přihlašovacích stránek</strong>: Doporučujeme převést prodej ze
                        SimpleShopu na FAPI, zkontrolovat a nastavit přihlašovací stránky, nástěnky a e-maily, které se
                        odesílají jednotlivým zákazníkům.
                    </li>
                    <li><strong>Přihlašovací údaje uživatelů zůstávají stejné</strong>. Uživatelé budou mít stále
                        možnost přihlásit se pod svým stávajícím uživatelským jménem a heslem.
                    </li>
                    <li><strong>Nastavení e-mailů a dalších funkcí</strong>: Obsahy e-mailů a další specifická nastavení
                        je potřeba znovu nastavit přímo v pluginu FAPI Member, protože tyto údaje se při migraci
                        nepřenášejí.
                    </li>

                    <li><strong>Deaktivujte plugin SimpleShop</strong>: Po úspěšné migraci můžete deaktivovat plugin
                        SimpleShop. Doporučujeme jej ihned nemazat, ale nechat jej vypnutý a sledovat, zda vše funguje
                        správně. Pokud by se něco nepovedlo, můžete plugin znovu aktivovat a zkontrolovat, co je potřeba
                        upravit.
                    </li>
                </ul>

                <h3>Jak poznáte, že migrace proběhla úspěšně?</h3>
                <p>Po spuštění migrace můžete deaktivovat SimpleShop plugin. Pokud se testovací uživatel, kterého jste
                    si přiřadili k několika členským sekcím, po migraci stále dostane k zamčeným stránkám, znamená to,
                    že migrace proběhla úspěšně.</p>

                <h3>Kompatibilita a verze WordPressu:</h3>
                <p>Migrace sama o sobě není závislá na verzi WordPressu. Plugin FAPI Member však podporuje verzi
                    WordPress <em>Requires at least: 5.9</em>, <em>Tested up to: 6.4</em>, <em>Requires PHP: 8.1</em>.
                </p>

                <p><strong>Poznámka:</strong> Délka migrace se může lišit podle počtu uživatelů a stránek. Během migrace
                    mohou nastat krátkodobé problémy s přihlašováním do členských sekcí.</p>

                <div className="vertical-divider"/>
                <br/>
                <h3>Nastavení migrace SimpleShop -> FAPI Member</h3>

                <table>
                    <thead>
                    <tr>
                        <th>Členská sekce SimpleShop</th>
                        <th>Členská sekce FAPI Member</th>
                    </tr>
                    </thead>
                    <tbody>
                    {ssSections.map((ssSection) => (
                        <tr key={ssSection.id}>
                            <td>{ssSection.name}</td>
                            <td>
                                <select id={'ss-' + ssSection.id} defaultValue={findDefaultValue(ssSection)}>
                                    <option value="">-- Žádná sekce --</option>
                                    <option value="new">Vytvořit novou sekci podle SimpleShop pluginu</option>
                                    {sections.map((section) => (
                                        <option key={section.id} value={section.id}>
                                            {section.name}
                                        </option>
                                    ))}
                                </select>
                            </td>
                        </tr>
                    ))}
                    </tbody>
                </table>
                <br/>
                <SubmitButton text="Spustit migraci" show={!migrating} centered={true} big={true}
                              onClick={handleStartMigration}/>
                {history.length > 0 && (
                    <div>
                        <br/>
                        <div className="vertical-divider"/>
                        <h3>Průběh migrace</h3>
                        <ol>
                            {history.map((item, index) => (
                                <li key={index}>{item.description}</li>
                            ))}
                        </ol>
                    </div>
                )}
            </div>
            <Help/>
        </div>
    );
}

export default SimpleShopToFAPIMember;
