import React from 'react';
import SubNavigationItem from "./SubNavigationItem";

function SubNavigation(props) {
    return (
		<ul className="sub-navigation">
			{props.navigation.getNavItem(props.activeNavItem).getSubNavItems().map(item => (
				<SubNavigationItem
					key={item.name}
					onClick={() => props.onSubNavItemChange(props.activeNavItem, item.name)}
					text={item.label}
					selected={item.name === props.activeSubNavItem}
				/>
			))}
		</ul>
    );
}

export default SubNavigation;
