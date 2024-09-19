import React, { useEffect } from 'react';
import statsExample from 'Images/stats-example.png';

const NoFmLicence = () => {
    return (
        <div className="fm-no-licence">
            <a className={"fm-link-button"} target="_blank" href={'https://page.fapi.cz/10559//fapi-member-pro'}>Získat FAPI Member Pro</a>
            <div className={"image"} style={{backgroundImage: `url(${statsExample})`}}>
            </div>
        </div>
    );
};

export default NoFmLicence;
