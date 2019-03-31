function checkRegion(res) {
	if (window.location.href.includes('-cn')) {
		return;
	}
	if (res.country === "CN") {
		document.getElementById('cn-tips').style.display = "block";
	}
}