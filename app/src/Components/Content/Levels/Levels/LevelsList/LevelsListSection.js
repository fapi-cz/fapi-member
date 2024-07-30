import React from 'react';

import LevelsListLevel from './LevelsListLevel';
import {StringHelper} from "Helpers/StringHelper";

function LevelsListSection({
    section,
    activeLevel,
    setActiveLevel,
    setSettingsWindow,
    setAddLevelData,
    settingsIcon,
    folderIcon,
    addIcon,
}) {

    const selected = activeLevel?.id === section.id
        ? 'selected'
        : '';

    return (
        <div className={'section ' + selected} id={'section-' + section.id}>
            <div className="section-name-container">
                <object className="folder-image" type="image/svg+xml" data={folderIcon}></object>
                <label
                    className="section-name"
                    onClick={() => setActiveLevel(section)}
                >
                    {StringHelper.truncateText(section.name, 31)}
                </label>
                <img
                    src={settingsIcon}
                    className="section-settings settings-icon clickable-icon"
                     onClick={(event) => {setSettingsWindow({
                         level: section,
                         clickEvent: event,
                     })}}
                />
            </div>
            <ul className="levels">
                {section.levels.map(level => (
                    <LevelsListLevel
                        key={level.id}
                        level={level}
                        activeLevel={activeLevel}
                        setActiveLevel={setActiveLevel}
                        settingsIcon={settingsIcon}
                        setSettingsWindow={setSettingsWindow}
                    ></LevelsListLevel>
                ))}
            </ul>
            <img
                src={addIcon}
                className="add-icon clickable-icon"
                onClick={(event) => {setAddLevelData({
                    parent: section,
                    clickEvent: event,
                })}}
            />
        </div>
    );
}

export default LevelsListSection;
