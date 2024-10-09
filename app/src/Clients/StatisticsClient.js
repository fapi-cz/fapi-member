import Client from './Client';
import {RequestMethodType} from "Enums/RequestMethodType";
import MembershipChange from "Models/MembershipChange";

export default class StatisticsClient extends Client {

	constructor() {
		super('statistics');
	}

	async getMembershipChangesForUser (userId) {
		var changesData = await this.sendRequest(
			'getMembershipChangesForUser',
			RequestMethodType.POST,
			{user_id: userId}
		);

		var changes = [];

		if (changesData) {
			changesData.forEach((changeData) => {
				changes.push(new MembershipChange(changeData));
			});
		}

		return changes;
	}

	async getMemberCountsForPeriod(filterData){
		return await this.sendRequest(
			'getMemberCountsForPeriod',
			RequestMethodType.POST,
			filterData,
		);
	}

	async getMemberCountChangesForPeriod(filterData){
		return await this.sendRequest(
			'getMemberCountChangesForPeriod',
			RequestMethodType.POST,
			filterData,
		);
	}

	async getChurnRate(filterData){
		return await this.sendRequest(
			'getChurnRate',
			RequestMethodType.POST,
			filterData,
		);
	}

	async getAcquisitionRate(filterData){
		return await this.sendRequest(
			'getAcquisitionRate',
			RequestMethodType.POST,
			filterData,
		);
	}

	async getActiveCountsForPeriod(filterData){
		return await this.sendRequest(
			'getActiveCountsForPeriod',
			RequestMethodType.POST,
			filterData,
		);
	}

	async getAverageChurnRatePeriods(filterData){
		return await this.sendRequest(
			'getAverageChurnRatePeriods',
			RequestMethodType.POST,
			filterData,
		);
	}

}
