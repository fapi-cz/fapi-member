import Client from './Client';
import {RequestMethodType} from "Enums/RequestMethodType";
import AlertService from "Services/AlertService";
import {EmailType} from "Enums/EmailType";

export default class EmailClient extends Client {

	constructor() {
		super('emails');
	}

	async getForLevel(levelId) {
		return await this.sendRequest('getForLevel', RequestMethodType.POST, {level_id: levelId});
	}

	async updateForLevel(
		levelId,
		afterRegistration = null,
		afterMembershipProlonged = null,
		afterAdding = null,
	) {
		const emailsData = {
			[EmailType.AFTER_REGISTRATION]: {
				subject: afterRegistration?.subject,
				body: afterRegistration?.body,
			},
			[EmailType.AFTER_MEMBERSHIP_PROLONGED]: {
				subject: afterMembershipProlonged?.subject,
				body: afterMembershipProlonged?.body,
			},
			[EmailType.AFTER_ADDING]: {
				subject: afterAdding?.subject,
				body: afterAdding?.body,
			},
		}

		return await this.sendRequest(
			'updateForLevel',
			RequestMethodType.POST,
			{level_id: levelId, emails: emailsData}
		);
	}
}
