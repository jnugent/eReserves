var localFields = new Array('usagerights', 'restricttologin', 'restricttoenroll', 'uploadedfile');
var remoteFields = new Array('doi', 'url', 'proxy');

var localReqFields = new Array('itemauthor', 'usagerights');
var remoteReqFields = new Array('url');

function toggleFields() {
	var chosenRadio = $("#fileChoice:checked").val();
	if (chosenRadio == 'filechoiceurl') {
		hideLocal();
	} else {
		hideRemote();
	}
}

function hideLocal() {
	for (i = 0; i < localFields.length; i++) {
		$("#li-" + localFields[i]).hide();
	}

	for (i = 0; i < localReqFields.length; i++) {
		$("#li-" + localReqFields[i]).removeClass("required");
	}

	for (i = 0; i < localReqFields.length; i++) {
		$("#li-" + localReqFields[i]).addClass("required");
	}

	for (i = 0; i < remoteFields.length; i++) {
		$("#li-" + remoteFields[i]).show();
	}
}

function hideRemote() {
	for (i = 0; i < remoteFields.length; i++) {
		$("#li-" + remoteFields[i]).hide();
	}

	for (i = 0; i < localReqFields.length; i++) {
		$("#li-" + localReqFields[i]).addClass("required");
	}

	for (i = 0; i < remoteReqFields.length; i++) {
		$("#li-" + remoteReqFields[i]).removeClass("required");
	}

	for (i = 0; i < localFields.length; i++) {
		$("#li-" + localFields[i]).show();
	}
}

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
	// our ids look like p### so we need to remove the initial p so our OPAC proxy will work.
	var pattern = /^p/i;
	itemID = itemID.replace(pattern, '');
	$("#progress").toggle();
	$.post('/reserves/index.php/opacProxy/' + itemID, function (data) {
			// data is valid JSON
			var jsonObject = jQuery.parseJSON(data);
			var htmlStr;
			if (jsonObject.title == null) {
				htmlStr = 'There is no record for this Call Number.';
			} else {
				htmlStr = jsonObject.title + '<br />' + jsonObject.author + '<br />' + jsonObject.callNumber;
				$('#p' + itemID + '-where').html(jsonObject.location + '<br />' + jsonObject.library);
				if (jsonObject.checkedOut == '0') {
					$('#p' + itemID + '-where').html(jsonObject.location + '<br />' + jsonObject.library);
					$('#p' + itemID + '-avail').html(jsonObject.loanPeriod);
				} else {
					$('#p' + itemID + '-avail').html('Due back @ ' + jsonObject.dueBack);
				}
			}
			$("#p" + itemID).html(htmlStr);
			$("#progress").toggle();
		}
	);
}

function externalLinks() { 
	if (!document.getElementsByTagName) 
		return; 
	var anchors = document.getElementsByTagName("a"); 
	for (var i=0; i<anchors.length; i++) { 
		var anchor = anchors[i]; 
		if (anchor.getAttribute("href") && anchor.getAttribute("rel") == "external") {
			anchor.target = "_blank"; 
		}
	} 
}

function focusTextField() {
	var inputs = $(":text:visible");
	if (inputs.length > 0) {
		inputs[0].focus();
		return true;
	}
	
	var textfields = $(":input:visible");
	if (textfields.length > 0) {
		textfields[0].focus();
		return true;
	}
}

window.onload = focusTextField;
