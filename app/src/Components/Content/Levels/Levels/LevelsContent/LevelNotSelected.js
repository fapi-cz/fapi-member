import React from 'react';

function LevelNotSelected({onlyForLevels}) {
  return (
    <p className="levels-content level-not-selected">
        Vyberte prosím sekci{onlyForLevels ? null : '/úroveň'}
    </p>
  );
}

export default LevelNotSelected;
