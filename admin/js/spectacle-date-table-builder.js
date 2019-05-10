/**
 * Dependancies:
 * - build.js
 * - date-field-builder.js
 */
function buildSpectacleDateTable(spectacleId, shows, reservationCount) {
		
// 	var reservationCount = []; //= <?php echo json_encode($reservations); ?>;
	var shows_url = ""; //"<?php echo add_query_arg(array('post_type' => 'spectacle','page' => 'shows'), admin_url('edit.php')) ?>";		
	var addBtn = document.getElementById("karma-add-date");
	var container; //= document.getElementById("karma-date-table-container");
	var numPlaceInput = document.getElementById("karma-spectacle-num-place");
	var numPlace;
	var table;
	var onBetween = []; // store actions to do while show.id is not set
	var statusList = [{
		name: "Places libres",
		value: "0",
		color: "green"
	}, {
		name: "Presque complet",
		value: "1",
		color: "orange"
	}, {
		name: "Complet",
		value: "2",
		color: "red"
	}, {
		name: "Ni réservation ni achat",
		value: "3",
		color: "grey"
	}, {
		name: "Seulement achat",
		value: "4",
		color: "blue"
	}, {
		name: "Seulement réservation",
		value: "5",
		color: "blue"
	}];
	
	function buildStatusSelector(show) {
		function getColor(status) {
			return statusList[status].color || "black";
		}
		return build("select", statusList.map(function(statusObj) {
			return build("option", statusObj.name, function() {
				this.value = statusObj.value;
				this.style.color = statusObj.color;
				this.selected = show.status === statusObj.value;
			})
		}),
			function() {
				this.style.color = getColor(show.status);
				this.addEventListener("change", function() {
					var status = this.value.toString();
					if (show.status !== status) {
						show.status = status;
						this.style.color = getColor(show.status);
						synchronizeShow(show);
					}
				});
			}
		)
	}
	function buildChangePlaces(show) {
		var button;
		var input = build("input", function() {
			this.type = "number";
			this.value = show.place || "0";
			this.addEventListener("keyup", function(event) {
				update(this.value);
			});
			this.addEventListener("mouseup", function(event) {
				update(this.value);
			});
		});
		var container = build("div", input);
		function update(value) {
			var place = value.toString();
			if (show.place !== place) {
				show.place = place;
				synchronizeShow(show);
			}
		}
		return container;
	}
	function buildTable() {
		
		if (shows.length) {
			shows.sort(function(a, b) {
				if (a.date < b.date) return -1;
				else if (a.date > b.date) return 1;
				else return 0;
			});
			return build("table",
				build("thead",
					build("tr",
						build("th", "Dates"),
						build("th", "Heures"),
						build("th.small-cell", "Status"),
						build("th.small-cell", "Jauge places"),
						build("th.small-cell", "Reservations"),
						build("th.small-cell")
					)
				),
				build("tbody", shows.map(function(show) {
					var numReservation = show.id && reservationCount[show.id] ? parseInt(reservationCount[show.id]) : 0;
					var dayDate = show.date && show.date.length === 19 ? show.date.slice(0, 10) : "";
					var hour = show.date && show.date.length === 19 && !parseInt(show.nohour) ? show.date.slice(-8, -3) : "";
										
					var dateField = buildDateField(show.date);
					var hourField = build("input", function() {
						this.value = hour || "";
						this.placeholder = "hh:mm";
					})
					function saveDate() {
						if (dayDate) {
							var fullDate;
							var nohour;
							if (hour) {
								fullDate = dayDate + " " + hour + ":00";
							} else {
								fullDate = dayDate + " 00:00:00";
								nohour = 1;
							}
							if (fullDate !== show.date || nohour != show.nohour) {
								show.date = fullDate;
								show.nohour = nohour || 0;
								synchronizeShow(show);
							}
						}
					}
					
					hourField.addEventListener("blur", function() {
						var matches = this.value && this.value.match(/[0-9]+/g);
						if (matches) {
							var h = Math.min(parseInt(matches[0]), 23);
							var m = Math.min(parseInt(matches[1] || 0), 59);
							var hh = Calendar.zeroize(h, 2);
							var mm = Calendar.zeroize(m, 2);
							hour = hh + ":" + mm;
						} else {
							hour = "";
						}
						saveDate();
						update();
					});
					dateField.placeholder = "dd/mm/yyyy";
					dateField.addEventListener("update", function(event) {
						dayDate = event.detail.date && event.detail.date.slice(0, 10) || "";
						saveDate();
						update();
					});
					if (show.trash === "1") {
						return build("tr.trash",
							build("td", dayDate),
							build("td", hour),
							build("td", statusList[show.status].name),
							build("td", show.place.toString()),
							build("td", numReservation.toString()),
							build("td", 
								build("button.button", "Annuler", function() {
									this.addEventListener("click", function(event) {
										event.preventDefault();
										show.trash = "0";
										synchronizeShow(show);
										update();
									});
								})
							)
						);
					}
					return build("tr",
						build("td",
							dateField
						),
						build("td",
							hourField
						),
						build("td", 
							buildStatusSelector(show)
						),
						build("td", 
							buildChangePlaces(show)
						),
						build("td",
							(numReservation > 0) ? build("a", function() {
								this.innerHTML = numReservation.toString();
								this.href = karma.admin_url+"edit.php?post_type=spectacle&page=shows&show="+show.id;
							}) : "0"
						),
						build("td", 
							build("button.button", "Supprimer", function() {
								this.addEventListener("click", function(event) {
									event.preventDefault();
									show.trash = "1";
									synchronizeShow(show);
									update();
								});
							})
						)
					);
				}))
			);
		} else {
			return build("label", "Dates", function() {
				this.htmlFor = "karma-add-date";
			});
		}
	}
	function addShow(numPlace, status, date) {
		var show = {
			place: numPlace || shows.length && shows[shows.length-1].place || "0",
			status: status || shows.length && shows[shows.length-1].status || "0",
			date: date || shows.length && shows[shows.length-1].date || ""
		};
		shows.push(show);
		
		$.post(ajaxurl, {
			action: "admin_add_show",
			spectacle_id: spectacleId,
			date: show.date,
			place: show.place,
			status: show.status
		}, function(response) {
			show.id = response.id;
			synchronizeShow(show);
		}, "json");
		
		return show;
	}
	function synchronizeShow(show, callback) {
		if (show.id) {
			$.post(ajaxurl, {
				action: "admin_synchronize_show",
				id: show.id,
				date: show.date || "",
				nohour: show.nohour || 0,
				place: show.place || 0,
				status: show.status || 0,
				trash: show.trash || 0
			}, function(response) {
				console.log(response);
				if (callback) {
					callback(response);
				}
			}, "json");
		}
	}

	function removeShow(show) {
		var index = shows.indexOf(show);
		if (index > -1) {
			shows.splice(index, 1);
		}
	}
	function update() {
		//updatePlace();
		if (table) {
			container.removeChild(table);
		}
		table = buildTable();
		container.appendChild(table);
	}
	
	container = build("div");
	
	update();
	
	return build("div",
		container,
		build("button.button", "Ajouter", function() {
			this.addEventListener("click", function(event) {
				event.preventDefault();
				addShow();
				update();
			});
		})
	);
}