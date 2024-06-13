export class Navigation {
	navItems;

	constructor(navItems) {
		this.navItems = navItems;
	}

	getNavItems () {
		return this.navItems;
	}

	getNavItem (name) {
		const navItem = this.navItems.find(item => item.name === name);
    	return navItem || null;
	}
}
