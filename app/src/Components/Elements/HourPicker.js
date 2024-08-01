import React, {useEffect, useState} from "react";

function HourPicker({id, onChange, defaultValue = 0}) {

     const [triggeredOnLoad, setTriggeredOnLoad] = useState(false);

    useEffect(() => {
        if (onChange && !triggeredOnLoad) {
          onChange({ target: {value: defaultValue}});
          setTriggeredOnLoad(true);
        }
    });

    return (
        <span className="hour-picker">
          <select
              id={id}
              name={id}
              className={'fm-select'}
              defaultValue={defaultValue}
              style={{display: 'inline-block'}}
              onChange={onChange}
              onLoad={onChange}
          >
              {[...Array(24).keys()].map(h => (
                  <option key={h} value={h}>
                    {`${h}:00`}
                  </option>
              ))}
          </select>
        </span>
    );
}

export default HourPicker;
