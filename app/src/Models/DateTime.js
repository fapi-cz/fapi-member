class DateTime {
  constructor(dateTimeStringOrDate, timeString) {

    if (timeString !== undefined) {
      this.date = new Date(`${dateTimeStringOrDate}T${timeString}`);
    } else {
      this.date = new Date(dateTimeStringOrDate);
    }
  }

  getDate() {
    const year = this.date.getFullYear();
    const month = String(this.date.getMonth() + 1).padStart(2, '0'); // Months are zero-based
    const day = String(this.date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  getTime() {
    const hours = String(this.date.getHours()).padStart(2, '0');
    const minutes = String(this.date.getMinutes()).padStart(2, '0');
    const seconds = '00';
    return `${hours}:${minutes}:${seconds}`;
  }
}

export default DateTime;
