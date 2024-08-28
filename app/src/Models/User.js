import DateTimeHelper from "Helpers/DateTimeHelper";
import DateTime from "Models/DateTime";

export default class User {
	id;
	email;
	firstName;
	lastName;
	loginName;
	createDate;
	levelIds;
	picture;

	constructor(data) {
		this.id = data?.ID ?? data?.id ?? null;
		this.email = data?.email ?? null;
		this.firstName = data?.first_name ?? null;
		this.lastName = data?.last_name ?? null;
		this.loginName = data?.login_name ?? null;
		this.createDate = new DateTime(data.create_date);
		this.levelIds = data?.level_ids ?? null
		this.picture = data?.picture ?? null
	}
}
