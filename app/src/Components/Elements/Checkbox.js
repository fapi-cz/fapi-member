import React from "react";

function Checkbox({id, className, checked, small = false, onClick = () => {}}) {

    return (
        <div className="fm-checkbox">
          <input
              id={id}
              name={id}
              className={'fm-checkbox-input ' + className}
              type="checkbox"
              aria-hidden="true"
              defaultChecked={checked}
              onClick={onClick}
          />
          <label className={small ? 'small' : ''} htmlFor={id}></label>
        </div>
    );
}

export default Checkbox;
