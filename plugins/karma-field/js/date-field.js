/**
 * version jan2020
 */
var DateField = {};

function createDatePopupManager(input, hiddenInput) {
  var popup;
	var isOpen;
  var manager = {
    input: input,
    hiddenInput: hiddenInput,
    format: "dd/mm/yyyy",
    buildInput: function(name, sqlDate) {
      this.input = build("input", function() {
        this.type = "text";
        // this.value = Calendar.format(item.start_date, manager.format, "yyyy-mm-dd hh:ii:ss");
        this.placeholder = manager.format;
      });
      this.hiddenInput = build("input", function() {
        this.type = "hidden";
        this.name = name;
        // this.value = Calendar.format(item.start_date, manager.format, "yyyy-mm-dd hh:ii:ss");
      })
      if (sqlDate) {
        var date = Calendar.parse(sqlDate);
        this.input.value = Calendar.format(date, manager.format);
        this.hiddenInput.value = sqlDate;
      }
      return build("div.date-field-group", this.hiddenInput, this.input);
    },
  	positionPopup: function() {
  		if (popup && this.input) {
        popup.style.left = this.input.offsetLeft.toFixed() + "px";
        popup.style.top = (this.input.offsetTop-popup.clientHeight-4).toFixed() + "px";
  			// var box = this.input.getBoundingClientRect();
  			// // popup.style.left = box.left + "px";
        // popup.style.left = this.input.offsetLeft + "px";
  			// if (box.top > window.innerHeight/2) {
        //   popup.style.left = this.input.offsetLeft +
        //
  			// 	popup.style.top = (box.top - popup.clientHeight - 4) + "px";
  			// } else {
  			// 	popup.style.top = (box.bottom + 4) + "px";
  			// }
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
  			// document.body.appendChild(popup);

        this.input.parentNode.appendChild(popup);
  			this.positionPopup();

        this.input.onfocusout = function() {
          manager.close();
        }
        // popup.addEventListener("focusout", function(event) {
        //   this.close();
        //   // DateField.onClickOutside && DateField.onClickOutside();
        // });
  		}
  	},
  	close: function() {
  		if (isOpen) {
  			this.input.parentNode.removeChild(popup);
  			isOpen = false;
  		}
      DateField.onClickOutside = null;
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
                      manager.sqlDate = Calendar.format(day.date);
                      if (manager.hiddenInput) {
                        manager.hiddenInput.value = manager.sqlDate;
                      }
                      manager.input.value = Calendar.format(day.date, manager.format);
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

      if (manager.sqlDate) {
        calendar.date = Calendar.parse(manager.sqlDate);
      }
  		manager.input.addEventListener("keyup", function() {
  			var date = Calendar.parse(this.value, manager.format);
  			if (date) {
  				calendar.date = date;
          manager.sqlDate = Calendar.format(date);
  				calendar.update();
          if (manager.onUpdate) {
            manager.onUpdate(date);
          }
  			}
  		});
      calendar.update();
  		return container;
  	},
    init: function() {
      if (manager.hiddenInput) {
        manager.sqlDate = manager.hiddenInput.value;
      }
      var date = Calendar.parse(manager.sqlDate);
      manager.input.value = Calendar.format(date, manager.format);

    	// manager.input.addEventListener("blur", function(event) {
      //   var date = Calendar.parse(manager.input.value, manager.format);
      //   manager.sqlDate = Calendar.format(date);
      //   if (manager.hiddenInput) {
      //     manager.hiddenInput.value = manager.sqlDate;
      //   }
      //   manager.close();
    	// });
    	manager.input.addEventListener("mousedown", function() {
    		manager.open();
    	});
    	manager.input.addEventListener("focus", function() {
    		manager.open();
    	});
    	// addEventListener("scroll", function() {
    	// 	manager.close();
    	// });
    	// document.addEventListener("focusin", function(event) {
      //   if (event.target !== manager.input) {
    	// 		manager.close();
    	// 	}
    	// });
    }
  };

  if (manager.input && manager.hiddenInput || manager.sqlDate) {
    manager.init();
  }

	return manager;
}



// document.body.addEventListener("focusout", function(event) {
//   console.log(event);
//   // DateField.onClickOutside && DateField.onClickOutside();
// });
