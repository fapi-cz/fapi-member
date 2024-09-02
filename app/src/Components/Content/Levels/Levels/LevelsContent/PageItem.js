import React from 'react';
import Checkbox from "Components/Elements/Checkbox";
import check from 'Images/check.svg';
import cross from 'Images/cross.svg';

function PageItem({page, assigned, hidden}) {
    const typeLabels = {
        post: 'Příspěvek',
        page: 'Stránka',
        cpt: 'CPT',
    };

    return (
        <tr
           className="page-item"
           key={page.id}
           style={{display: hidden ? 'none' : 'table-row'}}
        >
            <td>
                <Checkbox
                    id={'page_' + page.id + '_selected'}
                    className='page-selected'
                    checked={assigned}
                />
            </td>
            <td>
                <label
                    className="clickable-option"
                    htmlFor={'page_' + page.id + '_selected'}
                >
                    {page.title}
                </label>
            </td>
            <td>
                {(page.url
                    ? <a href={window.location.origin + page.url}>{page.url}</a>
                    : ''
                )}
            </td>
            <td>
                {typeLabels[page.type]}
            </td>
            <td>
                <img src={assigned ? check : cross}/>
            </td>
        </tr>
    )
}

export default PageItem;
