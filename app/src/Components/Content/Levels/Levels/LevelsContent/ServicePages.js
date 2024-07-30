import React, {useEffect, useState} from 'react';
import PageClient from "Clients/PageClient";
import {ServicePageType} from "Enums/ServicePageType";
import Select from "Components/Elements/Select";
import Loading from "Components/Elements/Loading";
import SubmitButton from "Components/Elements/SubmitButton";

function ServicePages({level}) {
    const pageClient = new PageClient();
    const [servicePageIds, setServicePageIds] = useState(null);
    const [pages, setPages] = useState(null);
    const [loadPages, setLoadPages] = useState(true);

    useEffect(() => {
        setServicePageIds(null);
        setLoadPages(true);
    }, [level.id])

    useEffect(() => {
    const reloadPages = async () => {
      await pageClient.list().then((data) => {
        setPages(data);
      });

      await pageClient.getServicePagesForLevel(level.id).then((data) => {
        setServicePageIds(data);
      });

      setLoadPages(false);
    }

    if (loadPages === true) {
      reloadPages();
    }
    }, [loadPages]);

    var pagesContent =  {
        [ServicePageType.LOGIN]: {
            title: 'Přihlašovací stránka',
            description: 'Vyberte stránku, kde je umístěn přihlašovací formulář. Stránka nesmí být zařazena jako členská.',
        },
        [ServicePageType.AFTER_LOGIN]: {
            title: 'Nástěnka',
            description: 'Vyberte stránku, která se zobrazí uživatelům po přihlášení do členské sekce nebo úrovně, tzn. nástěnka.',
        },
        [ServicePageType.NO_ACCESS]: {
            title: 'Stránka, když uživatel nemá přístup',
            description: 'Vyberte stránku, která se zobrazí uživateli, pokud nemá přístup na uzamčenou stránku. ' +
                'Stránka se většinou využívá pro výzvu ke koupi nebo prodloužení členství.',
        },
    };

    const handleUpdateServicePages = async (event) => {
        event.preventDefault();
        const form = event.target;

        const noAccess = parseInt(form.querySelector('#' + ServicePageType.NO_ACCESS).value);
        const login = parseInt(form.querySelector('#' + ServicePageType.LOGIN).value);
        const afterLogin = parseInt(form.querySelector('#' + ServicePageType.AFTER_LOGIN).value);

        await pageClient.updateServicePagesForLevel(level.id, noAccess, login, afterLogin);
        setLoadPages(true);
    }

    if (pages === null || servicePageIds === null) {
        return (<Loading height={'566px'}/>);
    }

    return (
        <form
            className="levels-content levels-service-pages"
            onSubmit={handleUpdateServicePages}
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
                        defaultValue ={servicePageIds[key]}
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

export default ServicePages;
