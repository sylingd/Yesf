function checkVisitor(res) {
	if (window.location.href.includes('-cn')) {
		return;
	}
	var src = document.createElement('script');
	src.src = "https://get.geojs.io/v1/ip/country.js?callback=checkRegion";
	document.body.appendChild(src);
}
function checkRegion(res) {
	if (res.country === "CN") {
		document.getElementById('cn-tips').style.display = "block";
	}
}
checkVisitor();