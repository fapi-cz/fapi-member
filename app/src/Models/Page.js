export default class Page {
	id;
	title;
	type;
	url;

	constructor(data) {
		this.id = data?.id ?? null;
		this.title = data?.title ?? null;
		this.type = data?.type ?? null;
		this.url = data?.url ?? null;
	}
}
