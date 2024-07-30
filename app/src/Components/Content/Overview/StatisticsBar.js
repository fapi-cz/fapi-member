import 'Styles/overview.css';

import React, {useEffect, useState} from 'react';

function StatisticsBar({columns}) {

  return (
    <div>
        <div className='vertical-divider' style={{margin: '30px 0px'}}/>
        <div className='statistics-bar'>
            {columns.map((column) => (
                <div className='statistics-bar-item' key={column.label}>
                    <div className='label'>{column.label + ':'}</div>
                    <div className='value'>{column.value}</div>
                </div>
            ))}
        </div>
        <div className='vertical-divider' style={{margin: '30px 0px'}}/>
    </div>
  );
}

export default StatisticsBar;
