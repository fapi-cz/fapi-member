import React from 'react';
import {createRoot} from 'react-dom/client';
import Settings from './Components/Settings';
import UserSettings from './Components/UserSettings';

document.addEventListener( 'DOMContentLoaded', function() {
    window.environmentData = environmentData;

    var settingsContainer = document.getElementById( 'fm-settings' );
    if( typeof settingsContainer !== 'undefined' && settingsContainer !== null ) {
        const root = createRoot(settingsContainer);
        root.render(<Settings />);
    }
})
