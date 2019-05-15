/**
 * Dependancies:
 * - build.js
 * - calendar.js
 */
function buildDateField(date, value) {
  var input = build("input", function() {
    this.type = "text";
    this.value = value;
    this.style.width = "100px";
    this.addEventListener("change+", function() {
      console.log("date change");
    });
  });
  var manager = createDatePopupManager(input);
  return manager.input;
}
function createDatePopupManager(input) {
  var popup;
	var isOpen;
  var manager = {
    format: "dd/mm/yyyy",
  	positionPopup: function() {
  		if (popup && input) {
  			var box = input.getBoundingClientRect();
  			popup.style.left = box.left + "px";
  			if (box.top > window.innerHeight/2) {
  				popup.style.top = (box.top - popup.clientHeight - 4) + "px";
  			} else {
  				popup.style.top = (box.bottom + 4) + "px";
  			}
  		}
  	},
  	open: function() {
  		if (!isOpen) {
  			isOpen = true;
  			popup = build("div.karma-popup", function() {
          // prevent closing
          this.addEventListener("mousedown", function(event) {
            event.preventDefault();
          });
        },
  				build("div.karma-popup-content.media-modal-content",
  					manager.buildCalendar()
  				)
  			);
  			document.body.appendChild(popup);
  			this.positionPopup();
  		}
  	},
  	close: function() {
  		if (isOpen) {
  			document.body.removeChild(popup);
  			isOpen = false;
  		}
  	},
  	buildCalendar: function() {
  		var calendar = Calendar.create();
  		var container = build("div.karma-calendar");
  		var content;
  		calendar.onUpdate = function(days) {
  			var rows = [];
  			if (content) {
  				container.removeChild(content);
  			}

  			while(days.length) {
  				rows.push(days.splice(0, 7));
  			}
  			content =	build("div.karma-calendar-content",
  				build("div.karma-calendar-header",
  					build("div.karma-calendar-nav",
  						build("div.karma-prev-month.karma-calendar-arrow", "&lsaquo;", function() {
  							this.addEventListener("mouseup", function() {
  								calendar.changeMonth(-1);
  							})
  						}),
  						build("div.karma-current-month", Calendar.format(calendar.date, "%fullmonth% yyyy")),
  						build("div.karma-next-month.karma-calendar-arrow", " &rsaquo;", function() {
  							this.addEventListener("mouseup", function() {
  								calendar.changeMonth(1);
  							})
  						})
  					)
  				),
  				build("div.karma-calendar-body",
  					build("ul.calendar-days-title", rows[0].map(function(day) {
  						return build("li", Calendar.format(day.date, "%d2%"));
  					})),
  					rows.map(function(row) {
  						return build("ul.calendar-days-content", row.map(function(day) {
  							var isActive = manager.sqlDate && day.sqlDate.slice(0, 10) === manager.sqlDate.slice(0, 10);
  							return build("li"+(isActive ? ".active" : "")+(day.isOffset ? ".offset" : "")+(day.isToday ? ".today" : "")+(day.isWeekend ? ".weekend" : ""),
  								build("span", Calendar.format(day.date, "#d")),
  								function() {
  									this.addEventListener("mouseup", function(event) {
  										event.preventDefault();
                      input.value = Calendar.format(day.date, manager.format);
                      if (manager.onUpdate) {
                        manager.onUpdate(day.date);
                      }
                      manager.close();
  									});
  								}
  							);
  						}));
  					})
  				)
  			);
  			container.appendChild(content);
  		}
  		if (input.value) {
  			calendar.date = Calendar.parse(input.value, manager.format);
        manager.sqlDate = Calendar.format(calendar.date);
  		}
  		input.addEventListener("keyup", function() {
  			var date = Calendar.parse(this.value, manager.format);
  			if (date) {
  				calendar.date = date;
  				calendar.update();
          if (manager.onUpdate) {
            manager.onUpdate(date);
          }
  			}
  		});
      calendar.update();
  		return container;
  	}
  };
  // input.form.addEventListener("submit", function() {
  //   var date = Calendar.matchDate(input.value, manager.format);
  //   if (date) {
  //     input.value = Calendar.parse(date);
  //   }
  // });
	input.addEventListener("blur", function(event) {
    // var date = Calendar.parse(this.value, manager.format);
    // input.value = date ? Calendar.format(date, manager.format) : "";

    manager.close();
	});
	input.addEventListener("mousedown", function() {
		manager.open();
	});
	input.addEventListener("focus", function() {
		manager.open();
	});
	addEventListener("scroll", function() {
		manager.close();
	});

	document.addEventListener("focusin", function(event) {
    if (event.target !== input) {
			manager.close();
		}
	});
	return manager;
}
