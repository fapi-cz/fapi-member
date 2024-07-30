import React from 'react';
import {StringHelper} from "Helpers/StringHelper";

function LevelsListLevel({
    level,
    activeLevel,
    setActiveLevel,
    settingsIcon,
    setSettingsWindow,
}) {
    const selected = activeLevel?.id === level.id
        ? 'selected'
        : '';

    return (
    <li className={"level " + selected} id={'level-' + level.id}>
        <div className="level-margin-line"></div>
        <label
            className="level-name"
            onClick={() => setActiveLevel(level)}
        >
            {StringHelper.truncateText(level.name, 29)}
        </label>
        <img
            src={settingsIcon}
            className="level-settings settings-icon clickable-icon"
             onClick={(event) => {setSettingsWindow({
                 level: level,
                 clickEvent: event,
             })}}
        />
    </li>
    );
}

export default LevelsListLevel;
