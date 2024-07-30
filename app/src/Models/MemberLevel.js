export default class MemberLevel
{
	id;
	name;
	parentId;

	constructor(data) {
		this.id = data?.id ?? null;
		this.name = data?.name ?? null;
		this.parentId = data?.parent_id ?? null;
	}
}
