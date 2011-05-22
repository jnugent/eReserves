function switchSemesters() {
	
	selectedIndex = document.getElementById('semester').value;
	if (selectedIndex != null) {

		var newUrl = document.location.href;
		var pattern = /\/viewAllReserves.*/;
		newUrl = newUrl.replace(pattern, "/viewAllReserves/0/0/" + selectedIndex);
		document.location.href = newUrl;
	}
	
}

function getOPACRecord(itemID) {

	$("#progress").toggle();
	$.post('{{ basePath }}/index.php/opacProxy/' + itemID, function (data) {
			// data is valid JSON
			var jsonObject = jQuery.parseJSON(data);
			var htmlStr;
			if (jsonObject.title == null) {
				htmlStr = 'There is no record for this Call Number.';
			} else {
				htmlStr = jsonObject.title + ' / ' + jsonObject.author + ' / ' + jsonObject.callNumber + ' / ' + jsonObject.location + ' / ' + jsonObject.library + ' / ' + jsonObject.loanPeriod;
				if (jsonObject.checkedOut == '0') {
					htmlStr = htmlStr + ' / ' + 'AVAILABLE';
				} else {
					htmlStr = htmlStr + ' / ' + 'Checked Out: Due back @ ' + jsonObject.dueBack;
				}
			}
			$("#item-" + itemID).html(htmlStr);
			$("#progress").toggle();
		}
	);
}

