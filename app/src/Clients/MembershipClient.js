import Client from './Client';
import {RequestMethodType} from "Enums/RequestMethodType";
import Membership from "Models/Membership";

export default class MembershipClient extends Client {

	constructor() {
		super('memberships');
	}

	async getAll() {
		var memberships = [];
		var membershipsData = await this.sendRequest(
			'list',
			RequestMethodType.GET,
			{},
		);


		if (membershipsData) {
			membershipsData = Object.values(membershipsData);
			membershipsData.forEach((membershipData) => {
				if (!memberships[membershipData.user_id]) {
					memberships[membershipData.user_id] = [];
				}

				if (!memberships[membershipData.user_id][membershipData.level]) {
					memberships[membershipData.user_id][membershipData.level] = [];
				}

				memberships[membershipData.user_id][membershipData.level].push(new Membership(membershipData));
			});
		}

		return memberships.filter(element => element !== '' && element !== null && element !== undefined && !Number.isNaN(element));;
	}

	async getAllForUser(userId) {
		var memberships = [];
		var membershipsData = await this.sendRequest(
			'getAllForUser',
			RequestMethodType.POST,
			{
				user_id: userId,
			},
		);

		if (membershipsData) {
			membershipsData.forEach((membershipData) => {
				memberships[membershipData.level] = new Membership(membershipData);
			});
		}

		return memberships;
	}

	async update(userId, memberships) {
		await this.sendRequest(
			'updateAllForUser',
			RequestMethodType.POST,
			{
				user_id: userId,
				memberships: memberships,
			},
		);
	}

}
