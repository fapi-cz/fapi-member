import React from "react";

function Select({
    id,
    options,
    emptyText = '-- nevybráno --',
    defaultValue = null,
    big = false,
    includeEmptyOption= true,
    onChangeUpdateFunction = (value) => {},
}) {
    return (
        <select
            className={'fm-select ' + (big ? 'big' : '')}
            id={id}
            name={id}
            defaultValue={defaultValue}
            onChange={(e) => {
                onChangeUpdateFunction(e.target.value === 'null' ? null : e.target.value)
            }}
        >
            {includeEmptyOption
                ? (<option className="fm-option" value={'null'}>{emptyText}</option>)
                : null
            }
            {options.map((option) => (
                <option
                    key={option.value}
                    className='fm-option'
                    value={option.value}
                >
                    {option.text}
                </option>
            ))}
        </select>
    );
}

export default Select;
