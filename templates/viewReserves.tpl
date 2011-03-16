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
						htmlStr = jsonObject.title + ' / ' + jsonObject.author + ' / ' + jsonObject.callNumber;
						$('#' + itemID + '-where').html(jsonObject.location + ' / ' + jsonObject.library);
						if (jsonObject.checkedOut == '0') {
							$('#' + itemID + '-where').html(jsonObject.location + ' / ' + jsonObject.library);
							$('#' + itemID + '-avail').html('AVAILABLE');
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

	{% if section.getSectionID > 0 %}
		{% set instructors = section.getInstructors %}	
		<h1>{{ section.getPrefix }} {{ section.getNumber }} - {{ section.getCourseName }} <br />
			<span class="small">{{ section.getCalendarCourseCode }}{% if instructors != '' %}, Instructors: {{ instructors }}{% endif %}</span>
		</h1>
			{%if user.isAdmin %}(<a href="{{ basePath }}/index.php/editSection/{{ section.getSectionID }}">Edit Section</a>){% endif %}
			{%if user.canAdministerSection(section.getSectionID) %}(<a href="{{ basePath }}/index.php/assignPeople/{{ section.getSectionID }}">Assign People</a>){% endif %}

		
		{% if itemHeadings|length == 0 %}
			<p>No item headings were found for this section. {%if user.canAdministerSection(section.getSectionID) %}<a href="{{ basePath }}/index.php/itemHeadings/{{ section.getSectionID }}/0">Create a Heading</a>?{% endif %}</p>
		{% else %}
		
		<p>{%if user.canAdministerSection(section.getSectionID) %}<a href="{{ basePath }}/index.php/itemHeadings/{{ section.getSectionID }}/0">Organize or Manage Reserves?</a>{% endif %}</p>

			{% for heading in itemHeadings %}
				<table id="itemHeadings" class="reservesTable">
				
					<tr id="{{ heading.getItemHeadingID }}">
						<th colspan="3">{{ heading.getHeadingName|e }}</th>
					</tr>
					{% if heading.getListedReserves|length > 0 %}
						{% for reserve in heading.getListedReserves %}
							<tr  {% if loop.index|even %}class="plain"{% endif %}>
								{% set totalNumber = reserve.getTotalNumberOfItems(user) %}
								{% if totalNumber == 1 %}
								{% set recordInfo = reserve.getSingleItem(user, basePath) %}
									{% if recordInfo.type == 'e' %}
										<td width="270" id="{{ recordInfo.id }}">
											{% if recordInfo.loginRequired == false %}
												<a href="{{ recordInfo.url }}" class="{{ recordInfo.class }}">{{ recordInfo.title }}</a>
											{% else %}
												{{ recordInfo.title }} (login to access)
											{% endif %}
										</td>
										<td width="250">{{ recordInfo.display }}</td>
										<td width="150">{{ recordInfo.info }}</td>
									{% else %}
										<td id="{{ recordInfo.id }}">
											<a href="{{ recordInfo.url }}" class="{{ recordInfo.class }}">{{ recordInfo.title }}</a>
										</td>
										<td id="{{ recordInfo.id }}-where"></td><td id="{{ recordInfo.id }}-avail"></td>
									{% endif %}
								{% else %} 
									{% if totalNumber > 1 %}
										<td width="270"><a href="{{ basePath }}/index.php/viewReserve/{{ reserve.getReservesRecordID }}">{{ reserve.getTitle }}</a></td>
										<td width="400"colspan="2">More than one item.  Click to view them.</td>
									{% else %} 
										{%if user.isAdmin %}
											<td width="270"><a href="{{ basePath }}/index.php/viewReserve/{{ reserve.getReservesRecordID }}">{{ reserve.getTitle }}</a></td>
											<td width="400"colspan="2">No items added.  You're an admin and users won't see this.</td>
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
	</script>
{% endblock %}
