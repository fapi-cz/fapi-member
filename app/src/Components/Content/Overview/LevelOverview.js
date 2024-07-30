import 'Styles/overview.css';

import React from 'react';
import StatisticsBar from "Components/Content/Overview/StatisticsBar";
import ReturnLink from "Components/Elements/ReturnLink";
import MemberList from "Components/Content/Overview/MemberList";

function LevelOverview({level, setActiveLevel, countActiveUsersByLeveId, pagesByLevel}) {
     return (
          <div className="content-levels-overview">
              <ReturnLink action={() => {setActiveLevel(null)}}/>
              <h1 style={{marginBottom: '20px'}}>
                  <strong>
                        Členská úroveň:
                  </strong>
                  {' ' + level.name}
              </h1>
              <StatisticsBar
                    columns={[
                        {label: 'Počet členů v úrovni', value: countActiveUsersByLeveId(level.id)},
                        {label: 'Přiřazených stránek', value: pagesByLevel[level.id].length},
                    ]}
                />
              <br/>
              <MemberList
                level={level}
             />
          </div>
    );
}

export default LevelOverview;
