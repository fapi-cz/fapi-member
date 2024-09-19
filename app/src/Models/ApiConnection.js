import DateTime from "Models/DateTime";

export default class ApiConnection
{
	apiUser;
	apiKey;
	licenceActive;
	licenceExpirationDate;


	constructor(data) {
		this.apiUser = data?.api_user ?? null;
		this.apiKey = data?.api_key ?? null;
		this.licenceActive = data?.active ?? null;
		var expirationDate = data?.expiration_date ?? null;

		if (expirationDate !== null) {
			expirationDate = new DateTime(expirationDate);
		}

		this.licenceExpirationDate = expirationDate;

	}
}
