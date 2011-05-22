{% extends "index.tpl" %}

{% block listspace %}

	<script type="text/javascript" src="{{ basePath }}/js/jquery.autocomplete.js"></script>

	<script type="text/javascript">
		<!--
			$("#instructor").autocomplete(
			"{{ basePath }}/index.php/findInstructorAssign/{{ sectionID }}/0",
			{
				delay:10,
				minChars:3,
				matchSubset:1,
				matchContains:1,
				cacheLength:10,
				onItemSelect:selectItem,
				onFindValue:findValue,
				formatItem:formatItem,
				autoFill:true,
				width:285
			}
		);
		// -->
	</script>

	{% if sectionRoles|length == 0 %}
		<p>There are no other instructors assigned to this section.</p>
	{% else %}

		<table class="reservesTable">
			<caption>The following user ids have been assigned to this section.</caption>
			<tr><th>User ID</th><th>Assigned Role</th><th>&nbsp;</th></tr>
			{% for user,role in sectionRoles %}
				<tr><td>{{ user }}</td><td>{{ role.roleDesc }}</td><td><a href="{{ basePath }}/index.php/assignPeople/{{ sectionID }}/{{ user }}">Remove?</a></td></tr>
			{% endfor %}
		</table>
	{% endif %}

{% endblock %}
