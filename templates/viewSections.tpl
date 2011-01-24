{% extends "index.tpl" %}

{% block listspace %}

	<p>Not what you were looking for?  Try a more general search for <a href="{{ basePath }}/index.php/quickSearch/0/0/{{ course.getPrefix() }}">{{ course.getPrefix() }}</a> or 
	<a href="{{ basePath }}/index.php/quickSearch/0/0/{{ course.getNumber() }}">{{ course.getNumber() }}</a> instead.</p>

	{% if sections|length == 0 %}
 		<p>No sections were found for {{ course.getCourseName }}.</p>
	{% else %}

		{% include "sectionListTemplate.tpl" %}

 	{% endif %}

{% endblock %}