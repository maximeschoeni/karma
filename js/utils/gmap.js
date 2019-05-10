function registerGmap(div, settings) {
	var defaults = {
		lat: null,
		lng: null,
		zoom: 18,
		address: "",
		marker: false,
		styles: null
	};
	function render(location) {
		var mapOptions = {
			center: location,
			zoom: settings.zoom,
			disableDefaultUI: true,
			backgroundColor: "white"
// 			gestureHandling: "greedy"
// 				scrollwheel: false,
// 				mapTypeControl: false,
// 				scaleControl: true,
// 					streetView: true,
// 				draggable: false
		};
		var map = new google.maps.Map(div, mapOptions);
		
		if (settings.marker) {
			
			var marker = new google.maps.Marker({
					position: location,
					icon: settings.marker //window.homeURL."/assets/images/pin.png"
			});
			marker.setMap(map);
			if (settings.address) {
				google.maps.event.addListener(marker, 'click', function() {
					var url = "https://www.google.ch/maps/place/"+encodeURIComponent(settings.address);
					var win = window.open(url, '_blank');
					win.focus();
				});
			}
		}
		if (settings.styles) {
			map.setOptions({styles: settings.styles});
		}
	}
	
	for (var i in defaults) {
		if (!settings[i]) {
			settings[i] = defaults[i];
		}
	}
	if (settings.lat && settings.lng) {
		var location = new google.maps.LatLng(settings.lat, settings.lng);
		render(location);
	} else if (settings.address) {
		var geocoder = new google.maps.Geocoder();
		geocoder.geocode({'address': settings.address}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				var location = results[0].geometry.location;
				render(location);
			}
		});
	} 
}