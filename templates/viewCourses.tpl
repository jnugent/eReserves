{% extends "index.tpl" %}

{% block action %}

	{% if user.isLoggedIn %}

		{% if user.isAdmin and not user.isActing %}
			<p>Because you are a reserves administrator, you can view and edit all courses.</p>
		{% endif %}

		{% if items|length > 0 %}

			<p>
				{% include "pagingOffset.tpl" %}
			</p>

		{% include "courseListTemplate.tpl" %}

		{% endif %}
	{% endif %}

{% endblock %}