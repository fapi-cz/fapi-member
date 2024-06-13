import React, {useEffect, useState} from 'react';
import PageClient from "Clients/PageClient";
import Select from "Components/Elements/Select";
import Loading from "Components/Elements/Loading";
import SubmitButton from "Components/Elements/SubmitButton";
import {CommonPageType} from "Enums/CommonPageType";

function Common({level}) {
    const pageClient = new PageClient();
    const [commonPageIds, setCommonPageIds] = useState(null);
    const [pages, setPages] = useState(null);
    const [loadPages, setLoadPages] = useState(true);

    useEffect(() => {
    const reloadPages = async () => {
      await pageClient.list().then((data) => {
        setPages(data);
      });

      await pageClient.getCommonPagesForLevel().then((data) => {
        setCommonPageIds(data);
      });

      setLoadPages(false);
    }

    if (loadPages === true) {
      reloadPages();
    }
    }, [loadPages]);

    var pagesContent =  {
        [CommonPageType.LOGIN_PAGE]: {
            title: 'Stránka pro přihlášení',
            description: 'Vyberte společnou přihlašovací stránku pro všechny sekce/úrovně.',
        },
        [CommonPageType.DASHBOARD_PAGE]: {
            title: 'Nástěnka',
            description: 'Vyberte společnou stránku po příhlášení tzn. nástěnku.',
        },
        [CommonPageType.TIME_LOCKED_PAGE]: {
            title: 'Stránka, když je sekce/úroveň časově uzamčena',
            description: 'Stránka, která se zobrazí, když obsah ještě nebyl odemčen. Úroveň musí mít povoleno časově omezené odemykání, aby byl uživatel přesměrován na tuto stránku.',
        },
    };

    const handleUpdateCommonPages = async (event) => {
        event.preventDefault();
        const form = event.target;

        const login = parseInt(form.querySelector('#' + CommonPageType.LOGIN_PAGE).value);
        const dashboard = parseInt(form.querySelector('#' + CommonPageType.DASHBOARD_PAGE).value);
        const timeLocked = parseInt(form.querySelector('#' + CommonPageType.TIME_LOCKED_PAGE).value);

        await pageClient.updateCommonPagesForLevel(login, dashboard, timeLocked);
        setLoadPages(true);
    }

    if (pages === null || commonPageIds === null) {
        return (<Loading height={'566px'}/>);
    }

    return (
        <form
            className="levels-content levels-common-pages"
            onSubmit={handleUpdateCommonPages}
        >
            {Object.entries(pagesContent).map(([key, pageContent]) => (
                <div key={key}>
                    <h4>{pageContent.title}</h4>
                    <p>{pageContent.description}</p>
                    <Select
                        id={key}
                        options={
                            pages.map((page) => ({
                                text: page.title,
                                value: page.id,
                            }))
                        }
                        defaultValue ={commonPageIds[key]}
                        big={true}
                    />
                    <div className="vertical-divider large-margin"/>
                </div>
            ))}

            <SubmitButton
                text={'Uložit'}
                show={!loadPages}
                centered={true}
                big={true}
            />
        </form>
    );
}

export default Common;
