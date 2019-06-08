function createAccordeon() {
	var accordeon = createPopupManager();
	accordeon.add = function(container, handle, body, content, isOpen) {
		var item = {
			container: container,
			handle: handle,
			body: body,
			content: content
		}
		if (body.parentNode === container) {
			container.removeChild(body);
		}
		handle.addEventListener("click", function() {
			if (accordeon.current === item) {
				accordeon.update();
				if (item.onClose) {
					item.onClose();
				}
			} else {
				accordeon.update(item);
				if (item.onOpen) {
					item.onOpen();
				}
			}
		});
		this.update(isOpen && item, true);
		return item;
	};
	accordeon.onBeforeOpen = function(item) {
		item.container.appendChild(item.body);
		item.body.style.height = "0";
		item.container.classList.add("active");
		item.container.classList.remove("closed");
	}
	accordeon.onAfterOpen = function(item) {
		item.container.classList.add("open");
	}
	accordeon.onBeforeClose = function(item) {
		item.container.classList.remove("open");
		item.container.classList.remove("active");
	}
	accordeon.onAfterClose = function(item) {
		item.container.removeChild(item.body);
		item.container.classList.add("closed");
	}
	accordeon.onRender = function(item, value) {
		item.body.style.height = (value*item.content.clientHeight).toFixed() + "px";
	}
	return accordeon;
}
