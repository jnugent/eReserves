{% extends "index.tpl" %}

{% block listspace %}

	{% if reports|length > 0 %}

		<table class="reservesTable">
			<tr><th>Report Title</th><th>&nbsp;</th></tr>
			{% for report in reports %}
				<tr><td>{{ report }}</td><td><a href="{{ basePath }}/reports/{{ report }}">download</a></td></tr>
			{% endfor %}
		</table>
	{% else %}
		<p>There are no generated reports. </p>
	{% endif %}

{% endblock %}
