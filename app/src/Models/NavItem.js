export class NavItem {
	name;
	label;
	icon;
	subNavItems;

	constructor(name, label, icon, subNavItems) {
		this.name = name;
		this.label = label;
		this.icon = icon;
		this.subNavItems = subNavItems;
	}

	getSubNavItems () {
		return this.subNavItems;
	}

	getSubNavItem (name) {
		const subNavItem = this.subNavItems.find(item => item.name === name);
    	return subNavItem || null;
	}

}
