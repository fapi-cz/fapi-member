import AxiosService from 'Services/AxiosService';

export default class Client {
	axiosService;
	namespace;

	constructor (namespace) {
		this.axiosService = new AxiosService();
		this.namespace = namespace;
	}

	async sendRequest(action, method, data = []) {
        document.body.style.cursor = 'wait';
		var response = null;

		if (action !== null) {
			action = '&action=' + action;
		} else {
			action = '';
		}

		await this.axiosService.sendRequest(this.namespace + action, method, data)
			.then(responseData => {
				response = responseData;
			});

        document.body.style.cursor = 'default';

		return response;
	}
}
