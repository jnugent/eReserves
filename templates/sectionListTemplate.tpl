	{% if user.isAdmin and not user.isActing %}
	
		<style type="text/css">
			.center {text-align: center;}
		</style>
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
		
		<script type="text/javascript">
			<!--
			var toggleState = false;
			var totalCheckboxes = 0;
			
			
			function toggleAll() {
				$(":checkbox").attr("checked", !toggleState);
				toggleState = !toggleState
				updateCount();
			}
			
			$(document).ready(function() {

				$(".sectionChk").change( function() { 
					if ($(this).attr("checked") == '' ) { 
						$("#selectAll").attr("checked", false);
					}
					updateCount();
				} );
				
				$("#modalCreateConfirm").dialog( { 
					autoOpen: false, 
					modal: true, 
					resizable: false, 
					draggable: false, 
					title: 'Confirm Reserve Creation' 
				} );
				
				$("#unenrolLinkConfirm").dialog( {
					autoOpen: false,
					modal: true,
					resizable: false,
					draggable: false,
					title: 'Confirm Unenrol Students'
				} );
				
			});

			function updateCount() {

				totalCheckboxes = $(".sectionChk:checked").length;
				if (totalCheckboxes > 0) {
					$("#createReservesButton").attr("disabled", "");
					$("#createReservesButton").attr("value", "Create Reserve (" + totalCheckboxes + ")");
				} else {
					$("#createReservesButton").attr("disabled", "disabled");
					$("#createReservesButton").attr("value", "Create Reserve");
				}
			}

			function showModal() {

				var sectionString = totalCheckboxes == 1 ? ' 1 section' : totalCheckboxes + ' sections';
				$("#modalCreateConfirm").html('You will be adding a new reserve item to ' 
											+ sectionString 
											+ '. It will appear in a section header called "{{ defaultHeadingTitle }}" in each section.');

				$("#modalCreateConfirm").dialog("option", 
					"buttons", [
						{ text: "Ok", click: function() { if (totalCheckboxes > 0) $("#adminSectionForm").submit(); } },
						{ text: "Cancel", click: function() { $(this).dialog("close"); return false; } }
					]
				);
				$("#modalCreateConfirm").dialog("open");
			}

			function showUnenrolModal(sectionID) {
				$("#unenrolLinkConfirm").html('You will remove all students (not instructors) from this Section.  This will NOT alter their Registrar information.  This cannot be undone.  Are you sure?');
				$("#unenrolLinkConfirm").dialog("option", 
					"buttons", [
						{ text: "Ok", click: function() {
							$.ajax({
								url: "{{ basePath }}/index.php/unenrolStudents/" + sectionID,
								type: 'GET',
								success: function(){ 

										linkParent = $('#unenrolLink-' + sectionID).parent();
										$("#unenrolLinkConfirm").dialog("close");
										linkParent.fadeTo(400, 0.1);
										linkParent.text('Students Unenroled');
										linkParent.fadeTo(400, 1);
								}
							});

							} },
						{ text: "Cancel", click: function() { $(this).dialog("close"); return false; } }
					]
				);
				$("#unenrolLinkConfirm").dialog("open");
			}
			 // -->
		</script>

		<div id="modalCreateConfirm"></div>
		<div id="unenrolLinkConfirm"></div>
		<form id="adminSectionForm" action="{{ basePath }}/index.php/createNewReserve/0" method="post">
	{% endif %}

	<table class="reservesTable">
		<caption>
			{% if course %}
			<p>There {%if sections|length > 1%}are{% else %}is{% endif %} {{ sections|length }} course section{% if sections|length > 1 %}s{% endif %} 
			for the course titled "<strong>{{ course.getCourseName }} ({{ course.getCourseCode }})</strong>".</p>

			<p>
				Show: {% autoescape false %}{{ includeSections }}{% endautoescape %}
			</p>
			{% endif %}
		</caption>
		<tr class="nodrag nodrop"><th>&nbsp;</th><th>Section Name</th><th {%if not user.isAdmin or user.isActing %}colspan="2"{% endif %}>Status</th>
			{% if user.isAdmin and not user.isActing %}
				<th>Admin Options</th><th class="center"><input type="checkbox" id="selectAll" onClick="toggleAll()" /></th>
				{% set colspan = 5 %}
			{% else %}
				{% set colspan = 3 %}
			{% endif %}
		</tr>
		{% for code, section in sections %}
			{% if section %}
				{% set headings = section.getHeadings() %}
				<tr id="{{ section.getSectionID }}" {% if loop.index is even %}class="plain"{% endif %}>
					<td>{{ loop.index }}</td>
					<td><a href="{{ basePath }}/index.php/viewReserves/{{ section.getSectionID }}">{{ section.getCalendarCourseCode|e }}</a></td> 
					<td>
						{% if section.getTotalNumberOfReserves > 0 %}
							<strong>{{ section.getTotalNumberOfReserves }} reserve{% if section.getTotalNumberOfReserves != 1 %}s{% endif %} assigned.</strong>
						{% else %}
							0 reserves assigned.
						{% endif %}
					</td>
					{% if user.canAdministerSection(section.getSectionID) or (user.isAdmin and not user.isActing) %}
					<td>
						{% if user.isAdmin and not user.isActing %}
							<a href="{{ basePath }}/index.php/editSection/{{ section.getSectionID }}">Edit</a> | <span><a href="#" id="unenrolLink-{{ section.getSectionID }}" onClick="showUnenrolModal({{ section.getSectionID }})">Unenrol Students</a></span> |
						{% endif %}
						{% if user.canAdministerSection(section.getSectionID) or (user.isAdmin and not user.isActing) %}
							<a href="{{ basePath }}/index.php/assignPeople/{{ section.getSectionID }}/0">Instructors</a> | <a href="{{ basePath }}/index.php/itemHeadings/{{ section.getSectionID }}/0">Headings</a>
						{% endif %}
					</td>
					{% else %}
					<td>&nbsp;</td>
					{% endif %}
					{% if user.isAdmin and not user.isActing %}
						<td class="center"><input type="checkbox" class="sectionChk" name="sectionIDs[]" value="{{ section.getSectionID }}" /></td>
					{% endif %}
				</tr>
			{% else %}
				<tr>
					<td>{{ loop.index }}</td>
					<td colspan="{{ colspan }}">The section "{{ code }}" doesn't appear to be listed in the section list.</td>
				</tr>
			{% endif %}	
		{% endfor %}
		{% if user.isAdmin and not user.isActing %}
			<tr>
				<td colspan="{{ colspan }}" style="text-align: right"><input id="createReservesButton" disabled="disabled" type="button" onClick="showModal()" value="Create Reserve" name="createReserves" /></td>
			</tr>
		{% endif %}
	</table>
	{% if user.isAdmin and not user.isActing %}
		</form>
	{% endif %}
