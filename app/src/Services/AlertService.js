class AlertService
{
	static alertsOpened = 0;

	static async showAlert(message, type = 'error') {
		var showDuration = 2;

		if (type === 'error' || type === 'warning') {
			showDuration = 3;
		}

		var alert = document.querySelector('#alert');
		var alertContent = document.querySelector('#alert-content');

		if (this.alertsOpened > 0) {
			this.close(alert);

			setTimeout(() => {
				this.open(alert, alertContent, message, type);
			}, 200);
		} else {
			this.open(alert, alertContent, message, type);
		}

		this.waitToClose(showDuration, alert);
	}

	static async waitToClose(showDuration, alert){
		setTimeout(() => {
			if(this.alertsOpened === 1) {
				this.close(alert);
			}

			this.alertsOpened -= 1;
		}, 1000 * showDuration);
	}

	static async close(alert) {
		alert.className = alert.className.replace('alert-visible', 'alert-hidden');

		if (sessionStorage.getItem('fmLastAlertMessage') !== null) {
			sessionStorage.removeItem('fmLastAlertMessage');
			sessionStorage.removeItem('fmLastAlertType');
		}
	}

	static async open(alert, alertContent, message, type) {
		this.alertsOpened += 1;
		alertContent.innerHTML = message;
		alert.className = 'alert-visible ' + type;
	}

}

export default AlertService;
