;(function() {
	if (window.location.href.includes('-cn')) {
		return;
	}
	fetch('https://api.ip.la/en?json')
	.then(res => res.json())
	.then(res => {
		if (res.location.country_code === "CN") {
			document.getElementById('cn-tips').style.display = "block";
		}
	});
})()