import DateTime from "Models/DateTime";

class DateTimeHelper {

  static getCurrentDateTime() {
        const currentDateTime = new Date();
        const offset = window.environmentData.timeZone;

        const utcDateTime = new Date(Date.UTC(
            currentDateTime.getUTCFullYear(),
            currentDateTime.getUTCMonth(),
            currentDateTime.getUTCDate(),
            currentDateTime.getUTCHours(),
            currentDateTime.getUTCMinutes(),
            currentDateTime.getUTCSeconds()
        ));

        const [offsetSign, offsetHours, offsetMinutes] = offset.match(/([+-])(\d{2}):(\d{2})/).slice(1);
        const offsetTotalMinutes = parseInt(offsetHours) * 60 + parseInt(offsetMinutes);
        const offsetInMilliseconds = offsetTotalMinutes * 60 * 1000 * (offsetSign === '+' ? 1 : -1);

        const adjustedDateTime = new Date(utcDateTime.getTime() + offsetInMilliseconds);

        const year = adjustedDateTime.getUTCFullYear();
        const month = String(adjustedDateTime.getUTCMonth() + 1).padStart(2, '0'); // Months are zero-based
        const day = String(adjustedDateTime.getUTCDate()).padStart(2, '0');
        const hours = String(adjustedDateTime.getUTCHours()).padStart(2, '0');
        const minutes = String(adjustedDateTime.getUTCMinutes()).padStart(2, '0');
        const seconds = String(adjustedDateTime.getUTCSeconds()).padStart(2, '0');

        const date = `${year}-${month}-${day}`;
        const time = `${hours}:${minutes}:${seconds}`;

        return new DateTime(date, time);
    }

}

export default DateTimeHelper;
