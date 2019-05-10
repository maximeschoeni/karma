function buildEvent(event) {
	var fullPlace = event.place + (event.city ? " " + event.city : "") + (event.country ? " (" + event.country + ")" : "");
	var fullDate = Calendar.formatRange(event.start_date, event.end_date) + (event.hour ? ", " + event.hour : "");
	var content = event.project && event.project.content || event.content;
	var description = event.project && event.project.description || event.description;
	var auteur = event.project && event.project.auteur || event.auteur;
	return build("div.event.single",
		build("div.slideshow",
			build("div.columns",
				build("div.column.left",
					build("div.place",
						build("p", fullPlace),
						build("p", fullDate)
					),
					build("div.description",
						description && build("p",
							build("em", description)
						),
						auteur && build("p", auteur),
					),
				),
				build("div.column.right", content)
			)
		)
	);
}
