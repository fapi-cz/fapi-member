import Client from './Client';
import {RequestMethodType} from "Enums/RequestMethodType";
import ApiConnection from "Models/ApiConnection";

export default class ApiConnectionClient extends Client {

	constructor() {
		super('apiConnections');
	}

	async list() {
		var connectionsData = await this.sendRequest('list', RequestMethodType.GET, {});

		return connectionsData.map((connectionData) => {
			return new ApiConnection(connectionData);
		});
	}

	async getStatusForAll() {
		return await this.sendRequest('getStatusForAll', RequestMethodType.GET, {});
	}

	async getApiToken() {
		return await this.sendRequest('getApiToken', RequestMethodType.GET, {});
	}

	async create(apiUser, apiKey) {
		return await this.sendRequest('create', RequestMethodType.POST, {api_key: apiKey, api_user: apiUser});
	}

	async remove(apiKey) {
		return await this.sendRequest('remove', RequestMethodType.POST, {api_key: apiKey});
	}

}
