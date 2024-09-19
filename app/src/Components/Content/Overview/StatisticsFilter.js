import React, {useEffect, useState} from 'react';
import Checkbox from "Components/Elements/Checkbox";
import MultiSelect from "Components/Elements/MultiSelect";
import SubmitButton from "Components/Elements/SubmitButton";
import DateTimeHelper from "Helpers/DateTimeHelper";
import DateTime from "Models/DateTime";

function StatisticsFilter({levels, setFilterData, setLoadStatistics, loadStatistics, filterData}) {
    const [selectedLevelIds, setSelectedLevelIds] = useState(null);
    const [dateFrom, setDateFrom] = useState(DateTimeHelper.addDaysToDateTime(
        DateTimeHelper.getCurrentDateTime(),
        -30,
    ));
    const [dateTo, setDateTo] = useState(DateTimeHelper.getCurrentDateTime());
    const [groupLevels, setGroupLevels] = useState(true);

    const handleSubmitFilter = () => {
        setFilterData({
            date_from: dateFrom.getDate(),
            date_to: dateTo.getDate(),
            level_ids: selectedLevelIds,
            group_levels: groupLevels,
        });

        setLoadStatistics(true);
    }

    if (filterData === null) {
        handleSubmitFilter();
    }


    return (
        <div
            className='statistics-filter table-filter'
        >
            <span className='filter-field'>
                <label>Od</label>
                <input
                    className='fm-input'
                    type={'date'}
                    id={'date-from'}
                    defaultValue={dateFrom.getDate()}
                    onChange={(e) => {
                        setDateFrom(new DateTime(e.target.value));
                    }}
                />
            </span>
            <span className='filter-field'>
                <label>Do</label>
                <input
                    className='fm-input'
                    type={'date'}
                    id={'date-to'}
                    defaultValue={dateTo.getDate()}
                    onChange={(e) => {
                        setDateTo(new DateTime(e.target.value));
                    }}
                />
            </span>
            <span className='filter-field'>
                <label>Sekce/Úrovně: </label>
                <MultiSelect
                    id={'statistics-levels'}
                    options={levels.map(level => {
                        return {label: level.name, value: level.id};
                    })}
                    emptyText={'Vše'}
                    onChangeUpdateFunction={(ids) => {setSelectedLevelIds(ids)}}
                />
            </span>
            <span className='filter-field'>
                <label>Sloučit sekce/úrovně </label>
                <Checkbox
                    id={'group-levels'}
                    onClick={(e) => {setGroupLevels(e.target.checked)}}
                    checked={true}
                />
            </span>
            <span className='filter-field'>
                <SubmitButton
                    id={'filter-submit'}
                    text={'Filtrovat'}
                    onClick={handleSubmitFilter}
                    show={!loadStatistics}
                />
            </span>
        </div>
    )
}

export default StatisticsFilter;
