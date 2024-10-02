import React from 'react';
import {BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer} from 'recharts';
import { StringHelper } from 'Helpers/StringHelper';

function BarChartWidget({data, keys = [], width = '100%', height = 400, title = '', colors = null, isPercentage = false}) {
  let totalSum = 0;

  Object.values(data).forEach((dataPoint) => {
      Object.keys(dataPoint).forEach((key) => {
          keys[key] = key;
      })
  });

  keys = Object.keys(keys)

  data = Object.entries(data).map(([date, value]) => {
      return {
          'fm-date': date,
          ...value
      };
  });

  data.forEach((dataPoint) => {
    keys.forEach((key) => {
      totalSum += dataPoint[key] || 0;
    });
  });

  if (totalSum === 0 || data === null) {
    return <div className='fm-chart no-data' style={{width: width}}>Žádná data</div>;
  }

  return (
    <div className={'fm-chart'} style={{width: width}}>
        <h4 style={{ textAlign: 'center' }}>{title}</h4>
        <ResponsiveContainer width={'100%'} height={height}>
          <BarChart
            data={data}
            margin={{
              top: 20, right: 30, left: 20, bottom: 5,
            }}
          >
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="fm-date"  tickFomatter={(value) => value.toFixed(2)}/>
            <YAxis tickFormatter={ isPercentage ? (value) => `${(value).toFixed(0)}%` : (value) => `${(value)}`}/>
            <Tooltip/>
            <Legend />
            {keys.map((key, index) => (
                  <Bar
                    key={key}
                    dataKey={key}
                    stackId="a"
                    fill={(colors === null ? StringHelper.stringToColor(key) : colors[index])}
                    animationBegin={0}
                    animationDuration={500}
                    animationEasing="ease-out"
                  />
                ))
            }
          </BarChart>
        </ResponsiveContainer>
    </div>
  );
}

export default BarChartWidget;
