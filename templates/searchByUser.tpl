{% extends "index.tpl" %}

{% block listspace %}

	{% if validEntries|length > 0 %}
		<br />
		<table id="validEntries" class="reservesTable">
			<tr>
				<th>User ID</th><th>User's Name</th><th>Email Address</th>
			</tr>
			{% for uid, terms in validEntries %}
			<tr {% if loop.index|even %}class="plain"{% endif %}>
				<td><a href="{{ basePath }}/index.php/searchByUser/{{ uid }}">{{ uid }}</a></td><td>{{ terms[1] }}</td><td><a href="mailto:{{ email }}">{{ terms[0] }}</a></td>
			</tr>
		{% endfor %}
		</table>
		{% else %}
		{% if sections|length > 0 %}
				<p>Now showing the {{ sections|length }} active sections for {{ commonName }}. <a href="{{ basePath }}/index.php/assumeUserRole/{{ emailID }}">Become this user?</a></p>
				{% include "sectionListTemplate.tpl" %}	
		{% elseif commonName != '' %}
				<p>No sections for <strong>{{ commonName }}</strong> were found. Try a different ID?</p>
		{% elseif emailID != '' %}
			<p>No user with that Email ID of "{{ emailID }}" exists. Try a different ID?</p>
		{% endif %}
	{% endif %}
{% endblock %}
