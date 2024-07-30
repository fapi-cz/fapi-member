import React from 'react';
import {PieChart, Pie, Cell, Tooltip, Legend} from 'recharts';
import {StringHelper} from "Helpers/StringHelper";

function PieChartWidget({data, size = 400, title=''}) {
	var totalSum = 0;

	data.forEach((dataPoint) => {
		totalSum += dataPoint.value;
	})

	if (totalSum === 0) {
		return (<div className='no-data'>Žádná data</div>)
	}

    return (
		<div>
			<h4 style={{textAlign: 'center'}}>{title}</h4>
			<PieChart width={size} height={size}>
				<Pie
				  data={data}
				  cx={size/2}
				  cy={size/2}
				  outerRadius={(size/2) - 20}
				  fill='#8884d8'
				  dataKey='value'
				  legendType='none'
				  animationBegin={0}
				  animationDuration={500}
				  animationEasing="ease-out"
				>
				  {data.map((entry, index) => (
					<Cell
						key={`cell-${index}`}
						fill={StringHelper.stringToColor(entry.name)}
					/>
				  ))}
				</Pie>
				<Tooltip/>
			</PieChart>
		</div>
    );
}

export default PieChartWidget;
