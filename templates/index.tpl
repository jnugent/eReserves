{{ page.getHeader(user) }}

{%block greeting %}
	{% if user.isActing %}
		<p style="padding: 2px; background-color: #ffffcc; text-align: center; ">You have assumed the role 
			of <em>{{ user.getAssumedFullName }}</em> (<a href="{{ basePath }}/index.php/assumeUserRole/">stop</a>)</p>
	{% endif %}
{% endblock %}

{% block breadcrumb %}
	<div id="reservesBreadCrumb"><p>{% autoescape false %}{{ breadCrumb }}{% endautoescape %}</p></div>
{% endblock %}

{% set displayLoginMessage = false %}

{% block action %}
	{% if opPerformed %}
		<div class="updateSuccess"><p>Your administration operation was performed successfully.</p></div>
	{% endif %}
	
	{% for form in forms %}
		{{ form.display }}
	{% endfor %}
	
	{% block listspace %}{% endblock %}
	
{% endblock %}
{% autoescape false %}
{{ extraJS }}
{% endautoescape %}
{{ page.getFooter(user) }}
