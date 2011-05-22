		<style type="text/css">
			td.action { text-align: center; }
		</style>
		
		<table class="reservesTable" cellpadding="0" cellspacing="0">

		{% include "pageLinks.tpl" %}

		<tr><th>Course Name</th><th>Course Code</th><th>{% if user.isAdmin %} Your Actions {% else %} View Sections {% endif %}</th></tr>

		{% if items|length == 0 %}
			<tr><td colspan="3"><p>You currently have no courses assigned to your account.</p></td></tr>
		{% else %}

			{% if user.isAdmin and not user.isActing %}
				<tr>
					<td><input type="text" name="courseNameFilter" id="courseNameFilter" value="{{ courseNameFilter|escape }}" /></td>
					<td><input type="text" name="courseCodeFilter" id="courseCodeFilter" value="{{ courseCodeFilter|escape }}" /></td>
					<td><input type="button" value="filter" onClick="applyFilters()" class="strongButton" /></td>
				</tr>
			{% endif %}
			
			{% for course in items %}
				{% set sections = course.getSections %}
	
				<tr {% if loop.index is even %}class="plain"{% endif %}>
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
		{% endif %}
		
		{% include "pageLinks.tpl" %}
		</table>
		
		{% if user.isAdmin and not user.isActing %}
			<script type="text/javascript">

				function applyFilters() {
				
					courseNameFilterText = $('#courseNameFilter').val();
					courseCodeFilterText = $('#courseCodeFilter').val();
					var pageUrl = document.location.toString(); // need this - Firefox casts document.location to an object, not a string
					if (courseNameFilterText == '' && courseCodeFilterText == '') { // filters cleared? Back to the beginning.
						document.location.href = "{{ basePath }}/index.php/viewCourses/0/0";
					} else {
						
						var pattern = /viewCourses$/;

						if (!pattern.test(pageUrl)) { // strip down the URL and the re-append the field values so re-submission of filters works correctly
							var pattern = /^.+\/viewCourses/ig;
							var pageUrl = pageUrl.match(pattern);
						}

						if (pageUrl != null) {
							document.location.href = pageUrl + '/0/0/' + encodeURIComponent(courseNameFilterText) + '|' + encodeURIComponent(courseCodeFilterText);
						}
					}
				}

			</script>
		{% endif %}