import React, {useEffect, useState} from 'react';
import StatisticsFilter from "Components/Content/Overview/StatisticsFilter";
import StatisticsClient from "Clients/StatisticsClient";
import MemberSectionClient from "Clients/MemberSectionClient";
import Loading from "Components/Elements/Loading";
import StatisticsCharts from "Components/Content/Overview/StatisticsCharts";

function Statistics() {
    const statisticsClient = new StatisticsClient();
    const sectionClient = new MemberSectionClient();

    const [loadStatistics, setLoadStatistics] = useState(true);
    const [levels, setLevels] = useState(null);
    const [filterData, setFilterData] = useState(null);
    const [memberCounts, setMemberCounts] = useState(null);
    const [activeCounts, setActiveCounts] = useState(null);
    const [memberCountChanges, setMemberCountChanges] = useState(null);
    const [churnRates, setChurnRates] = useState(null);
    const [acquisitionRates, setAcquisitionRates] = useState(null);
    const [showingGroupedLevels, setShowingGroupedLevels] = useState(true);

    useEffect(() => {
        const reloadStatistics = async () => {
           await sectionClient.getAllAsLevels().then((data) => {
               setLevels(data);
           });

           if (filterData !== null) {
               statisticsClient.getMemberCountsForPeriod(filterData).then((data) => {
                   setMemberCounts(data);
               });
               statisticsClient.getMemberCountChangesForPeriod(filterData).then((data) => {
                   setMemberCountChanges(data);
               });
               statisticsClient.getChurnRate(filterData).then((data) => {
                   setChurnRates(data);
               });
               statisticsClient.getAcquisitionRate(filterData).then((data) => {
                   setAcquisitionRates(data);
               });
               statisticsClient.getActiveCountsForPeriod(filterData).then((data) => {
                   setActiveCounts(data);
               });
               setShowingGroupedLevels(filterData.group_levels);
           }
           setLoadStatistics(false);
        }

        if (loadStatistics) {
          reloadStatistics()
        }

    }, [loadStatistics]);

    if (levels === null) {
        return (<Loading/>);
    }

    return (
      <div className="content-statistics">
        <StatisticsFilter
            levels={levels}
            setFilterData={setFilterData}
            setLoadStatistics={setLoadStatistics}
            loadStatistics={loadStatistics}
            filterData={filterData}
        />
        <StatisticsCharts
            showingGroupedLevels={showingGroupedLevels}
            memberCounts={memberCounts}
            memberCountChanges={memberCountChanges}
            churnRates={churnRates}
            acquisitionRates={acquisitionRates}
            activeCounts={activeCounts}
            resetStatsToNull={() => {
                setMemberCounts(null);
                setMemberCountChanges(null);
                setChurnRates(null);
                setAcquisitionRates(null);
            }}
        />
      </div>

    );
}

export default Statistics;
