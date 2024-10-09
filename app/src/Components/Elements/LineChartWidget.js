import React from 'react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import { StringHelper } from 'Helpers/StringHelper';
import Loading from "Components/Elements/Loading";

function LineChartWidget({ data, keys = [], width = '100%', height = 400, title = '', colors = null }) {
  let totalSum = 0;

   if (data === null) {
        return (
            <div className='fm-chart' style={{width: '100%'}}>
                <Loading height={height + 'px'}/>
            </div>
        );
   }

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

  if (totalSum === 0) {
    return <div className='fm-chart no-data' style={{width: width}}>Žádná data</div>;
  }

  return (
    <div className={'fm-chart'} style={{width: width}}>
      <h4 style={{ textAlign: 'center' }}>{title}</h4>
      <ResponsiveContainer width={'100%'} height={height}>
        <LineChart
          data={data}
          margin={{
            top: 20, right: 30, left: 20, bottom: 5,
          }}
        >
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis dataKey="fm-date" />
          <YAxis />
          <Tooltip />
          <Legend />
          {keys.map((key, index) => (
            <Line
              key={key}
              type="monotone"
              dataKey={key}
              stroke={colors === null ? StringHelper.stringToColor(key) : colors[index]}
              strokeWidth={2.5}
              animationBegin={0}
              animationDuration={500}
              animationEasing="ease-out"
            />
          ))}
        </LineChart>
      </ResponsiveContainer>
    </div>
  );
}

export default LineChartWidget;
