{% extends "index.tpl" %}

{% block listspace %}

	<br />
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.3/jquery-ui.min.js" type="text/javascript"></script>
	<div id="tabs">

		<ul>
			<li><a href="#physicalItemDiv">Physical Item</a></li>
			<li><a href="#electronicItemDiv">Electronic Item</a></li>
		</ul>
		
		<div id="physicalItemDiv">{{ physicalReserveForm.display }}</div>
		<div id="electronicItemDiv">{{ electronicReserveForm.display }}</div>
	</div>

	<script type="text/javascript">
	<!--
		$(function() {
			$("#tabs").tabs();
		});
			
		$(document).ready(function() {
			$("#tabs").bind('tabsshow', function (event, ui) { focusTextField(); } );
			toggleFields();
		});
	// -->
</script>

{% endblock %}