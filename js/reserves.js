function switchSemesters() {
	
	selectedIndex = document.getElementById('semester').value;
	if (selectedIndex != null) {

		var newUrl = document.location.href;
		var pattern = /\/viewAllReserves.*/;
		newUrl = newUrl.replace(pattern, "/viewAllReserves/0/0/" + selectedIndex);
		document.location.href = newUrl;
	}
	
}