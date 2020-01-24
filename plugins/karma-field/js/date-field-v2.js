/**
 * version jan2020
 */
// var DateField = {};



// DateField.createManager = function(input, hiddenInput, format) {
//   var calendar = Calendar.create();
//   var manager = {
//     // format: "dd/mm/yyyy",
//
//   	open: function() {
//       if (!this.popup) {
//         this.popup = this.build();
//         input.parentNode.appendChild(this.popup);
//
//         // prevent closing
//         this.popup.onmousedown = function(event) {
//           event.preventDefault();
//         }
//
//         if (hiddenInput.value) {
//           calendar.date = Calendar.parse(hiddenInput.value);
//         } else {
//           calendar.date = new Date();
//         }
//
//         calendar.update();
//
//         input.parentNode.style.position = "relative";
//         this.popup.style.left = input.offsetLeft.toFixed() + "px";
//         this.popup.style.top = (input.offsetTop-this.popup.clientHeight-2).toFixed() + "px";
//       }
//   	},
//   	close: function() {
//       if (this.popup) {
//         input.parentNode.removeChild(this.popup);
//         this.popup = null;
//       }
//   	},
//     build: function() {
//       return build({
//         class: "karma-popup",
//         child: build({
//           class: "karma-calendar",
//           start: function(update) {
//             var rows;
//             calendar.onUpdate = function(days) {
//               rows = [];
//         			while(days.length) {
//         				rows.push(days.splice(0, 7));
//         			}
//               update(rows);
//             }
//             return function(row) {
//               return {
//                 child: build({
//                   class: "karma-calendar-content",
//                   children: [
//                     build({
//                       class: "karma-calendar-header",
//                       child: build({
//                         class: "karma-calendar-nav",
//                         children: [
//                           build({
//                             class: "karma-prev-month karma-calendar-arrow",
//                             text: "&lsaquo;",
//                             init: function(arrow) {
//                               arrow.addEventListener("mouseup", function() {
//                 								calendar.changeMonth(-1);
//                 							});
//                             }
//                           }),
//                           build({
//                             class: "karma-current-month",
//                             text: Calendar.format(calendar.date, "%fullmonth% yyyy")
//                           }),
//                           build({
//                             class: "karma-next-month karma-calendar-arrow",
//                             text: " &rsaquo;",
//                             init: function(arrow) {
//                               arrow.addEventListener("mouseup", function() {
//                 								calendar.changeMonth(1);
//                 							});
//                             }
//                           })
//                         ]
//                       })
//                     }),
//                     build({
//                       class: "karma-calendar-body",
//                       children: [
//                         build({
//                           tag: "ul",
//                           class: "calendar-days-title",
//                           children: rows[0].map(function(day) {
//                 						return build({
//                               tag: "li",
//                               text: Calendar.format(day.date, "%d2%")
//                             });
//                 					})
//                         })
//                       ].concat(rows.map(function(row) {
//                         return build({
//                           tag: "ul",
//                           class: "calendar-days-content",
//                           children: row.map(function(day) {
//                             return build({
//                               tag: "li",
//                               child: build({
//                                 tag: "span",
//                                 text: Calendar.format(day.date, "#d")
//                               }),
//                               init: function(li) {
//                                 if (hiddenInput.value === day.sqlDate) {
//                                   li.classList.add("active");
//                                 }
//                                 if (day.isOffset) {
//                                   li.classList.add("offset");
//                                 }
//                                 if (day.isToday) {
//                                   li.classList.add("today");
//                                 }
//                                 li.onmouseup = function(event) {
//                                   event.preventDefault();
//                                   hiddenInput.value = day.sqlDate;
//                                   input.value = Calendar.format(day.date, format);
//                                   if (manager.onUpdate) {
//                                     manager.onUpdate(day.date);
//                                   }
//                                   manager.close();
//                                 }
//                               }
//                             });
//               						})
//                         });
//             					}))
//                     })
//                   ]
//                 })
//               };
//             }
//           }
//         })
//       });
//     }
//   };
//   if (!format) {
//     format = "dd/mm/yyyy";
//   }
//   if (hiddenInput.value) {
//     var date = Calendar.parse(hiddenInput.value);
//     input.value = Calendar.format(date, format);
//   }
//   input.addEventListener("keyup", function() {
//     var date = Calendar.parse(this.value, format);
//     if (date) {
//       calendar.date = date;
//       hiddenInput.value = Calendar.format(date);
//     } else {
//       calendar.date = new Date();
//       hiddenInput.value = "";
//     }
//     calendar.update();
//     if (manager.onUpdate) {
//       manager.onUpdate(date);
//     }
//   });
//   input.addEventListener("keydown", function(event) {
//     if (event.key === "ArrowDown") {
//       if (hiddenInput.value) {
//         var index = this.selectionStart || 0;
//         var date = Calendar.parse(hiddenInput.value);
//         if (format[index] === "y" || format[index-1] === "y") date.setFullYear(date.getFullYear() + 1);
//         else if (format[index] === "m" || format[index-1] === "m") date.setMonth(date.getMonth() + 1);
//         else if (format[index] === "d" || format[index-1] === "d") date.setDate(date.getDate() + 1);
//         input.value = Calendar.format(date, format);
//         hiddenInput.value = Calendar.format(date);
//         calendar.date = date;
//         calendar.update();
//         this.setSelectionRange(index, index);
//         event.preventDefault();
//       }
//     } else if (event.key === "ArrowUp") {
//       if (hiddenInput.value) {
//         var date = Calendar.parse(hiddenInput.value);
//         var index = this.selectionStart || 0;
//         if (format[index] === "y" || format[index-1] === "y") date.setFullYear(date.getFullYear() - 1);
//         else if (format[index] === "m" || format[index-1] === "m") date.setMonth(date.getMonth() - 1);
//         else if (format[index] === "d" || format[index-1] === "d") date.setDate(date.getDate() - 1);
//         input.value = Calendar.format(date, format);
//         hiddenInput.value = Calendar.format(date);
//         calendar.date = date;
//         calendar.update();
//         this.setSelectionRange(index, index);
//         event.preventDefault();
//       }
//     }
//   });
//   input.addEventListener("mousedown", function() {
//     manager.open();
//   });
//   input.addEventListener("focus", function() {
//     manager.open();
//   });
//   input.addEventListener("focusout", function() {
//     manager.close();
//   });
// 	return manager;
// }


