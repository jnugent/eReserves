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

{% endblock %}