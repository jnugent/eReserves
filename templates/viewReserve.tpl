{% extends "index.tpl" %}

{% block listspace %}

	{% if user.canAdministerSection(reservesRecord.getSectionID) %}
	
		<div id="modalDelete">
			<p>Are you sure you want to delete this item?</p>
		</div>
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
		<script type="text/javascript">
			<!--
			$(document).ready(function() {
				$("#modalDelete").dialog( { 
					autoOpen: false, 
					modal: true, 
					resizable: false, 
					draggable: false, 
					title: 'Confirm Delete' 
				} );
			});

			function showModal(url) {
				$("#modalDelete").dialog("option", 
					"buttons", [
						{ text: "Ok", click: function() { document.location.href = url; } },
						{ text: "Cancel", click: function() { $(this).dialog("close"); return false; } }
					]
				);
				$("#modalDelete").dialog("open");
			}

			// -->
		</script>
	{% endif %}

	<script type="text/javascript">
		<!--
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
			
		// -->
	</script>

	<h1>{{ reservesRecord.getTitle }} 
		{% if user.canAdministerSection(reservesRecord.getSectionID) %}
			(<a href="{{ basePath }}/index.php/adminCourseReserves/{{ reservesRecord.getItemHeadingID }}/{{ reservesRecord.getReservesRecordID }}">Edit</a>
		 | <a onClick="showModal('{{ basePath }}/index.php/deleteReservesRecord/{{ reservesRecord.getReservesRecordID }}')" href="#">Delete</a>
		 | <a href="{{ basePath }}/index.php/createReservesItem/{{ reservesRecord.getReservesRecordID }}/0">Add New Item</a>)
		 {% endif %}
	</h1>
	
	<p>
		{{ reservesRecord.getDetails }}
	</p>
	
	{% set electronicItems = reservesRecord.getElectronicItems %}
	{% set physicalItems = reservesRecord.getPhysicalItems(user) %}

	
		{% if electronicItems|length > 0 %}

			<p>The following electronic items have been placed on reserve:</p>

			<table id="electronicItems" class="reservesTable" cellpadding="5" border="0">
				<tr>
					<th>Item Title</th><th>Access</th><th>What kind of file is it?</th>
					{% if user.canAdministerSection(reservesRecord.getSectionID) %} 
						<th>Admin Options</th>
					{% endif %}
				</tr>
			{% for item in electronicItems %}
				<tr>
					<td>{{ item.getTitle }}</td>
					{% if not item.isRestricted or user.isLoggedIn %}
						<td><a href="{{ item.getURL }}">{{ item.getLinkTitle }}</a></td>
					{% else %}
						<td>{{ item.getLinkTitle }} (<a href="#" onClick="$('#loginForm').slideToggle(); $('#username').focus();">login</a> to access)</td>
					{% endif %}
					<td>{{ item.getMimeType }}</td>
					{% if user.canAdministerSection(reservesRecord.getSectionID) %}
						<td><a href="{{ basePath }}/index.php/editElectronicItem/{{ item.getElectronicItemID }}">Edit</a> | <a onClick="showModal('{{ basePath }}/index.php/deleteElectronicItem/{{ item.getElectronicItemID }}')" href="#">Delete</a></td>
					{% endif %}
				</tr>
			{% endfor %}
		</table><br />
		{% else %}
			<p>There are no electronic items on reserve at this time.</p>
		{% endif %}

		{% if physicalItems|length > 0 %}
		<br /><p>The following physical items have been placed on reserve:</p>
		<table id="physicalItems" class="reservesTable" cellpadding="5" border="0">
				<tr>
					<th>Item Call Number (click for catalogue record) <span id="progress" style="display: none;"><img src="{{ basePath }}/images/ajax-loader.gif" /></span></th><th>Item Location</th><th>Loan Period</th>
					{% if user.canAdministerSection(reservesRecord.getSectionID) %} 
						<th>Admin Options</th>
					{% endif %}
				</tr>
			{% for item in physicalItems %}
				{% if not item.isShadowed or (user.canAdministerSection(reservesRecord.getSectionID)) %}
				<tr>
					<td id="item-{{ item.getPhysicalItemID }}" {% if item.isShadowed %}style="text-decoration: line-through"{% endif %}><a href="javascript:getOPACRecord('{{ item.getPhysicalItemID }}')">{{ item.getCallNumber }}</a>{% if item.isShadowed %}(shadowed){% endif %}</td><td>{{ item.getLocation }}</td><td>{{ item.getLoanPeriod }}</td>
					{% if user.canAdministerSection(reservesRecord.getSectionID) %} 
						<td><a href="{{ basePath }}/index.php/editPhysicalItem/{{ item.getPhysicalItemID }}">Edit</a> | <a onClick="showModal('{{ basePath }}/index.php/deletePhysicalItem/{{ item.getPhysicalItemID }}')" href="#">Delete</a></td>
					{% endif %}
				</tr>
				{% endif %}
			{% endfor %}
		</table>
		{% else %}
			<p>There are no physical items on reserve at this time.</p>
		{% endif %}

{% endblock %}