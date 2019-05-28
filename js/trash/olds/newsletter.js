(function() {
	var formContainers = document.querySelectorAll(".newsletter-form");
	function register(container) {
		var form = container.querySelector("form");
		var success = container.querySelector(".newsletter-success");
		if (form) {
			form.addEventListener("submit", function(event) {
				event.preventDefault();
				if (!form.elements["email"] || form.elements["email"].value.indexOf('@') < 0) {
					alert("Veuillez SVP d'abord inscrire votre adresse email.");
				} else {
					ajaxPost(karma.ajax_url, {
						action: "subscribe_newsletter",
						nonce: form.elements["newsletter_nonce"].value,
	// 					first_name: form.elements["first_name"].value,
	// 					last_name: form.elements["last_name"].value,
						email: form.elements["email"].value
					}, function(result) {
						//console.log(result);
					});
					form.reset();
					if (success) {
						success.style.display = "block";
						form.style.display = "none";
					}
				}
			});
		}
	}
	for (var i = 0; i < formContainers.length; i++) {
		register(formContainers[i]);
	}	
})();