export default class Page {
	id;
	title;

	constructor(data) {
		this.id = data?.id ?? null;
		this.title = data?.title ?? null;
		this.type = data?.type ?? null;
	}
}
