		<br />
		<style type="text/css">
			td.action { text-align: center; }
		</style>
		<table class="reservesTable" cellpadding="0" cellspacing="0">

		{% if pageOffset > 0 or courses|length == 25 %}
		<tr>
			<td colspan="2">
				{% if pageOffset > 0 %}
				<a href="{{ basePath }}/index.php/{{ action }}/0/{{ pageOffset - 25 }}{% if action == 'quickSearch' %}/{{ keywords|urlencode }}/{{ semester|urlencode }}{% endif %}">View Previous Page</a>
				{% endif %}
			</td>
			<td>&nbsp;</td>
			<td style="text-align: right;">
				{% if courses|length == 25 %}
				<a href="{{ basePath }}/index.php/{{ action }}/0/{{ pageOffset + 25 }}{% if action == 'quickSearch' %}/{{ keywords|urlencode }}/{{ semester|urlencode }}{% endif %}">View Next Page</a>
				{% endif %}
			</td>
		</tr>
		{% endif %}

		<tr><th>&nbsp;</th><th>Course Name</th><th>Course Code</th><th>{% if user.isAdmin %} Your Actions {% else %} View Sections {% endif %}</th></tr>

		{% for course in courses %}
			{% set sections = course.getSections %}

			{% if loop.length == 0 %}
				<tr><td colspan="4"><p>You currently have no courses assigned to your account.</p></td></tr>
			{% endif %}

			<tr {% if loop.index|even %}class="plain"{% endif %}>
				<td>{{ loop.index0 + start }}.</td>
				<td>{{ course.getCourseName|e }}</td><td>{{ course.getCourseCode|e }}</td>
				<td class="action">
					{% set courseID = course.getCourseID %}
					{%if sections|length > 0 %}
						<a href="{{ basePath }}/index.php/viewSections/{{ courseID }}">Sections</a> ({{ sections|length }}) 
					{% else %}
						No Sections
					{% endif %} 
					{% if ((user.isAdmin and not user.isActing) or user.canAdministerCourse(courseID)) %}
						| <a href="{{ basePath }}/index.php/editCourse/{{ courseID }}">Edit</a> 
					{% endif %}
				</td>
			</tr>
		{% endfor %}
		</table>