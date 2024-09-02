import React, {useEffect, useState} from 'react';
import Select from "Components/Elements/Select";

function MembersFilter({members, setFilteredMembers, levels, loadMembers}) {
    const [level, setLevel] = useState(null);
    const [search, setSearch] = useState(null);
    const [sortBy, setSortBy] = useState('email');
    const [order, setOrder] = useState('asc');

    useEffect(() => {
        var filteredMembers = members;

        if (level !== null) {
            filteredMembers = filterLevel(level, filteredMembers);
        }

        if (search !== null && search?.trim() !== '') {
            filteredMembers = filterSearch(search, filteredMembers);
        }

        filteredMembers = sortMembers(filteredMembers, sortBy, order);

        setFilteredMembers(filteredMembers);
    }, [level, search, sortBy, order, loadMembers]);

    const filterLevel = (level, filteredMembers) => {
        return filteredMembers.filter(member => member.levelIds?.includes(parseInt(level)));
    }

    const filterSearch = (search, filteredMembers) => {
        return filteredMembers.filter(
            member => (
                member.loginName.toLowerCase().trim().includes(search.toLowerCase().trim())
                || member.firstName?.toLowerCase()?.trim()?.includes(search.toLowerCase().trim())
                || member.lastName?.toLowerCase()?.trim()?.includes(search.toLowerCase().trim())
                || member.email?.toLowerCase()?.trim()?.includes(search.toLowerCase().trim())
            )
        );
    };

    function sortMembers(members, sortBy, order) {
        const orderValue = order === 'desc' ? -1 : 1;

        return [...members].sort((a, b) => {
          if (['email', 'loginName'].includes(sortBy)) {
              return (a[sortBy]?.localeCompare(b[sortBy]) * orderValue) ?? 0;
          } else if (sortBy === 'createDate') {
              return (a.createDate.getDate().localeCompare(b.createDate.getDate()) * orderValue) ?? 0;
          }

          return 0;
      });
    }

    return (
        <div
            className='members-filter table-filter'
        >
            <input
                className='fm-input'
                style={{width: '250px', backgroundColor: 'white'}}
                type='text'
                id='members-search'
                placeholder={'Jméno/Email'}
                onInputCapture={(e) => {setSearch(e.target.value)}}
            />
            <span className='filter-field'>
                <label>Sekce/Úroveň: </label>
                <Select
                    id={'member-level'}
                    options={levels.map(level => {
                        return {text: level.name, value: level.id};
                    })}
                    emptyText={'Vše'}
                    onChangeUpdateFunction={setLevel}
                />
            </span>
            <span className='filter-field'>
                <label>Seřadit: </label>
                <Select
                    id={'member-sort-by'}
                    options={[
                        {text: 'Uživatelské jméno', value: 'loginName'},
                        {text: 'Email', value: 'email'},
                        {text: 'Datum registrace', value: 'createDate'},
                    ]}
                    defaultValue={'name'}
                    onChangeUpdateFunction={setSortBy}
                    includeEmptyOption={false}
                />
                <Select
                    id={'member-order'}
                    options={[
                        {text: 'Vzestupně', value: 'asc'},
                        {text: 'Sestupně', value: 'desc'},
                    ]}s
                    defaultValue={'asc'}
                    onChangeUpdateFunction={setOrder}
                    includeEmptyOption={false}
                />
            </span>
        </div>
    )
}

export default MembersFilter;
