function registerThumbHover(table) {
	var currentRow;
	var thumb;
	function update(row) {
		if (thumb) {
			document.body.removeChild(thumb);
			thumb = null;
		}
		currentRow = null;
		if (row && row.hasAttribute("data-thumb")) {
			thumb = build("img", function() {
				this.src = row.getAttribute("data-thumb");
				this.className = "thumb-hover";
				this.width = 300;
				// this.height = 206;
				this.style.height = "auto";
				this.style.position = "fixed";
				this.style.zIndex = "20";
			});
			document.body.appendChild(thumb);
			currentRow = row;
		}
	}	
	function render(x, y) {
		if (thumb) {
			thumb.style.left = (x+10)+"px";
			thumb.style.top = (y+10)+"px";
		}
	}
	function registerRow(row) {
		document.addEventListener("mousemove", function(event) {
			var box = row.getBoundingClientRect();
			var x = event.clientX;
			var y = event.clientY;
			if (x > box.left && x < box.right && y > box.top && y < box.bottom) {
				if (row !== currentRow) {
					update(row);
					render(x, y);
				}
			} else if (row === currentRow) {
				update();
			} 
		});
	}
	document.addEventListener("mousemove", function(event) {
		var x = event.clientX;
		var y = event.clientY;
		render(x, y)
	});	
	var rows = table.querySelectorAll("tbody tr");
	for (var i = 0; i < rows.length; i++) {
		registerRow(rows[i]);
	}
}

function registerSortColumns(table) {
	var headerRow = table.querySelector("thead tr");
	var tbody = table.querySelector("tbody");
	var currentColumnIndex = 0;
	var currentOrder = "desc";
	function registerColumn(th, index) {
		th.addEventListener("click", function() {
			currentColumnIndex = index;
			currentOrder = th.getAttribute("data-order") || "asc";
			console.log(th, index, currentOrder);
			sort();
		});
	}
	function sort() {
		var rows = [];
		while (tbody.children.length) {
			var row = tbody.children[0];
			tbody.removeChild(row);
			rows.push(row);
		}
		rows.sort(function(a, b) {
			var comp = a.children[currentColumnIndex].innerHTML.localeCompare(b.children[currentColumnIndex].innerHTML);
			if (currentOrder === "desc") {
				comp = -comp;
			}
			if (comp === 0) {
				comp = a.children[0].innerHTML.localeCompare(b.children[0].innerHTML)*-1;
			}
			if (comp === 0) {
				comp = a.children[1].innerHTML.localeCompare(b.children[1].innerHTML);
			}
			return comp;
		});
		for (var i = 0; i < rows.length; i++) {
			tbody.appendChild(rows[i]);
		}
	}
	
	if (headerRow && tbody) {
		for (var i = 0; i < headerRow.children.length; i++) {
			registerColumn(headerRow.children[i], i);
		}
	}
	
}


document.addEventListener("DOMContentLoaded", function() {
	var table = document.getElementById("index-projects-table");
	
	function registerLinks() {
		var rows = table.querySelectorAll("tbody tr");
		console.log(rows);
		for (var i = 0; i < rows.length; i++) {
			rows[i].addEventListener("click", function(event) {
				event.preventDefault();
				location.href = this.getAttribute("data-link");
			});
		}
	}
	
	if (table) {
		registerThumbHover(table);
		registerSortColumns(table);
		registerLinks();
	}
});
