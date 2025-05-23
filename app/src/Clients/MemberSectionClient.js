import Client from './Client';
import MemberSection from 'Models/MemberSection';
import {RequestMethodType} from "Enums/RequestMethodType";
import AlertService from "Services/AlertService";
import {UnlockingType} from "Enums/UnlockingType";
import MemberLevel from "Models/MemberLevel";
import levels from "Components/Content/Levels/Levels";

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

	async getAllAsLevels () {
		var levels = [];
		var levelsData = await this.sendRequest('getAllAsLevels', RequestMethodType.GET, []);

		if (levelsData) {
			levelsData.forEach((levelData) => {
				levels.push(new MemberLevel(levelData));
			});
		}

		return levels;
	}

	async delete(id) {
		await this.sendRequest('delete', RequestMethodType.POST, {id: id});
	}

	async create(name, parentId = null) {
		return await this.sendRequest('create', RequestMethodType.POST, {name: name, parent_id: parentId});
	}

	async updateName(id, name) {
		await this.sendRequest('update', RequestMethodType.POST, {id: id, data: {name: name}});
	}

	async reorder(levelId, direction) {
		await this.sendRequest('reorder', RequestMethodType.POST, {id: levelId, direction: direction});
	}

	async updateUnlocking(
		levelId,
		buttonUnlock = null,
		timeUnlock = null,
		daysUnlock = null,
		dateUnlock = null,
		afterDateUnlock = false,
		hourUnlock = 0,
	) {
		const unlocking = {
			[UnlockingType.BUTTON_UNLOCK]: buttonUnlock,
			[UnlockingType.TIME_UNLOCK]: timeUnlock,
			[UnlockingType.DAYS_UNLOCK]: daysUnlock,
			[UnlockingType.DATE_UNLOCK]: dateUnlock,
			[UnlockingType.AFTER_DATE_UNLOCK]: afterDateUnlock,
			[UnlockingType.HOUR_UNLOCK]: hourUnlock,
		}

		return await this.sendRequest('updateUnlocking', RequestMethodType.POST, {id: levelId, unlocking});
	}

	async getUnlocking(levelId) {
		return await this.sendRequest('getUnlocking', RequestMethodType.POST, {id: levelId});
	}
}
