{% extends "index.tpl" %}

{% block greeting %}

{% endblock %}

{% block listspace %}

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