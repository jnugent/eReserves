{% extends "index.tpl" %}

{% block action %}

	{% block listspace %}

		{% if items|length > 0 %}

			<p>
				{% include "pagingOffset.tpl" %}
				 Would you like to <a href="#" id="searchLink">search again</a>?</p>
				<div id="searchForm" style="display: none;">
					{% for form in forms %}
						{{ form.display }} 
					{% endfor %}
				</div>
				<script>$("#searchLink").click( function() { $("#searchForm").slideToggle(); } );</script> 
				<table class="reservesTable" cellpadding="5" border="0">
					{% include "pageLinks.tpl" %}
					
					{% set prevSectionID = 0 %}
					<tr>
						<th>Course Name</th><th>Course Number</th><th>Instructor(s)</th>
					</tr>
					{% for section in items %}
						{% set sectionID = section.getSectionID %}
						{% set reserves = section.getReserves %}
						{% if sectionID != prevSectionID %}
							<tr id="section{{ sectionID }}" {% if loop.index is even %}class="plain"{% endif %}>
								<td><a href="{{ basePath }}/index.php/viewReserves/{{ section.getSectionID }}">{{ section.getCourseName }}</a></td>
								<td>{{ section.getShortCourseCode }}</td>
									{% set instructors = section.getInstructors %}
								<td>{% if instructors != '' %}{{ instructors }}{% endif %}</td>
							</tr>
						{% endif %}
					{% endfor %}
					{% include "pageLinks.tpl" %}
				</table>
		{% else %}
 			<p>No matching sections {%if not user.isAdmin %}with reserves{% endif %} were found.  Try again?</p>
			{% for form in forms %}
				{{ form.display }} 
			{% endfor %} 
		{% endif %}

	{% endblock %}

{% endblock %}