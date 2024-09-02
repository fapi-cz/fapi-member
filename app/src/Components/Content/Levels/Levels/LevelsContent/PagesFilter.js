import React, {useEffect, useState} from 'react';
import Select from "Components/Elements/Select";

function PagesFilter({pages, setFilteredPages, assignedPageIds, loadPages}) {
    const [type, setType] = useState(null);
    const [assigned, setAssigned] = useState(null);
    const [search, setSearch] = useState(null);
    const [sortBy, setSortBy] = useState('assigned');
    const [order, setOrder] = useState('asc');

    useEffect(() => {
        var filteredPages = pages;

        if (type !== null) {
            filteredPages = filterType(type, filteredPages);
        }

        if (assigned !== null) {
            filteredPages = filterAssigned(assigned, filteredPages);
        }

        if (search !== null && search?.trim() !== '') {
            filteredPages = filterSearch(search, filteredPages);
        }

        filteredPages = sortPages(filteredPages, sortBy, order);

        setFilteredPages(filteredPages);
    }, [type, assigned, search, sortBy, order, loadPages]);

    const filterType = (type, filteredPages) => {
        return filteredPages.filter(page => page.type === type);
    }

    const filterAssigned = (assigned, filteredPages) => {
        return filteredPages.filter(page => assignedPageIds.includes(page.id) === (parseInt(assigned) !== 0));
    }

    const filterSearch = (search, filteredPages) => {
        return filteredPages.filter(
            page => (page.title.toLowerCase().trim().includes(search.toLowerCase().trim())
            || page.url?.toLowerCase()?.trim()?.includes(search.toLowerCase().trim()))
        );
    };

    function sortPages(pages, sortBy, order) {
        const orderValue = order === 'desc' ? -1 : 1;

        return [...pages].sort((a, b) => {
          if (sortBy === 'assigned') {
              const sortA = assignedPageIds.includes(a.id) ? (-1 * orderValue)  : orderValue;
              const sortB = assignedPageIds.includes(b.id) ? (-1 * orderValue)  : orderValue;

              return sortA === sortB ? 0 : sortA;
          }

          if (['url', 'title'].includes(sortBy)) {
              return (a[sortBy]?.localeCompare(b[sortBy]) * orderValue) ?? 0;
          }

          return 0;
      });
    }

    return (
        <div
            className='pages-filter table-filter'
        >
            <input
                className='fm-input'
                style={{width: '250px', backgroundColor: 'white'}}
                type='text'
                id='page-search'
                placeholder={'Název/URL'}
                onInputCapture={(e) => {setSearch(e.target.value)}}
            />
            <span className='filter-field'>
                <label>Typ: </label>
                <Select
                    id={'page-type'}
                    options={[
                        {text: 'Stránka', value: 'page'},
                        {text: 'Příspěvek', value: 'post'},
                        {text: 'CPT', value: 'cpt'},
                    ]}
                    emptyText={'Vše'}
                    onChangeUpdateFunction={setType}
                />
            </span>
            <span className='filter-field'>
                <label>Přiřazení: </label>
                <Select
                    id={'page-assigned'}
                    options={[
                        {text: 'Přiřazeno', value: 1},
                        {text: 'Nepřiřazeno', value: 0},
                    ]}
                    emptyText={'Vše'}
                    onChangeUpdateFunction={setAssigned}
                />
            </span>
            <span className='filter-field'>
                <label>Seřadit: </label>
                <Select
                    id={'page-sort-by'}
                    options={[
                        {text: 'Přiřazení', value: 'assigned'},
                        {text: 'Název', value: 'title'},
                        {text: 'URL', value: 'url'},
                    ]}
                    defaultValue={'assigned'}
                    onChangeUpdateFunction={setSortBy}
                    includeEmptyOption={false}
                />
                <Select
                    id={'page-order'}
                    options={[
                        {text: 'Vzestupně', value: 'asc'},
                        {text: 'Sestupně', value: 'desc'},
                    ]}
                    defaultValue={'asc'}
                    onChangeUpdateFunction={setOrder}
                    includeEmptyOption={false}
                />
            </span>
        </div>
    )
}

export default PagesFilter;
