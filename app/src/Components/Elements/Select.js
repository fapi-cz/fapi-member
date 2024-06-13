import React from "react";
import Loading from "Components/Elements/Loading";

function Select({id, options, emptyText = '-- nevybráno --', defaultValue = null,big = false}) {
    return (
        <select
            className={'fm-select ' + (big ? 'big' : '')}
            id={id}
            name={id}
            defaultValue={defaultValue}
        >
            <option className="fm-option" value={null}>{emptyText}</option>
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
