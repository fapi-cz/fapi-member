import React from 'react';

function SubNavigationItem(props) {

  return (
    <li
        className={`sub-navigation-item ${props.selected ? 'selected' : ''}`}
        onClick={props.onClick}
    >
      {props.text}
    </li>
  );
}

export default SubNavigationItem;
