import Client from './Client';
import {RequestMethodType} from "Enums/RequestMethodType";
import Membership from "Models/Membership";
import DateTime from "Models/DateTime";
import ApiConnectionClient from "Clients/ApiConnectionClient";

export default class MembershipClient extends Client {
	apiConnectionClient;

	constructor() {
		super('memberships');

		this.apiConnectionClient = new ApiConnectionClient();
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

		if (memberships === null) {
			return;
		}

		var response = await this.sendRequest(
			'updateAllForUser',
			RequestMethodType.POST,
			{
				user_id: userId,
				memberships: memberships,
			},
		);

		return response.success;
	}

	async create(
		row,
	) {
		await this.apiConnectionClient.getApiToken().then(async (tokenData) => {
			row.token = tokenData?.apiToken;
			row.send_email = (row.send_email === undefined || '' === row.send_email)
				? false
				: row.send_email === true || row.send_email === 'true';

			await this.sendRequest(
				'create',
				RequestMethodType.POST,
				row,
			);
		});
	}

	async createMultiple(
		rows,
	) {
		await this.apiConnectionClient.getApiToken().then(async (tokenData) => {
			var data = rows.map((row) => {
				var rowData = row;
				rowData.token = tokenData?.apiToken;
				rowData.send_email = (rowData.send_email === undefined || '' === rowData.send_email)
				? false
				: rowData.send_email === true || rowData.send_email === 'true';

				return rowData;
			});

			await this.sendRequest(
				'createMultiple',
				RequestMethodType.POST,
				data,
			);
		});
	}

	async getUnlockDate(levelId, userId, registrationDate) {
		var date = await this.sendRequest(
			'getUnlockDate',
			RequestMethodType.POST,
			{
				user_id: userId,
				level_id: levelId,
				registration_date: registrationDate,
			},
		);

		if (date !== null && date !== undefined) {
			date =  new DateTime(date);
		}

		return date;
	}

}
