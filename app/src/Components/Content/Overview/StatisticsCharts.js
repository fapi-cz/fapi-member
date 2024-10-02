import React, {useEffect, useState} from 'react';
import Loading from "Components/Elements/Loading";
import BarChartWidget from "Components/Elements/BarChartWidget";
import LineChartWidget from "Components/Elements/LineChartWidget";

function StatisticsCharts({
    showingGroupedLevels,
    memberCounts,
    activeCounts,
    memberCountChanges,
    churnRates,
    acquisitionRates,
    resetStatsToNull,
}) {

    useEffect(() => {
        resetStatsToNull();
    }, [showingGroupedLevels]);


    if (memberCounts === null || memberCountChanges === null || acquisitionRates === null || churnRates === null || activeCounts === null) {
        return (<Loading height={'400px'}/>);
    }

    return (
      <div className="statistics-charts">
          <br/>
          <BarChartWidget
            data={memberCounts}
            colors={showingGroupedLevels ? ['#0074e2'] : null}
            title={'Počet členství'}
          />
          <LineChartWidget
            data={memberCountChanges}
            colors={showingGroupedLevels ? ['#aad20e', 'rgb(250, 83, 41)'] : null}
            title={'Vzniklých/zaniklých členství'}
          />
          <BarChartWidget
            data={activeCounts}
            colors={['#aad20e']}
            title={'Aktivních členů'}
          />
          {showingGroupedLevels
              ?(
                  <BarChartWidget
                    data={{
                        ...churnRates,
                        ...acquisitionRates,
                    }}
                    colors={showingGroupedLevels ? ['rgb(250, 83, 41)', '#aad20e'] : null}
                    isPercentage={true}
                  />
              ) : (
                  <div style={{display: 'grid', gap: '20px', gridTemplateColumns: 'auto auto'}}>
                      <BarChartWidget
                        data={churnRates}
                        colors={showingGroupedLevels ? ['rgb(250, 83, 41)'] : null}
                        title={'Churn rate'}
                        isPercentage={true}
                      />

                      <BarChartWidget
                        data={acquisitionRates}
                        colors={showingGroupedLevels ? ['#aad20e'] : null}
                        title={'Acquisition rate'}
                        isPercentage={true}
                      />
                  </div>
              )
          }

          <div className='fm-chart no-data' style={{width: '100%'}}>
              Data vygenerovaná před stažením FAPI Member verze 2.2.0 nejsou přesná.
          </div>
      </div>
    );
}

export default StatisticsCharts;
