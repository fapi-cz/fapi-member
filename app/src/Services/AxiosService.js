import {RequestMethodType} from "Enums/RequestMethodType";
import AlertService from "Services/AlertService";

const axios = require("axios");

class AxiosService {

	config = {
		headers: {
			'Content-Type': 'application/json',
			'X-WP-Nonce': window.apiInternalAccessNonce,
		},
		withCredentials: true,
	};

	async sendRequest(endpoint, method, data)
	{
		var url = window.location.origin + '/?rest_route=/fapi/v2/' + endpoint;

		switch (method) {
			case RequestMethodType.GET:
				return await this.getRequest(url);
			case RequestMethodType.POST:
				return await this.postRequest(url, data);
		}

	}

	async getRequest(url)
	{
		var responseData = null;

		await axios.get(url, this.config)
		.then(response => {
			responseData = response.data;
			this.handleAlert(response?.data?.data?.alert);
		  })
		  .catch(error => {
			this.handleAlert(error.response?.data?.data?.alert);
		  });

		return responseData;
	}

	async postRequest(url, data)
	{
		var responseData = null;

		await axios.post(url, data, this.config)
		  .then(response => {
			responseData = response.data;
			this.handleAlert(response?.data?.data?.alert);

		  })
		  .catch(error => {
		  	console.log(error)
			this.handleAlert(error.response?.data?.data?.alert);
		  });


		return responseData;
	}

	handleAlert(alert) {
		if (alert?.type === 'error' || alert?.type === 'success' || alert?.type === 'warning') {
			sessionStorage.setItem('fmLastAlertMessage', alert.message);
			sessionStorage.setItem('fmLastAlertType', alert.type);
			AlertService.showAlert(alert.message, alert.type);
		}
	}

}

export default AxiosService;
