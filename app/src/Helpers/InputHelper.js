export class InputHelper {
	static getValue(id, defaultValue = null) {
		var value = document.querySelector('#' + id)?.value;

		if (
			value === undefined ||
			value?.trim() === '' ||
			value === '' ||
			value === null
		) {
			return defaultValue;
		}

		return value;
	}

	static getCheckboxValue(id) {
		var value = document.querySelector('#' + id)?.checked

		if (value === true) {
			return true;
		}

		return false;
	}

}
