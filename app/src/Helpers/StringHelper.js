export class StringHelper {
	static toPascalCase(str) {
		return str
			.split('-')
			.map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
			.join('');
	}

	static truncateText(str, maxLength) {
		const ellipsis = ' ...';
		if (maxLength <= ellipsis.length) {
			return ellipsis.substring(0, maxLength);
		}

		if (str.length <= maxLength) {
			return str;
		}

		return str.substring(0, maxLength - ellipsis.length) + ellipsis;
	}

	static stringToColor(str) {
		let sum = 0;
		for (let i = 0; i < str.length; i++) {
			sum += str.charCodeAt(i);
		}

		var hue= (sum * 897) % 360;
		hue = Math.floor(hue);

		return `hsl(${hue}, 100%, 66%)`;
	}

	static stringToDots(str) {
		var char = '•';
		return char.repeat(str.length);
	}
}
