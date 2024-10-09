import React, {useEffect, useState} from 'react';
import Loading from "Components/Elements/Loading";
import BarChartWidget from "Components/Elements/BarChartWidget";
import LineChartWidget from "Components/Elements/LineChartWidget";

function StatisticsCharts({
    filterData,
    showingGroupedLevels,
    memberCounts,
    activeCounts,
    memberCountChanges,
    churnRates,
    acquisitionRates,
    averageChurnRatePeriods,
    resetStatsToNull,
}) {

    useEffect(() => {
        resetStatsToNull();
    }, [showingGroupedLevels]);


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

          <BarChartWidget
            data={averageChurnRatePeriods}
            colors={showingGroupedLevels ? [ 'rgb(250, 83, 41)'] : null}
            title={'Churn rate od data registrace'}
            isPercentage={true}
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
                    title={
                      'Churn/Acquisition rate' + (filterData !== null
                            ? ' - od ' + filterData.date_from + ' do ' + filterData.date_to
                            : ''
                    )}
                  />
              ) : (
                  <div style={{display: 'grid', gap: '20px', gridTemplateColumns: 'auto auto'}}>
                      <BarChartWidget
                        data={churnRates}
                        colors={showingGroupedLevels ? ['rgb(250, 83, 41)'] : null}
                        isPercentage={true}
                        title={
                          'Churn rate' + (filterData !== null
                                ? ' - od ' + filterData.date_from + ' do ' + filterData.date_to
                                : ''
                        )}
                      />

                      <BarChartWidget
                        data={acquisitionRates}
                        colors={showingGroupedLevels ? ['#aad20e'] : null}
                        isPercentage={true}
                        title={
                          'Acquisition rate' + (filterData !== null
                                ? ' - od ' + filterData.date_from + ' do ' + filterData.date_to
                                : ''
                        )}
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
