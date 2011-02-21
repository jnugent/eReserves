{{ page.getHeader(user) }}

{%block greeting %}
	{% if user.isAnonymous %}
		<p>Welcome, Guest! <a href="#" id="loginLink">Login?</a></p>
		<div id="loginForm" style="display: none;">
			{{ loginForm.display }}
		</div>
		<script>$("#loginLink").click( function() { $("#loginForm").slideToggle(); $("#username").focus(); } );</script>
		
	{% endif %}
	
	{% if user.isActing %}
		<p style="padding: 2px; background-color: #ffffcc; text-align: center; ">You have assumed the role 
			of <em>{{ user.getAssumedFullName }}</em> (<a href="{{ basePath }}/index.php/assumeUserRole/">stop</a>)</p>
	{% endif %}
{% endblock %}

{% block breadcrumb %}
	<div id="reservesBreadCrumb"><p>{{ breadCrumb }}</p></div>
{% endblock %}

{% block action %}
	{% if opPerformed %}
		<div class="updateSuccess"><p>Your administration operation was performed successfully.</p></div>
	{% endif %}
	
	{% for form in forms %}
		{{ form.display }}
	{% endfor %}
	
	{% block listspace %}{% endblock %}
	
{% endblock %}
{{ extraJS }}
{{ page.getFooter(user) }}
