export default class User {
	id;
	email;
	firstName;
	lastName;
	loginName;

	constructor(data) {
		this.id = data?.ID ?? data?.id ?? null;
		this.email = data?.email ?? null;
		this.firstName = data?.first_name ?? null;
		this.lastName = data?.last_name ?? null;
		this.loginName = data?.login_name ?? null;
	}
}
