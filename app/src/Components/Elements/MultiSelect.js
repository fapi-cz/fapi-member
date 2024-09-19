import React from "react";
import Select from 'react-select';

function MultiSelect({
    id,
    options,
    emptyText = '-- nevybráno --',
    defaultValue = [],
    big = false,
    includeEmptyOption= true,
    onChangeUpdateFunction = (value) => {},
}) {
    return (
        <Select
            isMulti
            className={'fm-select ' + (big ? 'big' : '')}
            id={id}
            name={id}
            defaultValue={defaultValue}
            placeholder={emptyText}
            options={options}
            onChange={(values)=> {onChangeUpdateFunction(
                values.map((value) => {
                    return value.value;
                }
             ))}}
        />
    );
}

export default MultiSelect;
