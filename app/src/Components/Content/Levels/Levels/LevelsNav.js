import React from 'react';
import LevelsNavItem from "Components/Content/Levels/Levels/LevelsNavItem";
import {LevelsNavItemType} from "Enums/LevelsNavItemType";

function LevelsNav({activeItem, setActiveItem}) {
  return (
    <div className="levels-nav">
       { [
         {text: 'Přiřazené stránky a přízpěvky', key: LevelsNavItemType.PAGES},
         {text: 'E-maily', key: LevelsNavItemType.EMAILS},
         {text: 'Servisní stránky', key: LevelsNavItemType.SERVICE_PAGES},
         {text: 'Uvolňování obsahu', key: LevelsNavItemType.UNLOCKING},
       ].map(item => (
            <LevelsNavItem
                key={item.key}
                text={item.text}
                selected={item.key === activeItem}
                onClick={() => {setActiveItem(item.key)}}
            />
        ))}
    </div>
  );
}

export default LevelsNav;
