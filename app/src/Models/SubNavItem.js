export class SubNavItem {
	name;
	label;
	component;

	constructor(name, label, component) {
		this.name = name;
		this.label = label;
		this.component = component;
	}

	getComponent () {
		return this.component;
	}
}
