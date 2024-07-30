import React, {useState, useEffect} from 'react';

import 'Styles/levels.css';

import LevelList from './Levels/LevelsList/LevelsList';
import LevelsNav from './Levels/LevelsNav';
import Loading from "Components/Elements/Loading";
import LevelsListSettings from "Components/Popups/LevelsListSettings";
import {LevelsNavItemType} from "Enums/LevelsNavItemType";
import MemberSectionClient from "Clients/MemberSectionClient";
import PopupWindow from "Components/Popups/PopupWindow";
import EmailsSymbolTable from "Components/Content/Levels/Levels/LevelsContent/EmailsSymbolTable";

function Levels() {
    const [activeLevelsNavItem, setActiveLevelsNavItem] = useState(LevelsNavItemType.PAGES);
    const [activeLevel, setActiveLevel] = useState(null);
    const [settingsWindow, setSettingsWindow] = useState(null);
    const [sectionsLoading, setSectionsLoading] = useState(true);
    const [sections, setSections] = useState(null);


    const contents = {
        [LevelsNavItemType.PAGES]: React.lazy(() => import('Components/Content/Levels/Levels/LevelsContent/Pages')),
        [LevelsNavItemType.EMAILS]: React.lazy(() => import('Components/Content/Levels/Levels/LevelsContent/Emails')),
        [LevelsNavItemType.SERVICE_PAGES]: React.lazy(() => import('Components/Content/Levels/Levels/LevelsContent/ServicePages')),
        [LevelsNavItemType.UNLOCKING]: React.lazy(() => import('Components/Content/Levels/Levels/LevelsContent/Unlocking')),
    };

    var Component = React.lazy(() => import('Components/Content/Levels/Levels/LevelsContent/LevelNotSelected'));

    if(activeLevel !== null) {
        Component = contents[activeLevelsNavItem];
    }

    const memberSectionClient = new MemberSectionClient();

     useEffect(() => {
         const reloadSectionsData = async () => {
           await memberSectionClient.getAll().then((updatedSections) => {
               setSectionsLoading(false);
               setSections(updatedSections);
               updateActiveNavItemFromUrl();
               updateActiveLevelFromUrl(updatedSections);
           });
        }

        if(sectionsLoading === true) {
           reloadSectionsData();
        }
     }, [sectionsLoading]);

     const reloadSections = () => {
         setSectionsLoading(true);
    }

    const updateActiveLevelFromUrl = (updatedSections = sections) => {
         if (updatedSections === null) {
             return;
         }

        var url = new URL(window.location.href);
        var levelId = parseInt(url.searchParams.get('level'));
        var activeLevel = null;

        if (levelId === null) {
            return levelId;
        }

        updatedSections.forEach((section) => {
            if (section.id === levelId) {
                activeLevel = section;
                return;
            }
            if (section.levels !== null) {
                section.levels.forEach((level) => {
                    if (level.id === levelId) {
                        activeLevel = level;
                    }
                });
            }
        });

        setActiveLevel(activeLevel);
    }

    const handleSetActiveLevel = (level) => {
         var url = new URL(window.location.href);
         url.searchParams.set('level', level.id);
         window.history.pushState({level: level.id}, document.title, url);

         updateActiveLevelFromUrl();
    }

    const updateActiveNavItemFromUrl = () => {
        var url = new URL(window.location.href);
        var navItem =  url.searchParams.get('fm-levels-page');

        if (navItem == null) {
            navItem = LevelsNavItemType.PAGES;
        }

        setActiveLevelsNavItem(navItem);
    }

    const handleSetActiveNavItem = (navItem) => {
         var url = new URL(window.location.href);
         url.searchParams.set('fm-levels-page', navItem);
         window.history.pushState({fmLevelsPage: navItem}, document.title, url);

         updateActiveNavItemFromUrl();
    }

    window.addEventListener('popstate', () => {
        updateActiveLevelFromUrl();
        updateActiveNavItemFromUrl();
    }, false);

  return (
    <div className="content-levels">
        <div className="levels-list-header">
            <span>Členské sekce/úrovně</span>
        </div>

        <LevelList
            sections={sections}
            activeLevel={activeLevel}
            setActiveLevel={handleSetActiveLevel}
            setSettingsWindow={setSettingsWindow}
            reloadSections={reloadSections}
        />

        <LevelsNav
            activeItem={activeLevelsNavItem}
            setActiveItem={handleSetActiveNavItem}
        />

        <React.Suspense fallback={
            <div className="levels-content">
                <Loading/>
            </div>
        }>
            {activeLevel === null
                ? (
                    <Component
                        onlyForLevels={LevelsNavItemType.UNLOCKING === activeLevelsNavItem}
                    />
                ) : (
                    <Component
                        level={activeLevel}
                    />
                )
            }


            {LevelsNavItemType.EMAILS === activeLevelsNavItem
                ? <EmailsSymbolTable/>
                : ''
            }
        </React.Suspense>

        <PopupWindow
            clickEvent={settingsWindow?.clickEvent}
            Component={LevelsListSettings}
            componentData={{
                level: settingsWindow?.level,
                reloadSections: reloadSections,
            }}
            onClose={() => {setSettingsWindow(null)}}
        />
    </div>
  );
}

export default Levels;
