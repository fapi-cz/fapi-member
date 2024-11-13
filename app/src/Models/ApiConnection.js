import DateTime from "Models/DateTime";

export default class ApiConnection
{
	apiUser;
	apiKey;
	licenceActive;
	licenceExpirationDate;
	billing;


	constructor(data) {
		this.apiUser = data?.api_user ?? null;
		this.apiKey = data?.api_key ?? null;
		this.licenceActive = data?.active ?? null;
		let expirationDate = data?.expiration_date ?? null;

		if (expirationDate !== null) {
			expirationDate = new DateTime(expirationDate);
		}

		this.licenceExpirationDate = expirationDate;
		this.billing = data?.billing ?? null;
	}
}
