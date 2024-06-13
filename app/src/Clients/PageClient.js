import Client from './Client';
import {RequestMethodType} from "Enums/RequestMethodType";
import Page from "Models/Page";
import {ServicePageType} from "Enums/ServicePageType";
import AlertService from "Services/AlertService";
import {CommonPageType} from "Enums/CommonPageType";

export default class PageClient extends Client {

	constructor() {
		super('pages');
	}

	async list() {
		var pages = [];
		const pagesData = await this.sendRequest('list', RequestMethodType.GET, {});
		if (pagesData) {
			pagesData.forEach((pageData) => {
				pages.push(new Page(pageData));
			});
		}

		return pages;
	}

	async listWithCpts() {
		var pages = [];
		const pagesData = await this.sendRequest('listWithCpts', RequestMethodType.GET, {});
		if (pagesData) {
			pagesData.forEach((pageData) => {
				pages.push(new Page(pageData));
			});
		}

		return pages;
	}

	async getIdsByLevel(levelId) {
		return await this.sendRequest('getIdsByLevel', RequestMethodType.POST, {level_id: levelId});
	}

	async getIdsByAllLevels() {
		return await this.sendRequest('getIdsByAllLevels', RequestMethodType.GET, {});
	}

	async updatePagesForLevel(levelId, pages) {
		return await this.sendRequest('updatePagesForLevel', RequestMethodType.POST, {level_id: levelId, pages: pages});
	}

	async getServicePagesForLevel(levelId) {
		return await this.sendRequest('getServicePagesByLevel', RequestMethodType.POST, {level_id: levelId})
	}

	async updateServicePagesForLevel(
		levelId,
		noAccessPageId = null,
		loginPageId = null,
		afterLoginPageId = null,
	) {
		var pages = {
			[ServicePageType.NO_ACCESS]: noAccessPageId,
			[ServicePageType.LOGIN]: loginPageId,
			[ServicePageType.AFTER_LOGIN]: afterLoginPageId
		}

		await this.sendRequest('updateServicePagesForLevel', RequestMethodType.POST, {level_id: levelId, pages: pages});
	}

	async getCommonPagesForLevel() {
		return await this.sendRequest('getCommonPagesByLevel', RequestMethodType.POST, {})
	}

	async updateCommonPagesForLevel(
		loginPageId = null,
		dashboardPageId = null,
		timeLockedPageId = null,
	) {
		var pages = {
			[CommonPageType.LOGIN_PAGE]: loginPageId,
			[CommonPageType.DASHBOARD_PAGE]: dashboardPageId,
			[CommonPageType.TIME_LOCKED_PAGE]: timeLockedPageId,
		}

		await this.sendRequest('updateCommonPagesForLevel', RequestMethodType.POST, {pages: pages});
	}

}
