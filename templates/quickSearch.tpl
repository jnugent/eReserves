{% extends "index.tpl" %}

{% block action %}

	{% block listspace %}

		{% if sections|length > 0 %}

			{% set start = pageOffset + 1 %}
			{% set end = start + sections|length - 1 %}

 			<p>Now showing {{ start }}-{{ end }} out of {{ totalRecords }} sections.  Would you like to <a href="#" id="searchLink">search again</a>?</p>
				<div id="searchForm" style="display: none;">
					{% for form in forms %}
						{{ form.display }} 
					{% endfor %}
				</div>
				<script>$("#searchLink").click( function() { $("#searchForm").slideToggle(); } );</script> 
				<table class="reservesTable" cellpadding="5" border="0">
					{% if pageOffset > 0 or sections|length == 25 %}
						<tr>
							<td>
								{% if pageOffset > 0 %}
								<a href="{{ basePath }}/index.php/{{ action }}/0/{{ pageOffset - 25 }}{% if action == 'quickSearch' %}/{{ keywords|urlencode }}/{{ semester|urlencode }}{% endif %}">View Previous Page</a>
								{% endif %}
							</td>
							<td>&nbsp;</td>
							<td style="text-align: right;">
								{% if sections|length == 25 %}
								<a href="{{ basePath }}/index.php/{{ action }}/0/{{ pageOffset + 25 }}{% if action == 'quickSearch' %}/{{ keywords|urlencode }}/{{ semester|urlencode }}{% endif %}">View Next Page</a>
								{% endif %}
							</td>
						</tr>
					{% endif %}
					
					{% set prevSectionID = 0 %}
					<tr>
						<th>Course Name</th><th>Course Number</th><th>Instructor(s)</th>
					</tr>
					{% for section in sections %}
						{% set sectionID = section.getSectionID %}
						{% set reserves = section.getReserves %}
						{% if sectionID != prevSectionID %}
							<tr id="section{{ sectionID }}" {% if loop.index|even %}class="plain"{% endif %}>
								<td><a href="{{ basePath }}/index.php/viewReserves/{{ section.getSectionID }}">{{ section.getCourseName }}</a></td>
								<td>{{ section.getShortCourseCode }}</td>
									{% set instructors = section.getInstructors %}
								<td>{% if instructors != '' %}{{ instructors }}{% endif %}</td>
							</tr>
						{% endif %}
					{% endfor %}
				</table>
		{% else %}
 			<p>No sections with reserves were found.  Try again?</a></p>
			{% for form in forms %}
				{{ form.display }} 
			{% endfor %} 
		{% endif %}

	{% endblock %}

{% endblock %}
