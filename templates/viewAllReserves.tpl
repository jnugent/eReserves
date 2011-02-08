{% extends "index.tpl" %}

{% block listspace %}

	<div id="chooseSemesterForm">
		<script type="text/javascript" src="{{ basePath }}/js/reserves.js"></script>
		{{ semesterForm.display }}
	</div>

	{% if sections|length == 0 %}
			<p>There are no active reserves for the selected semester.</p>	
	{% else %}
	<script type="text/javascript" src="{{ basePath }}"></script>
	<table class="reservesTable" cellpadding="5" border="0">
		<caption><p>There {% if sections|length != 1 %}are{% else %}is{% endif %} {{ sections|length }} section{% if sections|length != 1 %}s{% endif %} with added reserves for this semester. 
		Reserves are grouped according to course section.  A reserve may contain more than one file for you to download.</p> </caption>

		{% set prevSectionID = 0 %}
		
		{% for section in sections %}
			{% set sectionID = section.getSectionID %}
			{% set reserves = section.getReserves %}
			{% if sectionID != prevSectionID %}
				{% set prevSectionID = sectionID %}
				<tr id="section{{ sectionID }}" {% if loop.index|even %}class="plain"{% endif %}>
					<th colspan="3" style="text-align: center">
						<a href="{{ basePath }}/index.php/viewReserves/{{ section.getSectionID }}">{{ section.getCalendarCourseCode|e }}</a>
					</th>
				</tr>
			{% endif %}

			{% for reserve in reserves %}
				<tr id="{{ reserve.getReservesRecordID }}">
					{% set physicalItems   = reserve.getPhysicalItems(user, sectionID, TRUE) %}
					{% set electronicItems = reserve.getElectronicItems(TRUE) %}

					<td>{{ loop.index }}</td>
					<td><a href="{{ basePath }}/index.php/viewReserve/{{ reserve.getReservesRecordID }}">{{ reserve.getTitle }}</td>
					<td>{{ physicalItems|length }} physical item{% if physicalItems|length != 1 %}s{% endif %}, and {{ electronicItems|length }} 
						electronic one{% if electronicItems|length != 1 %}s{% endif %}, filed in {{ reserve.getHeadingTitle }}.</td>
				</tr>
			{% endfor %}
		{% endfor %}
	</table>
	{% endif %}
{% endblock %}
