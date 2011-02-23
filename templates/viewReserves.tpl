{% extends "index.tpl" %}

{% block listspace %}

	{% if section.getSectionID > 0 %}
		{% set instructors = section.getInstructors %}	
		<h1>{{ section.getPrefix }} {{ section.getNumber }} - {{ section.getCourseName }} <br />
			<span class="small">{{ section.getCalendarCourseCode }}{% if instructors != '' %}, Instructors: {{ instructors }}{% endif %}</span>
		</h1>
			{%if user.isAdmin %}(<a href="{{ basePath }}/index.php/editSection/{{ section.getSectionID }}">Edit Section</a>){% endif %}
			{%if user.canAdministerSection(section.getSectionID) %}(<a href="{{ basePath }}/index.php/assignPeople/{{ section.getSectionID }}">Assign People</a>){% endif %}

		
		{% if itemHeadings|length == 0 %}
			<p>No item headings were found for this section. {%if user.canAdministerSection(section.getSectionID) %}<a href="{{ basePath }}/index.php/itemHeadings/{{ section.getSectionID }}/0">Create a Heading</a>?{% endif %}</p>
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
