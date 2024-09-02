import React, {useEffect, useState} from 'react';

import 'Styles/global.css';
import Alert from "Components/Elements/Alert";
import UserMembershipsForm from "Components/Elements/UserMembershipsForm";

function UserSettings() {
    const form = document.getElementById('your-profile');
    var userId = new URLSearchParams(window.location.search).get('user_id');

    return (
        <div className='fm-user-settings'>
            <Alert/>
            <h1>FAPI Member - Členské sekce</h1>
            <br/>
            <UserMembershipsForm
                userId={userId}
                form={form}
            />
        </div>
    );
}

export default UserSettings;
