import NavigationItem from "./NavigationItem";
import React from "react";

function Navigation(props) {

    return (
		<ul className="navigation">
			{props.navigation.getNavItems().map(item => (
				<NavigationItem
					key={item.name}
					onClick={() => props.onNavItemChange(item.name, item.name)}
					text={item.label}
					name={item.name}
					icon={item.icon}
					selected={item.name === props.activeNavItem}
				/>
			))}
		</ul>
    );
}

export default Navigation;
