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
				{% if prefixSuggest|length > 0 and not corrected %}
					Were you looking for 
						{% for prefix in prefixSuggest %}
							<a href="{{ basePath }}/index.php/quickSearch/0/0/{{ prefix }}/{{ semester }}?corrected=1">{{ prefix }}</a>{% if loop.index + 1 < prefixSuggest|length %}, 
							{% elseif prefixSuggest|length > 1 %} or {% endif %}
						{% endfor %}
					courses?
				{% endif %}
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
						{% if yearterm != section.getYearTerm %}
							{% set yearterm = section.getYearTerm %}
							<tr class="sectionHeader"><td colspan="3">{{ yearterm }}</td></tr>
						{% endif %}
							<tr id="section{{ sectionID }}" {% if loop.index is even %}class="plain"{% endif %}>
								<td><a href="{{ basePath }}/index.php/viewReserves/{{ section.getSectionID }}">{{ section.getCourseName }}</a> ({{ section.getSectionNumber }})</td>
								<td>{{ section.getShortCourseCode }}</td>
									{% set instructors = section.getInstructors %}
								<td>{% if instructors != '' %}{{ instructors }}{% endif %}</td>
							</tr>
						{% endif %}
					{% endfor %}
					{% include "pageLinks.tpl" %}
				</table>
		{% else %}
 			<p class="caution">No matching sections {%if not user.isAdmin %}with reserves{% endif %} were found.
                                {% if prefixSuggest|length > 0 and not corrected %}
                                        Were you looking for
                                                {% for prefix in prefixSuggest %}
                                                        <a href="{{ basePath }}/index.php/quickSearch/0/0/{{ prefix }}/{{ semester }}?corrected=1">{{ prefix }}</a>{% if loop.index + 1 < prefixSuggest|length %},
                                                        {% elseif prefixSuggest|length > 1 %} or {% endif %}
                                                {% endfor %}
                                        courses?
                                {% endif %}
</p>
			{% for form in forms %}
				{{ form.display }} 
			{% endfor %} 
		{% endif %}

	{% endblock %}

{% endblock %}
