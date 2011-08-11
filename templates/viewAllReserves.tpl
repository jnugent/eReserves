{% extends "index.tpl" %}

{% block listspace %}

	<div id="chooseSemesterForm">
		{{ semesterForm.display }}
	</div>

	{% if items|length == 0 %}
			<p>There are no active reserves for the selected semester.</p>	
	{% else %}
		{% include "pagingOffset.tpl" %}
	<br />
		<p>Reserves are grouped according to course section.  A reserve may contain more than one file for you to download.</p> 
	<table class="reservesTable">

		{% include "viewAllLinks.tpl" %}

		{% set prevSectionID = 0 %}
		<tr>
			<th>Course Name</th><th>Course Number</th><th>Instructor(s)</th>
		</tr>
		{% for section in items %}
			{% set sectionID = section.getSectionID %}
			{% set reserves = section.getReserves %}
			{% if sectionID != prevSectionID %}
				<tr id="section{{ sectionID }}" {% if loop.index is even %}class="plain"{% endif %}>
					<td><a href="{{ basePath }}/index.php/viewReserves/{{ section.getSectionID }}">{{ section.getCourseName }}</a> ({{ section.getSectionNumber }})</td>
					<td>{{ section.getShortCourseCode }}</td>
						{% set instructors = section.getInstructors %}
					<td>{% if instructors != '' %}{{ instructors }}{% endif %}</td>
				</tr>
			{% endif %}
		{% endfor %}

		{% include "viewAllLinks.tpl" %}
	</table>
	{% endif %}
{% endblock %}