// DateField.buildDateField = function(sqlDate, input, onSave) {
KarmaFieldMedia.fields.date = function(sqlDate, option, onSave) {
  var format = option.format || "dd/mm/yyyy";
  var open;
  var close;
  var onUpdate;
  var calendar = Calendar.create();
  calendar.date = sqlDate && Calendar.parse(sqlDate) || new Date();
  return build({
    class: "date-input-container",
    children: [
      build({
        class: "date-popup-container",
        onUpdate: function(isOpen) {
          return {
            child: isOpen && build({
              class: "karma-popup",
              child: build({
                class: "karma-calendar",
                init: function(calendarElement, update) {
                  calendar.onUpdate = function(days) {
                    var rows = [];
              			while(days.length) {
              				rows.push(days.splice(0, 7));
              			}
                    update(rows);
                  }
                },
                onUpdate: function(rows) {
                  return {
                    child: build({
                      class: "karma-calendar-content",
                      children: [
                        build({
                          class: "karma-calendar-header",
                          child: build({
                            class: "karma-calendar-nav",
                            children: [
                              build({
                                class: "karma-prev-month karma-calendar-arrow",
                                text: "&lsaquo;",
                                init: function(arrow) {
                                  arrow.addEventListener("mouseup", function() {
                                    calendar.changeMonth(-1);
                                  });
                                }
                              }),
                              build({
                                class: "karma-current-month",
                                text: Calendar.format(calendar.date, "%fullmonth% yyyy")
                              }),
                              build({
                                class: "karma-next-month karma-calendar-arrow",
                                text: " &rsaquo;",
                                init: function(arrow) {
                                  arrow.addEventListener("mouseup", function() {
                                    calendar.changeMonth(1);
                                  });
                                }
                              })
                            ]
                          })
                        }),
                        build({
                          class: "karma-calendar-body",
                          children: [
                            build({
                              tag: "ul",
                              class: "calendar-days-title",
                              children: rows[0].map(function(day) {
                                return build({
                                  tag: "li",
                                  text: Calendar.format(day.date, "%d2%")
                                });
                              })
                            })
                          ].concat(rows.map(function(row) {
                            return build({
                              tag: "ul",
                              class: "calendar-days-content",
                              children: row.map(function(day) {
                                return build({
                                  tag: "li",
                                  child: build({
                                    tag: "span",
                                    text: Calendar.format(day.date, "#d")
                                  }),
                                  init: function(li) {
                                    if (sqlDate === day.sqlDate) {
                                      li.classList.add("active");
                                    }
                                    if (day.isOffset) {
                                      li.classList.add("offset");
                                    }
                                    if (day.isToday) {
                                      li.classList.add("today");
                                    }
                                    li.onmouseup = function(event) {
                                      event.preventDefault();
                                      sqlDate = day.sqlDate;
                                      onUpdate();
                                      onSave && onSave(sqlDate);
                                      close();
                                    }
                                  }
                                });
                              })
                            });
                          }))
                        })
                      ]
                    })
                  };
                }
              }),
              init: function(popup) {
                // prevent closing
                popup.onmousedown = function(event) {
                  event.preventDefault();
                };
              }
            })
          }
        },
        init: function(container, update) {
          open = function() {
            update(true);
            calendar.update();
          };
          close = function() {
            update();
          };
        }
      }),
      build({
        tag: "input",
        class: "text",
        init: function(input) {
          input.type = "text";
          if (option.id) {
            input.id = option.id;
          }
          onUpdate = function() {
            var date = Calendar.parse(sqlDate);
            input.value = Calendar.format(date, format);
          };
          if (sqlDate) {
            onUpdate();
          }
          input.addEventListener("keyup", function() {
            var date = Calendar.parse(this.value, format);
            if (date) {
              calendar.date = date;
              sqlDate = Calendar.format(date);
              calendar.update();
              onUpdate();
            } else {
              sqlDate = '';
            }
            onSave && onSave(sqlDate);
          });
          var keyChange = function(dir) {
            var index = input.selectionStart || 0;
            var date = Calendar.parse(sqlDate);
            if (format[index] === "y" || format[index-1] === "y") date.setFullYear(date.getFullYear() + dir);
            else if (format[index] === "m" || format[index-1] === "m") date.setMonth(date.getMonth() + dir);
            else if (format[index] === "d" || format[index-1] === "d") date.setDate(date.getDate() + dir);
            input.value = Calendar.format(date, format);
            sqlDate = Calendar.format(date);
            calendar.date = date;
            calendar.update();
            input.setSelectionRange(index, index);
            onUpdate();
          };
          input.addEventListener("keydown", function(event) {
            if (sqlDate) {
              if (event.key === "ArrowDown") {
                keyChange(1);
                event.preventDefault();
              } else if (event.key === "ArrowUp") {
                keyChange(-1);
                event.preventDefault();
              }
            }
          });
          input.addEventListener("mousedown", function() {
            open();
          });
          input.addEventListener("focus", function() {
            open();
          });
          input.addEventListener("focusout", function() {
            close();
          });
        }
      })
    ]
  });
}
