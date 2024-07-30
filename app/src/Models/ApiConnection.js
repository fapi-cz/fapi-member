export default class ApiConnection
{
	apiUser;
	apiKey;

	constructor(data) {
		this.apiUser = data?.api_user ?? null;
		this.apiKey = data?.api_key ?? null;
	}
}
