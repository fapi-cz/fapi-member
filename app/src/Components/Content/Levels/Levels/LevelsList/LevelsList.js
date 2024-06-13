import React, {useState} from 'react';

import LevelsListSection from './LevelsListSection';
import Loading from 'Components/Elements/Loading';
import NewLevel from "Components/Popups/NewLevel";
import PopupWindow from "Components/Popups/PopupWindow";

import folderIcon from 'Images/folder.svg';
import settingsIcon from 'Images/settings.svg';
import addIcon from 'Images/add-filled.svg'

function LevelsList({
    sections,
    activeLevel,
    setActiveLevel,
    setSettingsWindow,
    reloadSections,
}) {
    const [addLevelData, setAddLevelData] = useState(null);
    var levelsList = null;

    if (sections !== null) {
       levelsList = sections.map(section => (
            <LevelsListSection
                key={section.id}
                section={section}
                activeLevel={activeLevel}
                setActiveLevel={setActiveLevel}
                setSettingsWindow={setSettingsWindow}
                setAddLevelData={setAddLevelData}
                settingsIcon={settingsIcon}
                folderIcon={folderIcon}
                addIcon={addIcon}
            />
        ));

        levelsList.push(
           <img
                key="addIcon"
                src={addIcon}
                className="add-icon clickable-icon"
                onClick={(event) => {setAddLevelData({
                    parent: null,
                    clickEvent: event,
                })}}
            />
       );
    } else {
        levelsList = <Loading/>
    }

    return (
        <div className="levels-list">
            {levelsList}

            <PopupWindow
                clickEvent={addLevelData?.clickEvent}
                Component={NewLevel}
                componentData={{
                    parent: addLevelData?.parent,
                    reloadSections: reloadSections,
                }}
                onClose={() => {setAddLevelData(null)}}
            />
        </div>
    );
}

export default LevelsList;
