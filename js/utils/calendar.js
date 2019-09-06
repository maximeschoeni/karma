/**
 * Etceterize
 */
var Calendar = {
	months: ["janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"],
	months3: ["jan", "fev", "mar", "avr", "mai", "jun", "jui", "aoû", "sept", "oct", "nov", "dec"],
	weekdays: ["dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi"],
	weekdays1: ["d", "l", "m", "m", "j", "v", "s"],
	weekdays2: ["di", "lu", "ma", "me", "je", "ve", "sa"],
	weekdays3: ["dim", "lun", "mar", "mer", "jeu", "ven", "sam"],
	create: function () {
		var calendar = {
			date: null,
			update: function() {
				if (!this.date) {
					this.date = new Date();
				}
				if (this.onUpdate) {
					var days = this.getDays();
					this.onUpdate(days);
				}
			},
			getDays: function () {
				var days = [];
				var lastDayPrevMonth = new Date(this.date.getFullYear(), this.date.getMonth(), 0);
				var firstDayNextMonth = new Date(this.date.getFullYear(), this.date.getMonth() + 1, 1);
				var date = new Date(this.date.getFullYear(), this.date.getMonth(), 1 - lastDayPrevMonth.getDay());
				var today = (new Date()).setHours(0, 0, 0, 0);

				while((date.getTime() < firstDayNextMonth.getTime()) || date.getDay() !== 1) {
					var day = new Date(date.getFullYear(), date.getMonth(), date.getDate());

					days.push({
						date: day,
						sqlDate: Calendar.format(day),
						isDayBefore: day.getTime() == lastDayPrevMonth.getTime(),
						isDayAfter: day.getTime() == firstDayNextMonth.getTime(),
						isOffset: day.getTime() <= lastDayPrevMonth.getTime() || day.getTime() >= firstDayNextMonth.getTime(),
						isToday: day.getTime() === today,
						isWeekend: day.getDay() === 0 || day.getDay() === 6
					});
					date.setDate(date.getDate() + 1);
				}
				return days;
			},
			setMonth: function(month) {
				this.date.setMonth(month);
				this.update();
			},
			setYear: function(year) {
				this.date.setFullYear(year);
				this.update();
			},
			changeMonth: function(diff) {
				this.date.setMonth(this.date.getMonth() + diff);
				this.update();
			},
			changeYear: function(diff) {
				this.date.setFullYear(this.date.getFullYear() + diff);
				this.update();
			}
		}
		return calendar;
	},
	zeroize: function(number, size) {
		number = number.toString().slice(-size);
		while (number.length < size) {
			number = "0" + number;
		}
		return number;
	},
	format: function(date, format, inputFormat) { // 0000-00-00 00:00:00
		if (date && inputFormat) {
			date = this.parse(date, inputFormat);
		}
		if (date) {
			if (!format) {
				format = "yyyy-mm-dd hh:ii:ss";
			}
			format = format.replace("yyyy", this.zeroize(date.getFullYear(), 4));
			format = format.replace("mm", this.zeroize(date.getMonth()+1, 2));
			format = format.replace("dd", this.zeroize(date.getDate(), 2));
			format = format.replace("hh", this.zeroize(date.getHours(), 2));
			format = format.replace("ii", this.zeroize(date.getMinutes(), 2));
			format = format.replace("ss", this.zeroize(date.getSeconds(), 2));
			format = format.replace("#m", (date.getMonth()+1).toString());
			format = format.replace("#d", date.getDate().toString());
			format = format.replace("#w", date.getDay().toString());
			format = format.replace("%fullmonth%", Calendar.months[date.getMonth()]);
			format = format.replace("%fullweek%", Calendar.weekdays[date.getDay()]);
			format = format.replace("%mon%", Calendar.months3[date.getMonth()]);
			format = format.replace("%day%", Calendar.weekdays3[date.getDay()]);
			format = format.replace("%d2%", Calendar.weekdays2[date.getDay()]);
			format = format.replace("%d%", Calendar.weekdays1[date.getDay()]);
			format = format.replace("%y2%", this.zeroize(date.getFullYear(), 2));
			return format;
		}
		return "";
	},
	parse: function(dateString, format, outputFormat) {
		format = format || "yyyy-mm-dd hh:ii:ss";
		var date = new Date(0, 0, 1);
		var reg = format
			.replace("yyyy", "([0-9]{4})")
			.replace("mm", "([0-9]{2})")
			.replace("dd", "([0-9]{2})")
			.replace("hh", "([0-9]{2})")
			.replace("ii", "([0-9]{2})")
			.replace("ss", "([0-9]{2})")
			.replace("#m", "([0-9]+)")
			.replace("#d", "([0-9]+)")
			.replace("%y2%", "([0-9]{2})")
			.replace("%fullmonth%", "("+Calendar.months3.join("|")+")")
			.replace("%mon%", "("+Calendar.months3.join("|")+")");
		var results = dateString.match(new RegExp("^"+reg+"$"));
		var items = format.match(/(yyyy|mm|dd|hh|ii|ss|#m|#d|%y2%|%fullmonth%|%mon%)/g);
		if (results && items) {
			for (var i = 1; i < results.length; i++) {
				switch (items[i-1]) {
					case "yyyy":
						date.setFullYear(parseInt(results[i]));
						break;
					case "%y2%":
						date.setFullYear(parseInt("20"+results[i]));
						break;
					case "mm":
					case "#m":
						date.setMonth(parseInt(results[i])-1);
						break;
					case "dd":
					case "#d":
						date.setDate(parseInt(results[i]));
						break;
					case "hh":
						date.setHours(parseInt(results[i]));
						break;
					case "ii":
						date.setMinutes(parseInt(results[i]));
						break;
					case "ss":
						date.setSeconds(parseInt(results[i]));
						break;
					case "%fullmonth%":
						date.setMonth(Calendar.months.indexOf(results[i]) || 0);
						break;
					case "%mon%":
						date.setMonth(Calendar.months3.indexOf(results[i]) || 0);
						break;
				}
			}
		}

		if (date > new Date(0, 0, 1)) {
			if (outputFormat) {
				return this.format(date, outputFormat);
			}
			return date;
		}
	},
	formatRange: function(date1, date2, sep) {
		if (typeof date1 === "string") date1 = this.parse(date1);
		if (typeof date2 === "string") date2 = this.parse(date2);
		if (!sep) {
			sep = " — ";
		}
		var d1 = this.format(date1, 'dd');
		var m1 = this.format(date1, 'mm');
		var y1 = this.format(date1, 'yyyy');
		var d2 = this.format(date2, 'dd');
		var m2 = this.format(date2, 'mm');
		var y2 = this.format(date2, 'yyyy');
    if (y1 === y2) {
      if (m1 === m2) {
        if (d1 === d2) {
          return this.format(date2, 'dd.mm.yyyy');
        } else {
          return this.format(date1, 'dd') + sep + this.format(date2, 'dd.mm.yyyy');
        }
      } else {
        return this.format(date1, 'dd.mm') + sep + this.format(date2, 'dd.mm.yyyy');
      }
    } else {
      return this.format(date1, 'dd.mm.yyyy') + sep + this.format(date2, 'dd.mm.yyyy');
    }
  }

};
