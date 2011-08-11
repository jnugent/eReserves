{% extends "index.tpl" %}

{% block listspace %}

	{% if user.canAdministerSection(reservesRecord.getSectionID) %}
	
		{% set canAdmin = true %}
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

	<h1>{{ reservesRecord.getTitle }}</h1>
	
		{% if canAdmin %}
			<a href="{{ basePath }}/index.php/adminCourseReserves/{{ reservesRecord.getItemHeadingID }}/{{ reservesRecord.getReservesRecordID }}">Edit</a>
		 | <a onClick="showModal('{{ basePath }}/index.php/deleteReservesRecord/{{ reservesRecord.getReservesRecordID }}')" href="#">Delete</a>
		 | <a href="{{ basePath }}/index.php/createReservesItem/{{ reservesRecord.getReservesRecordID }}/0">Add New Item</a>
		 {% endif %}
	
	<p>
		{{ reservesRecord.getDetails }}
	</p>
	
	{% set electronicItems = reservesRecord.getElectronicItems %}
	{% set physicalItems = reservesRecord.getPhysicalItems(user) %}

	
		{% if electronicItems|length > 0 %}

			<p>The following electronic items have been placed on reserve:</p>

			<table id="electronicItems" class="reservesTable" cellpadding="5" border="0">
				<tr>
					<th>Item Title</th><th>Access</th><th>File Type</th>
					{% if canAdmin %} 
						<th>Options</th>
					{% endif %}
				</tr>
			{% for item in electronicItems %}
				{% set clearanceStatus = item.isShadowed %}
				{% if clearanceStatus or canAdmin %}
				<tr class="plain">
					<td style="width: 40%"><strong>{{ item.getTitle }}</strong>{% if not clearanceStatus %} <em>Clearance: {{ item.getClearanceStatus }}</em> {% endif %}</td>
					{% if not item.isRestricted or user.isAdmin or ( user.isLoggedIn and not item.requiresEnrolment ) or reservesRecord.getSection.userIsEnrolled(user.getUserName) %}
						<td><strong><a rel="external" class="noicon" href="{{ item.getURL }}">{{ item.getLinkTitle }}</a></strong></td>
					{% elseif item.requiresEnrolment %}
						{% set displayLoginMessage = true %}
						<td><strong>{{ item.getLinkTitle }}</strong> <img src="{{ basePath }}/images/lock.png" title="please login to access this item" height="15" /></td>
					{% else %}
						{% set displayLoginMessage = true %}
						<td><strong>{{ item.getLinkTitle }}</strong> <img src="{{ basePath }}/images/lock.png" title="please login to access this item" height="15" /></td>
					{% endif %}
					<td align="center"><img src="{{ basePath }}/images/mimeIcons/{{ item.mapTypeToImg }}.png" height="15" /></td>
					{% if canAdmin %}
						<td><a href="{{ basePath }}/index.php/editElectronicItem/{{ item.getElectronicItemID }}">Edit</a> | <a onClick="showModal('{{ basePath }}/index.php/deleteElectronicItem/{{ item.getElectronicItemID }}')" href="#">Delete</a></td>
					{% endif %}
				</tr>
				{% endif %}
				<tr>
					<td colspan="{%if canAdmin %}4{% else %}3{% endif %}"><span style="margin-left: 20px;">{{ item.getNotes }}</span></td>
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
					<th>Item Information<span id="progress" style="display: none;"><img src="{{ basePath }}/images/ajax-loader.gif" /></span></th><th>Item Location</th><th>Loan Period</th>
					{% if canAdmin %} 
						<th>Admin Options</th>
					{% endif %}
				</tr>
			{% for item in physicalItems %}
				{% if not item.isShadowed or canAdmin %}
				<tr>
					<td id="p{{ item.getPhysicalItemID }}" {% if item.isShadowed %}style="text-decoration: line-through"{% endif %}><a class="opacLink" href="javascript:getOPACRecord('{{ item.getPhysicalItemID }}')">Load Quest Record</a>{% if item.isShadowed %}(shadowed){% endif %}</td>
					<td id="p{{ item.getPhysicalItemID }}-where">&nbsp;</td><td id="p{{ item.getPhysicalItemID }}-avail">&nbsp;</td>
					{% if canAdmin %} 
						<td><a href="{{ basePath }}/index.php/editPhysicalItem/{{ item.getPhysicalItemID }}">Edit</a> | <a onClick="showModal('{{ basePath }}/index.php/deletePhysicalItem/{{ item.getPhysicalItemID }}')" href="#">Delete</a></td>
					{% endif %}
				</tr>
				{% endif %}
			{% endfor %}
		</table>
		{% else %}
			<p>There are no physical items on reserve at this time.</p>
		{% endif %}

	<script type="text/javascript">
		$('.opacLink').parent().each( function (index) { getOPACRecord( $(this).attr('id') ); }  );
		{% if displayLoginMessage %}

		$(document).ready(function() {
			$('#enrollmentRequired').show();
		});
		
	{% endif %}
	
	window.onload = externalLinks;

	</script>
{% endblock %}
