
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
		},
		clone: function(item) {
			var clone = {};
			clone.start_date = item.start_date;
			for (var i = 0; i < this.fields.length; i++) {
				var fieldName = this.fields[i].name;
				clone[fieldName] = item[fieldName];
			}
			return clone;
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

function buildSelector(name, values, current, onChange) {
	return build("select",
		values.map(function(value) {
			return build("option", value.name, function() {
				this.value = value.key;
				if (parseInt(value.key) === current) {
					this.selected = true;
				}
			});
		}), function() {
			this.name = name;
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
						this.name = "subevent[start_date][]";
						this.value = Calendar.format(item.start_date, "dd/mm/yyyy", "yyyy-mm-dd hh:ii:ss");
						this.placeholder = "jj/mm/aaaa";
						var manager = createDatePopupManager(this);
						manager.onUpdate = function(date) {
							item.start_date = Calendar.format(date);
						}
					})
				),
				manager.fields.map(function(field) {
					if (field.type === 'taxonomy') {
						return build("td.child-cell",
							buildSelector("subevent["+field.name+"][]", manager.types[field.name], item[field.name] && item[field.name].length && item[field.name][0], function() {
								item[field.name] = this.value;
							})
						);
					} else {
						return build("td.child-cell",
							build("input", function() {
						    this.type = "text";
						    this.name = "subevent["+field.name+"][]";
						    this.value = item[field.name] || "";
						    this.addEventListener("input", function() {
						      item[field.name] = this.value;
						    });
						  })
						);
					}
				}),
        build("td.child-cell.more",
          item.id && build("a.button", "more...", function() {
            this.href = manager.admin_url+"post.php?post="+item.id+"&action=edit";
          })
				),
				build("td.child-cell.remove",
					build("input", function() {
						this.type = "hidden";
						this.name = "subevent[event_id][]";
						this.value = item.id || "";
					}),
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
    manager.items.length && build("table.children-table.widefat.striped",
  		build("thead",
  			build("tr",
  				build("th", "Date"),
					manager.fields.map(function(field) {
						return build("th", field.label);
					}),
          build("th", ""),
  				build("th", "")
  			)
  		),
  		tbody
    ),
    build("button.button.children-table-add-child", "+", function() {
      this.addEventListener("click", function(event) {
        event.preventDefault();
				manager.add(manager.items.length && manager.clone(manager.items[manager.items.length-1]));
        manager.update();
      });
    })
	);
}
