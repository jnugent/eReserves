{% extends "index.tpl" %}

{% block action %}

	{% if user.isLoggedIn %}

		{% if user.isAdmin and not user.isActing %}
			<p>Because you are a reserves administrator, you can view and edit all courses.</p>
		{% endif %}

		{% if courses|length > 0 %}

			{% set start = pageOffset + 1 %}
			{% set end = start + courses|length - 1 %}

			<p>Now showing {{ start }}-{{ end }} out of {{ totalRecords }} courses.</p>

		{% include "courseListTemplate.tpl" %}

		{% endif %}
	{% endif %}

{% endblock %}