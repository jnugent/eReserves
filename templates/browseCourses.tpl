{% extends "index.tpl" %}

{% block action %}

	<p>Here are the course prefixes that you may find relevant. The number in parentheses after each prefix is the number of sections listed within that area of study. Please note that
	there may be more courses present.</p>

	{% for letter, prefixes in letterSections %}
	
		<h4>{{ letter }}</h4>
		{% for prefix, total in prefixes %}
			<a href="{{ basePath }}/index.php/quickSearch/0/0/{{ prefix }}">{{ prefix }} ({{ total }})</a>{%if loop.index < prefixes|length %}, {% endif %}
		{% endfor %}
	{% endfor %}

{% endblock %}