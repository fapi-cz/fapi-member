import DateTime from "Models/DateTime";

export default class Membership
{
	levelId;
	userId
	registered;
	until;
	isUnlimited;

	constructor(data) {
		this.levelId = data.level;
		this.userId = data.user_id;
		this.registered = data.registered ? new DateTime(data.registered) : null;
		this.until = data.until ? new DateTime(data.until) : null;
		this.isUnlimited = data.is_unlimited ?? null;
	}
}
