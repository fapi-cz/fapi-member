import DateTime from "Models/DateTime";
import MemberLevel from "Models/MemberLevel";

export default class MembershipChange
{
	level;
	userId;
	type;
	registered;
	until;
	timestamp;

	constructor(data) {
		this.level = new MemberLevel(data.level);
		this.userId = data.user_id;
		this.type = data.type;
		this.registered = data.registered ? new DateTime(data.registered) : null;
		this.until = data.until ? new DateTime(data.until) : null;
		this.timestamp = data.timestamp ? new DateTime(data.timestamp) : null;
	}
}
