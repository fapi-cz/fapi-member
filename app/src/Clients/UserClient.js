import Client from './Client';
import {RequestMethodType} from "Enums/RequestMethodType";
import User from "Models/User";

export default class UserClient extends Client {

	constructor() {
		super('users');
	}

	async create(email, firstName, lastName){
		var result = await this.sendRequest(
			'create',
			RequestMethodType.POST,
			{email: email ?? null, first_name: firstName ?? null, last_name: lastName ?? null}
		);

		return result;
	}

	async getByEmail(email) {
		var user = await this.sendRequest(
			'getByEmail',
			RequestMethodType.POST,
			{
				email: email,
			}
		);

		return user;
	}

	async list(membersOnly = false) {
		var usersData = await this.sendRequest(
			membersOnly ? 'listMembers' : 'list',
			RequestMethodType.GET,
		);

		return usersData.map((userData) => (new User(userData)));
	}

	async getByLevel(levelId) {
		var usersData = await this.sendRequest(
			'getByLevel',
			RequestMethodType.POST,
			{level_id: levelId},
		);

		return usersData.map((userData) => (new User(userData)));
	}
}
