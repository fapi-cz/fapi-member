import React, {useState} from 'react';
import {StringHelper} from "Helpers/StringHelper";

function HiddenText({value}) {
	const [hidden, setHidden] = useState(true);

    return (
		<span
			className='hidden-text'
		>
			{hidden
				? (<span className='hidden-text-value'>{StringHelper.stringToDots(value)}</span>)
				: (<code className='hidden-text-value'>{value}</code>)
			}
			<a
				className='hidden-text-toggle'
				onClick={() => {setHidden(!hidden)}}
				style={{marginLeft: '10px'}}
			>
				{hidden
					? ('Zobrazit')
					: ('Skrýt')
				}
			</a>
		</span>
    );
}

export default HiddenText;
