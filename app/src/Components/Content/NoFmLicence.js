import React, {useEffect, useState} from 'react';
import statsExample from 'Images/stats-example.png';
import Loading from "Components/Elements/Loading";
import ApiConnectionClient from "Clients/ApiConnectionClient";


const NoFmLicence = () => {
    const [loading, setLoading] = useState(true);
    const [urlObject, setUrlObject] = useState(null);

    const connectionClient = new ApiConnectionClient();

    useEffect(() => {
        const fetchInitialData = async () => {
            const connections = await connectionClient.list();
            const connection = connections[0] || {};
            const urlObject = new URL('https://page.fapi.cz/10559/fapi-member-pro');

            const data = {
                "fapi-form-email": connection.billing?.email,
                "fapi-form-mobil": connection.billing?.phone,
                "fapi-form-company": connection.billing?.name,
                "fapi-form-ic": connection.billing?.ic,
                "fapi-form-dic": connection.billing?.dic,
                "fapi-form-ic-dph": connection.billing?.['ic_dph'],
                "fapi-form-street": connection.billing?.address?.street,
                "fapi-form-city": connection.billing?.address?.city,
                "fapi-form-postcode": connection.billing?.address?.zip,
                "fapi-form-state": connection.billing?.address?.country,
            };

            const jsonData = JSON.stringify(data);
            const base64EncodedData = btoa(encodeURIComponent(jsonData));
            urlObject.search += `fapi-form-data=${base64EncodedData}`;

            setUrlObject(urlObject);
            setLoading(false);
        };

        if (loading) {
            fetchInitialData();
        }
    }, [loading]);

    if (loading === null) {
        return <Loading/>;
    }

    return (
        <div className="fm-no-licence" style={{backgroundImage: `url(${statsExample})`}}>
            <div className={"blur-filter"} >
                <a className={"fm-link-button"} target="_blank" href={urlObject}>
                    Získat FAPI Member Pro
                </a>
            </div>
        </div>
    );
};

export default NoFmLicence;
