{% extends "index.tpl" %}

{% block listspace %}

	<br />
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.3/jquery-ui.min.js" type="text/javascript"></script>
	<div id="tabs">

		<ul>
			<li><a href="#electronicItemDiv">Electronic Item</a></li>
			<li><a href="#physicalItemDiv">Physical Item</a></li>
		</ul>
		
		<div id="electronicItemDiv">{{ electronicReserveForm.display }}</div>
		<div id="physicalItemDiv">{{ physicalReserveForm.display }}</div>
	</div>

	<script type="text/javascript">
		$(function() {
			$("#tabs").tabs();
		});
	</script>

	<script type="text/javascript">
		<!--

			var localFields = new Array('usagerights', 'restricttologin', 'restricttoenroll', 'uploadedfile');
			var remoteFields = new Array('doi', 'url');

			function toggleFields() {
				var chosenRadio = $("#fileChoice:checked").val();
				if (chosenRadio == 'filechoiceurl') {
					hideLocal();
				} else {
					hideRemote();
				}
			}

			function hideLocal() {
				for (i = 0 ; i < localFields.length ; i ++) {
					$("#li-" + localFields[i]).hide();
				}
				for (i = 0 ; i < remoteFields.length ; i ++) {
					$("#li-" + remoteFields[i]).show();
				}
			}
			
			function hideRemote() {
				for (i = 0 ; i < remoteFields.length ; i ++) {
					$("#li-" + remoteFields[i]).hide();
				}
				for (i = 0 ; i < localFields.length ; i ++) {
					$("#li-" + localFields[i]).show();
				}
			}
			
			$(document).ready(function() {
				toggleFields();
			});
		// -->
	</script>

{% endblock %}