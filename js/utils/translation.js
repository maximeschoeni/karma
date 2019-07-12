function __(text, localization) {
	if (typeof localization === "string") {
		return window[localization] && window[localization][text] || text;
	} else {
		return localization[text] || text;
	}
}
