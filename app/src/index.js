import React from 'react';
import {createRoot} from 'react-dom/client';
import Settings from './Components/Settings';
import UserSettings from './Components/UserSettings';
import Alert from "Components/Elements/Alert";

document.addEventListener( 'DOMContentLoaded', function() {
    window.environmentData = environmentData;

    var settingsContainer = document.getElementById( 'fm-settings' );
    if( typeof settingsContainer !== 'undefined' && settingsContainer !== null ) {
        const root = createRoot(settingsContainer);
        root.render(<Settings />);
    }

    var userSettingsContainer = document.getElementById( 'fm-user-settings' );
    if( typeof userSettingsContainer !== 'undefined' && userSettingsContainer !== null ) {
        const root = createRoot(userSettingsContainer);
        root.render(<UserSettings />);
    }
})
