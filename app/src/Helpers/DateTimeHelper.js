import DateTime from "Models/DateTime";

class DateTimeHelper {

  static getCurrentDateTime() {
        const currentDateTime = new Date();
        const offset = window.environmentData.timeZoneOffset;
        const offsetMilliseconds = offset * 60 * 60 * 1000;

        const utcDateTime = new Date(Date.UTC(
            currentDateTime.getUTCFullYear(),
            currentDateTime.getUTCMonth(),
            currentDateTime.getUTCDate(),
            currentDateTime.getUTCHours(),
            currentDateTime.getUTCMinutes(),
            currentDateTime.getUTCSeconds()
        ));

        const adjustedDateTime = new Date(utcDateTime.getTime() + offsetMilliseconds);

        const year = adjustedDateTime.getUTCFullYear();
        const month = String(adjustedDateTime.getUTCMonth() + 1).padStart(2, '0');
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
