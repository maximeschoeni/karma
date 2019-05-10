
function createChildrenManager(container) {
	var manager = {
		sortableManager: createSortableManager(),
		items: [],
    columns: ["date", "hour", "place", "city", "country", "more", "remove"],
		add: function(item) {
			if (!item) {
				item = {};
			}
			this.items.push(item);
		},
		update: function() {
			if (this.content) {
				container.removeChild(this.content);
			}
			this.content = buildChildrenTable(this);
			container.appendChild(this.content);
		}
	};


	manager.sortableManager.checkTarget = function(target) {
		return target.classList.contains("child-cell");
	};
	manager.sortableManager.createPlaceholder = function(draggedElement) {
		placeholder = build("tr.child-row.placeholder", manager.columns.map(function() {
      return build("td");
    }));
		placeholder.style.height = draggedElement.clientHeight.toFixed()+"px";
		return placeholder;
	};
	manager.sortableManager.onStartDrag = function(draggedElement, placeholder, box) {
		draggedElement.style.backgroundColor = "rgb(240, 240, 240)";
	};
	manager.sortableManager.onChange = function(dragFrom, dragTo) {
		var itemToMove = manager.items.splice(dragFrom, 1)[0];
		manager.items.splice(dragTo, 0, itemToMove);
	};
	manager.sortableManager.onEndDrag = function(draggedElement, placeholder) {
		draggedElement.style.removeProperty("background-color");
		manager.update();
	};

	return manager;
}

function buildSelector(values, current, onChange) {
	return build("select",
		values.map(function(value) {
			return build("option", value.name, function() {
				this.value = value.key;
				if (value.key === current) {
					this.selected = true;
				}
			});
		}), function() {
			this.addEventListener("change", onChange);
		}
	);
}


function buildChildrenTable(manager) {

	manager.sortableManager.reset();

	var pageIndex = 0;

	var tbody = build("tbody",
		manager.items.map(function(item, itemIndex) {
			var row = build("tr.child-row",
				build("td.child-cell.date",
					build("input", function() {
            this.type = "text";
            this.name = "start_date[]";
            this.value = Calendar.format(item.start_date, "dd/mm/yyyy", "yyyy-mm-dd hh:ii:ss");
            this.placeholder = "jj/mm/aaaa";
            var manager = createDatePopupManager(this);
            manager.onUpdate = function(date) {
              item.start_date = Calendar.format(date);
            }
          })
				),
        build("td.child-cell.hour",
					build("input", function() {
            this.type = "text";
            this.name = "hour[]";
            this.value = item.hour || "";
            this.addEventListener("input", function() {
              item.hour = this.value;
            });
          })
				),
        build("td.child-cell.name",
					build("input", function() {
            this.type = "text";
            this.name = "name[]";
            this.value = item.name || "";
            this.addEventListener("input", function() {
              item.name = this.value;
            });
          })
				),
				build("td.child-cell.place",
					build("input", function() {
            this.type = "text";
            this.name = "place[]";
            this.value = item.place || "";
            this.addEventListener("input", function() {
              item.place = this.value;
            });
          })
				),
        build("td.child-cell.city",
					build("input", function() {
            this.type = "text";
            this.name = "city[]";
            this.value = item.city || "";
            this.addEventListener("input", function() {
              item.city = this.value;
            });
          })
				),
        build("td.child-cell.country",
					build("input", function() {
            this.type = "text";
            this.name = "country[]";
            this.value = item.country || "";
            this.placeholder = "CH";
            this.addEventListener("input", function() {
              item.country = this.value;
            });
          })
				),
        build("td.child-cell.more",
					build("input", function() {
            this.type = "hidden";
            this.name = "event_id[]";
            this.value = item.id || "";
          }),
          item.id && build("a.button", "more...", function() {
            // this.addEventListener("click", function(event) {
            //   // this.form.elements["_wp_http_referer"] = manager.admin_url+"post.php?post="+item.id+"&action=edit";
						//
            //   // console.log(this.form.elements["_wp_http_referer"]);
            // });
            this.href = manager.admin_url+"post.php?post="+item.id+"&action=edit";
          })
          // item.id && build("a", "more", function() {
          //   this.href = manager.admin_url+"post.php?post="+item.id+"&action=edit";
          // })
				),
				build("td.child-cell.remove",
					build("button.button", "✕", function() {
						this.addEventListener("click", function(event) {
							event.preventDefault();
							manager.items.splice(itemIndex, 1);
							manager.update();
						});
					})
				)
			);
			manager.sortableManager.addItem(row);
			return row;
		})
	);

	manager.sortableManager.container = tbody;

	return build("div.table-children-container",
    build("table.children-table.widefat.striped",
  		build("thead",
  			build("tr",
  				build("th", "Date"),
  				build("th", "Heure"),
					build("th", "Nom de l'événement"),
  				build("th", "Lieu (salle/institution)"),
          build("th", "Lieu (ville)"),
          build("th", "Lieu (pays)"),
          build("th", ""),
  				build("th", "")
  			)
  		),
  		tbody
    ),
    build("button.button.children-table-add-child", "+", function() {
      this.addEventListener("click", function(event) {
        event.preventDefault();
        manager.add();
        manager.update();
      });
    })
	);
}
