import 'Styles/overview.css';

import React, { useState} from 'react';
import {StringHelper} from "Helpers/StringHelper";
import PieChartWidget from "Components/Elements/PieChartWidget";
import StatisticsBar from "Components/Content/Overview/StatisticsBar";
import LevelOverview from "Components/Content/Overview/LevelOverview";
import ReturnLink from "Components/Elements/ReturnLink";
import MemberList from "Components/Content/Overview/MemberList";

function LevelsOverview({section, setActiveSection, countActiveUsersByLeveId, pagesByLevel}) {

    const [activeLevel, setActiveLevel] = useState(null);

    const levelsToChartData = () => {
         return section.levels.map((level) => {
            return {name: level.name, value: countActiveUsersByLeveId(level.id)};
         })
     }

     if (activeLevel === null) {
         return (
              <div className="content-levels-overview">
                  <ReturnLink action={() => {setActiveSection(null)}}/>
                  <h1 style={{marginBottom: '20px'}}>
                      <strong>
                          Členská sekce:
                      </strong>
                      {' ' + section.name}
                  </h1>
                  <StatisticsBar
                        columns={[
                            {label: 'Počet členů v sekci', value: countActiveUsersByLeveId(section.id)},
                            {label: 'Přiřazených stránek', value: pagesByLevel[section.id].length},
                            {label: 'Počet úrovní', value: section.levels.length},
                        ]}
                    />
                  <h2 style={{marginBottom: '10px'}}>Úrovně:</h2>

                  <div className='levels-overview' id='levels-overview'>
                        <div className='levels-overview-list'>
                             {section.levels.map((level) => (
                                 <div
                                     key={level.id}
                                     className='levels-overview-item clickable-option'
                                     onClick={() => {setActiveLevel(level)}}
                                 >
                                     <div
                                         className='color'
                                         style={
                                             {backgroundColor: StringHelper.stringToColor(level.name)}
                                         }
                                     />
                                     <span className='name'>{StringHelper.truncateText(level.name, 38)}</span>
                                 </div>
                             ))}
                         </div>
                        <PieChartWidget
                            data={levelsToChartData()}
                            title={'Počet členů'}
                        />
                  </div>
                  <MemberList
                    level={section}
                 />
              </div>
        );
     }

     return (
         <LevelOverview
             level={activeLevel}
             setActiveLevel={setActiveLevel}
             countActiveUsersByLeveId={countActiveUsersByLeveId}
             pagesByLevel={pagesByLevel}
         />
     );

}

export default LevelsOverview;
