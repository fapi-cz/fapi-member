import Client from './Client';
import MemberSection from 'Models/MemberSection';
import {RequestMethodType} from "Enums/RequestMethodType";
import AlertService from "Services/AlertService";
import {UnlockingType} from "Enums/UnlockingType";

export default class MemberSectionClient extends Client {

	constructor() {
		super('sections');
	}

	async getAll () {
		var sections = [];
		var sectionsData = await this.sendRequest(null, RequestMethodType.GET, []);

		if (sectionsData) {
			sectionsData.forEach((sectionData) => {
				sections.push(new MemberSection(sectionData));
			});
		}

		return sections;
	}

	async delete(id) {
		await this.sendRequest('delete', RequestMethodType.POST, {id: id});
	}xx

	async create(name, parentId = null) {
		await this.sendRequest('create', RequestMethodType.POST, {name: name, parent_id: parentId});
	}

	async updateName(id, name) {
		await this.sendRequest('update', RequestMethodType.POST, {id: id, data: {name: name}});
	}

	async reorder(levelId, direction) {
		await this.sendRequest('reorder', RequestMethodType.POST, {id: levelId, direction: direction});
	}

	async getUnlocking(levelId) {
		return await this.sendRequest('getUnlocking', RequestMethodType.POST, {id: levelId});
	}

	async updateUnlocking(
		levelId,
		buttonUnlock = null,
		timeUnlock = null,
		daysUnlock = null,
		dateUnlock = null,
		hourUnlock = 0,
	) {
		const unlocking = {
			[UnlockingType.BUTTON_UNLOCK]: buttonUnlock,
			[UnlockingType.TIME_UNLOCK]: timeUnlock,
			[UnlockingType.DAYS_UNLOCK]: daysUnlock,
			[UnlockingType.DATE_UNLOCK]: dateUnlock,
			[UnlockingType.HOUR_UNLOCK]: hourUnlock,
		}

		return await this.sendRequest('updateUnlocking', RequestMethodType.POST, {id: levelId, unlocking});
	}
}
