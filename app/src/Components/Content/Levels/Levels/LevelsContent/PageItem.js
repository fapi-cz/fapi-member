import React from 'react';
import Checkbox from "Components/Elements/Checkbox";

function PageItem({page, checked}) {
    return (
        <div
           className="page-item"
           key={page.id}
        >
            <Checkbox
                id={'page_' + page.id + '_selected'}
                className='page-selected'
                checked={checked}
            />
            <label
                className="clickable-option"
                htmlFor={'page_' + page.id + '_selected'}
            >
                {page.title}
            </label>
            <div className="vertical-divider"></div>
        </div>
    )
}

export default PageItem;
