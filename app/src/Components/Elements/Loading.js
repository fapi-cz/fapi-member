import React from 'react';

function Loading({height = '100%', width = '100%'}) {

    return (
		<div
			className="loading"
			 style={{
				 height: height,
				 width: width,
			}}
		>
			<div className="loading-animation"></div>
		</div>
    );
}

export default Loading;
