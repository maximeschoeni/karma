function __(text, localization) {
	return window[localization] && window[localization][text] || text;
}
