import React, {useEffect} from 'react';
import AlertService from "Services/AlertService";

function Alert() {
	const alertMessage = sessionStorage.getItem('fmLastAlertMessage');
    const alertType = sessionStorage.getItem('fmLastAlertType');

	useEffect(() => {
		if (alertMessage !== null) {
			AlertService.showAlert(alertMessage, alertType);
		}
	}, []);

	return (
		<div id="alert">
			<div>
				<strong>Chyba: </strong><span id="alert-content"></span>
			</div>
		</div>
	);
}

export default Alert;
