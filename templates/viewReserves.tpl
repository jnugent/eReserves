{% extends "index.tpl" %}

{% block listspace %}

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
						htmlStr = jsonObject.title + '<br />' + jsonObject.author + '<br />' + jsonObject.callNumber;
						$('#' + itemID + '-where').html(jsonObject.location + '<br />' + jsonObject.library);
						if (jsonObject.checkedOut == '0') {
							$('#' + itemID + '-where').html(jsonObject.location + '<br />' + jsonObject.library);
							$('#' + itemID + '-avail').html(jsonObject.loanPeriod);
						} else {
							$('#' + itemID + '-avail').html('Checked Out: Due back @ ' + jsonObject.dueBack);
						}
					}
					$("#" + itemID).html(htmlStr);
					$("#progress").toggle();
				}
			);
		}
		// -->
		</script>

	{%if user.canAdministerSection(section.getSectionID) %}
		{% set canAdmin = true %}
		
		<div id="modalDelete">
			<p>Are you sure you want to delete this item?</p>
		</div>
		
		<div id="modalAddItem">
			{% set existingItemHeadings = section.getHeadings %}
			{% if existingItemHeadings|length > 0 %}
				<p>Select a heading for this new reserve item:</p>
				<select name="itemHeadingID" id="itemHeadingID">
					{% for itemHeading in existingItemHeadings %}
						<option value="{{ itemHeading.getItemHeadingID }}">{{ itemHeading.getHeadingName }}</option>
					{% endfor %}
				</select>
			{% else %}
				<p>There are no headings for this reserve. A default one called "{{ defaultHeadingTitle }}" will be created.</p>
			{% endif %}
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
				
			$("#modalAddItem").dialog( { 
					autoOpen: false, 
					modal: true, 
					resizable: false, 
					draggable: false, 
					title: 'Add New Reserves Item' 
				} );
			});

			function showDeleteModal(url) {
				$("#modalDelete").dialog("option", 
					"buttons", [
						{ text: "Ok", click: function() { document.location.href = url; } },
						{ text: "Cancel", click: function() { $(this).dialog("close"); return false; } }
					]
				);
				$("#modalDelete").dialog("open");
			}

			function showAddItemModal(url) {
				$("#modalAddItem").dialog("option", 
					"buttons", [
						{ text: "Ok", click: function() { 
						
								chosenHeadingID = $("#itemHeadingID").val();
								otherID = {{ section.getSectionID }};
								if (chosenHeadingID == null || chosenHeadingID == '') {
									chosenHeadingID = 0;
								} else {
									otherID = 0;
								}
								document.location.href = url + chosenHeadingID + '/' + otherID;
							} },
						{ text: "Cancel", click: function() { $(this).dialog("close"); return false; } }
					]
				);
				$("#modalAddItem").dialog("open");
			}
			// -->
		</script>
	{% endif %}
	<script type="text/javascript">
		function externalLinks() { 
			if (!document.getElementsByTagName) return; 
			var anchors = document.getElementsByTagName("a"); 
			for (var i=0; i<anchors.length; i++) { 
				var anchor = anchors[i]; 
				if (anchor.getAttribute("href") && anchor.getAttribute("rel") == "external") anchor.target = "_blank"; } 
			} 
			window.onload = externalLinks;
	</script>

	{% if section.getSectionID > 0 %}
		{% set instructors = section.getInstructors %}	
		<h1>{{ section.getPrefix }} {{ section.getNumber }} - {{ section.getCourseName }} <br />
			<span class="small">{{ section.getCalendarCourseCode }}{% if instructors != '' %}<br />Instructors: {{ instructors }}{% endif %}</span>
		</h1>
			{%if user.isAdmin %}<a href="{{ basePath }}/index.php/editSection/{{ section.getSectionID }}">Edit Section</a> {% endif %}
			{%if canAdmin %}
				| <a href="{{ basePath }}/index.php/assignPeople/{{ section.getSectionID }}">Assign People</a> | 
				<a href="{{ basePath }}/index.php/itemHeadings/{{ section.getSectionID }}/0">Manage Headings</a> |
				<a onClick="showAddItemModal('{{ basePath }}/index.php/adminCourseReserves/')" href="#">Add New Item</a>
			{% endif %}

		{% if itemHeadings|length == 0 %}
			<p>There are no items on reserve for this section at this time.</p>
		{% else %}
		
			{% for heading in itemHeadings %}
				<table id="itemHeadings" class="reservesTable">
				
					<tr id="{{ heading.getItemHeadingID }}">
						<th colspan="{% if canAdmin %}4{% else %}3{% endif %}">{{ heading.getHeadingName|e }}</th>
					</tr>
					{% if heading.getListedReserves|length > 0 %}
						{% for reserve in heading.getListedReserves %}
							<tr {% if loop.index is even %}class="plain"{% endif %}>
								{% set totalNumber = reserve.getTotalNumberOfItems(user) %}
								{% if totalNumber == 1 %}
									{% set recordInfo = reserve.getSingleItem(user, basePath) %}
									{% if recordInfo.type == 'e' %}
										<td width="50%" id="{{ recordInfo.id }}">
											{% if recordInfo.loginRequired == false %}
												<a rel="external" href="{{ recordInfo.url }}" class="{{ recordInfo.class }}">{{ recordInfo.title }}</a>
											{% else %}
												{% set displayLoginMessage = true %}
												{{ recordInfo.title }} <img src="{{ basePath }}/images/lock.png" title="please login to access this item" height="25" />
											{% endif %}
										</td>
										<td width="25%">{{ recordInfo.display }}</td>
										<td width="*">{% autoescape false %}{{ recordInfo.info }}{% endautoescape %}</td>
										{% if canAdmin %}
											<td width="*">
												<a href="{{ basePath }}/index.php/editElectronicItem/{{ recordInfo.id }}">Edit</a> | 
												<a onClick="showDeleteModal('{{ basePath }}/index.php/deleteElectronicItem/{{ recordInfo.id }}')" href="#">Delete</a>
											</td>
										{% endif %}
										</tr>
										{% if recordInfo.notes != '' %}
											<tr>
												<td colspan="{% if canAdmin %}4{% else %}3{% endif %}">{{ recordInfo.notes }}</td>
											</tr>
										{% endif %}
									{% else %}
										<td id="{{ recordInfo.id }}">
											<a href="{{ recordInfo.url }}" class="{{ recordInfo.class }}">{{ recordInfo.title }}</a>
										</td>
										<td id="{{ recordInfo.id }}-where"></td><td id="{{ recordInfo.id }}-avail"></td>
										{% if canAdmin %}
											<td width="*">
												<a href="{{ basePath }}/index.php/editPhysicalItem/{{ recordInfo.id }}">Edit</a> | 
												<a onClick="showDeleteModal('{{ basePath }}/index.php/deletePhysicalItem/{{ recordInfo.id }}')" href="#">Delete</a>
											</td>
										{% endif %}
									{% endif %}
								{% else %} 
									{% if totalNumber > 1 %}
										<td width="50%"><a href="{{ basePath }}/index.php/viewReserve/{{ reserve.getReservesRecordID }}">{{ reserve.getTitle }}</a></td>
										<td width="50%"colspan="2">More than one item.  Click to view them.</td>
									{% else %} 
										{%if user.isAdmin %}
											<td width="50%"><a href="{{ basePath }}/index.php/viewReserve/{{ reserve.getReservesRecordID }}">{{ reserve.getTitle }}</a></td>
											<td width="50%"colspan="{% if canAdmin %}3{% else %}2{% endif %}">
												No items added.  You're an admin and users won't see this. 
												<a onClick="showDeleteModal('{{ basePath }}/index.php/deleteReservesRecord/{{ reserve.getReservesRecordID }}')" href="#">Delete Completely</a> | 
												<a href="{{ basePath }}/index.php/createReservesItem/{{ reserve.getReservesRecordID }}/0">Add an Item</a>
											</td>
										{% endif %}
									{% endif %}
								{% endif %}
							</tr> 
						{% endfor %}
					{% else %}
						<tr><td colspan="3">There are no reserves in this category.</td></tr>
					{% endif %}
				</table><br />
			{% endfor %}
			
	{% endif %}
	{% else %}
		<p>There are no sections specifically assigned to your account at this time.</p>
	{% endif %}

	<script type="text/javascript">
		$('.opacLink').parent().each( function (index) { getOPACRecord( $(this).attr('id') ); }  );

	{% if displayLoginMessage %}

	$(document).ready(function() {
		$('#enrollmentRequired').show();
	});
	{% endif %}

	</script>

{% endblock %}
