{% extends "index.tpl" %}

{% block listspace %}



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

	{% if section.getSectionID > 0 %}
		{% set instructors = section.getInstructors %}	
		<h1>{{ section.getPrefix }} {{ section.getNumber }} - {{ section.getCourseName }} <br />
			<span class="small">{{ section.getCalendarCourseCode }}{% if instructors != '' %}<br />Instructors: {{ instructors }}{% endif %}</span>
		</h1>
			{%if canAdmin %}
				<a onClick="showAddItemModal('{{ basePath }}/index.php/adminCourseReserves/')" href="#">Add New Item</a> |
				<a href="{{ basePath }}/index.php/itemHeadings/{{ section.getSectionID }}/0">Manage Headings</a> |
				<a href="{{ basePath }}/index.php/assignPeople/{{ section.getSectionID }}">(Un)assign People</a>  |
				<a href="{{ basePath }}/index.php/editSection/{{ section.getSectionID }}">Edit Section</a> 
				
			{% endif %}

		{% if itemHeadings|length == 0 %}
			<p>There are no items on reserve for this section at this time.</p>
		{% else %}
		
			{% for heading in itemHeadings %}
				<table id="itemHeadings{{ loop.index }}" class="reservesTable">
				
					<tr id="heading-{{ heading.getItemHeadingID }}">
						<th colspan="{% if canAdmin %}4{% else %}3{% endif %}">{{ heading.getHeadingName|e }}</th>
					</tr>
					{% if heading.getListedReserves|length > 0 %}
						{% for reserve in heading.getListedReserves %}
							{% set totalNumber = reserve.getTotalNumberOfItems(user) %}
							<tr class="firstRow{% if loop.index is even %} plain{% endif %}">
								{% if totalNumber == 1 %}
									{% set recordInfo = reserve.getSingleItem(user, basePath) %}
									{% if recordInfo.type == 'e' %}
										<td style="width: 50%" id="e{{ recordInfo.id }}">
											{% if recordInfo.loginRequired == false %}
												<strong><a rel="external" href="{{ recordInfo.url }}" class="noicon {{ recordInfo.class }}">{{ recordInfo.title }}</a></strong>
												{% autoescape false %}{{ recordInfo.info }}{% endautoescape %}
											{% else %}
												{% set displayLoginMessage = true %}
												<strong><a rel="external" href="{{ recordInfo.url }}" class="noicon {{ recordInfo.class }}">{{ recordInfo.title }}</a></strong> 
												{% autoescape false %}{{ recordInfo.info }}{% endautoescape %}
												<img src="{{ basePath }}/images/lock.png" title="please login to access this item" height="15" />
												
											{% endif %}
										</td>
										<td style="width: 25%">{{ recordInfo.display }}</td>
										<td>&nbsp;</td>
										{% if canAdmin %}
											<td>
												<a href="{{ basePath }}/index.php/editElectronicItem/{{ recordInfo.id }}">Edit</a> | 
												<a onClick="showDeleteModal('{{ basePath }}/index.php/deleteElectronicItem/{{ recordInfo.id }}')" href="#">Delete</a>
											</td>
										{% endif %}
										</tr>
										{% if recordInfo.notes != '' %}
											<tr class="secondRow{% if loop.index is even %} plain{% endif %}">
												<td colspan="{% if canAdmin %}4{% else %}3{% endif %}">{{ recordInfo.notes }}</td>
										{% endif %}
									{% else %}
										<td id="p{{ recordInfo.id }}">
											<a href="{{ recordInfo.url }}" class="{{ recordInfo.class }}"><strong>{{ recordInfo.title }}</strong></a>
										</td>
										<td id="p{{ recordInfo.id }}-where"></td><td id="p{{ recordInfo.id }}-avail"></td>
										{% if canAdmin %}
											<td>
												<a href="{{ basePath }}/index.php/editPhysicalItem/{{ recordInfo.id }}">Edit</a> | 
												<a onClick="showDeleteModal('{{ basePath }}/index.php/deletePhysicalItem/{{ recordInfo.id }}')" href="#">Delete</a>
											</td>
										{% endif %}
									{% endif %}
								{% else %} 
									{% if totalNumber > 1 %}
										
										<td style="width: 50%"><a href="{{ basePath }}/index.php/viewReserve/{{ reserve.getReservesRecordID }}"><strong>{{ reserve.getTitle }}</strong></a></td>
										<td style="width: 50%" colspan="{% if canAdmin %}3{% else %}2{% endif %}">More than one item.  Click to view them.</td>
									{% else %} 
										{%if user.isAdmin %}
											<td style="width: 50%"><a href="{{ basePath }}/index.php/viewReserve/{{ reserve.getReservesRecordID }}"><strong>{{ reserve.getTitle }}</strong></a></td>
											<td style="width: 50%" colspan="{% if canAdmin %}3{% else %}2{% endif %}">
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

	window.onload = externalLinks;

	</script>

{% endblock %}
