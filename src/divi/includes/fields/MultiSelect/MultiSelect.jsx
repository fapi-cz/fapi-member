// External Dependencies
import React, { Component, useState } from 'react';

class MultiSelect extends Component
{
  static slug = 'fmd_multi_select';

  render() {
    const options = Object.values(this.props.fieldDefinition.options);
    var selected = this.props.value;

    if (selected && selected !== '') {
      selected = Object.values(JSON.parse(selected));
    }
    const handleSelectChange = (value) => {
      let newSelected;
      if (selected.includes(value)) {
        newSelected = selected.filter((item) => item !== value);
      } else {
        newSelected = [...selected, value];
      }
      selected = newSelected;
      this.props._onChange(this.props.name, JSON.stringify(Object.assign({}, selected)));
    };

    return(
      <div className="multi-select">
          {options.map((option) => (
              <div key={option.id}>
                <input
                    style={{marginRight: '5px'}}
                    type="checkbox"
                    value={option.id}
                    defaultChecked={selected.includes(option.id)}
                    id={'fm-level-' + option.id}
                    name={'fm-level-' + option.id}
                    onChange={() => handleSelectChange(option.id)}
                />
                <label
                    htmlFor={'fm-level-' + option.id}
                >
                  {option.name}
                </label>
              </div>
          ))}
      </div>
    );
  }
}

export default MultiSelect;
