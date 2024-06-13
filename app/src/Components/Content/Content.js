import React, { Suspense } from 'react';

import Loading from 'Components/Elements/Loading';

function Content(props) {
	const Component = props.navigation
			.getNavItem(props.activeNavItem)
			.getSubNavItem(props.activeSubNavItem)
			.getComponent();

    return (
		 <div className="content">
            <Suspense fallback={<Loading/>}>
                {Component && <Component/>}
            </Suspense>
        </div>
    );
}

export default Content;
