import React from 'react';

function LevelsNavItem({text, selected, onClick}) {
  return (
    <label
        className={`levels-nav-item ${selected ? 'selected' : ''}`}
        onClick={onClick}
    >
      {text}
    </label>
  );
}

export default LevelsNavItem;
