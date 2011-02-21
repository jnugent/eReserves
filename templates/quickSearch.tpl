{% extends "index.tpl" %}

{% block action %}

	{% block listspace %}

		{% if courses|length > 0 %}

			{% set start = pageOffset + 1 %}
			{% set end = start + courses|length - 1 %}

 			<p>Now showing {{ start }}-{{ end }} out of {{ totalRecords }} courses.  Would you like to <a href="#" id="searchLink">search again</a>?</p>
				<div id="searchForm" style="display: none;">
					{% for form in forms %}
						{{ form.display }} 
					{% endfor %}
				</div>
				<script>$("#searchLink").click( function() { $("#searchForm").slideToggle(); } );</script> 
 				{% include "courseListTemplate.tpl" %}
		{% else %}
 			<p>No courses were found.  Try again?</a></p>
			{% for form in forms %}
				{{ form.display }} 
			{% endfor %} 
		{% endif %}

	{% endblock %}

{% endblock %}
