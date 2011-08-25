{% extends "index.tpl" %}

{% block listspace %}

	<script type="text/javascript" src="http://jqueryui.com/ui/jquery.ui.datepicker.js"></script>
	<script type="text/javascript" src="http://jqueryui.com/ui/jquery.ui.widget.js"></script>
	<script type="text/javascript" src="http://jqueryui.com/ui/jquery.ui.core.js"></script>
	<script type="text/javascript">
	
		$().ready(function() {
		
			$('#semesters').tableDnD();
	
			$('#semesters').tableDnD({
				onDragClass: "onDragClass",
				onDrop: function(table, row) {
						serializedOrder = $.tableDnD.serialize();
						$.post("{{ basePath }}/index.php/reorderSemesters", serializedOrder);
					}
			});	
		});
		
		
		$(function() {
			$("#startdate").datepicker( {dateFormat: 'yy-mm-dd'} );
			$("#enddate").datepicker( {dateFormat: 'yy-mm-dd'} );
		});
	</script>
	

	<h2>Maintaining Semesters for Course Reserves</h2>

	{{ editForm.display }}
	
	{% if semesters|length > 0 %}
	
		<table id="semesters" class="reservesTable">
			<caption>
				These are the other semesters. Click to make changes. Drag and drop to reorder them for the dropdown.
			</caption>
			<tr class="nodrag nodrop"><th>&nbsp;</th><th>Semester</th><th>Start Date</th><th>End Date</th><th>Is Active?</th><th>Is Current?</th></tr>
			{% for semester in semesters %}
				<tr id="{{ semester.getSemesterID }}">
					<td>{{ loop.index }}.</td>
					<td><a href="{{ basePath }}/index.php/adminSemesters/{{ semester.getSemesterID }}">{{ semester.getYear }}{{ semester.getTerm }}</a></td>
					<td>{{ semester.getStartDate }}</td>
					<td>{{ semester.getEndDate }}</td>
					<td>{{ semester.isActive }}</td>
					<td>{{ semester.isCurrent }}</th>
				</tr>
			{% endfor %}
		</table>
	{% else %}
		<p>There are no semesters yet.  You are about to create one.</p>
	{% endif %}
	

	
{% endblock %}
