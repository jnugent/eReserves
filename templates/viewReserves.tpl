{% extends "index.tpl" %}

{% block listspace %}

	{% if section.getSectionID > 0 %}
	
		<h1>{{ section.getCalendarCourseCode }} - {{ section.getCourseName }} <br />
			{%if user.isAdmin %}(<a href="{{ basePath }}/index.php/editSection/{{ section.getSectionID }}">Edit Section</a>){% endif %}
			{%if user.canAdministerSection(section.getSectionID) %}(<a href="{{ basePath }}/index.php/assignInstructors/{{ section.getSectionID }}">Assign Instructors</a>){% endif %}
		</h1>
		
		{% if itemHeadings|length == 0 %}
			<p>No item headings were found for this section.</p>
		{% else %}
		
		<table id="itemHeadings" class="reservesTable">
			<caption>
				<p>There are {{ itemHeadings|length }} reserve headings. {%if user.canAdministerSection(section.getSectionID) %}<a href="{{ basePath }}/index.php/itemHeadings/{{ section.getSectionID }}/0">Organize or Manage Reserves?</a>{% endif %}</p>
			</caption>
			<tr class="nodrag nodrop"><th>&nbsp;</th><th>Heading Name</th><th>Status</th></tr>
			{% for heading in itemHeadings %}
				<tr id="{{ heading.getItemHeadingID }}" {% if loop.index|even %}class="plain"{% endif %}>
					<td>{{ loop.index }}</td>
					<td>{{ heading.getHeadingName|e }}</td>
					<td>
						{{ heading.getListedReserves|length }}
						{% if heading.getListedReserves|length != 1 %}
							reserves assigned.
						{% else %}
							reserve assigned.
						{% endif %}
					</td>
				</tr>
				{% if heading.getListedReserves|length > 0 %}
				<tr ><td>&nbsp;</td>
					<td colspan="2">
						<ol>
							{% for reserve in heading.getListedReserves %}
							<li>
								<a href="{{ basePath }}/index.php/viewReserve/{{ reserve.getReservesRecordID }}">{{ reserve.getTitle }}</a>
							</li>
							{% endfor %}
						</ol>
					</td>
				</tr>
				{% endif %}
			{% endfor %}
		</table>
	{% endif %}
	{% else %}
		<p>There are no sections specifically assigned to your account at this time.</p>
	{% endif %}

{% endblock %}