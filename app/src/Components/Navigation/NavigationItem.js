import React from 'react';

function NavigationItem(props) {
  return (
    <li
        className={`navigation-item ${props.selected ? 'selected' : ''}`}
        onClick={props.onClick}
    >
      <span>{props.text}</span>
      <object type="image/svg+xml" data={props.icon}></object>
    </li>
  );
}

export default NavigationItem;
