import React from 'react';

function ReturnLink({action}) {

	return (
		<a
		  className='return-link'
		  onClick={action}
		>
		  ←Zpět
		</a>
	);
}


export default ReturnLink;
