{% extends "index.tpl" %}

{% block listspace %}

	<script type="text/javascript">
	<!-- 
		function selectAll() {
			$('.optionMultiple').attr({selected: "selected"});
		}
	// -->
	</script>
	<h2>Maintaining Reserves listed in '{{ itemHeading.getHeadingName }}', for {{ section.getCalendarCourseCode }}</h2>
	
	{% if reserves|length > 0 %}
	
		<table class="reservesTable">
			<caption>
				These are the other Reserves records in this heading.
			</caption>
			<tr><th>&nbsp;</th><th>Reserves Title (click to edit)</th><th>&nbsp;</th></tr>
			{% for reserve in reserves %}
				<tr>
					<td>{{ loop.index }}.</td>
					<td><a href="{{ basePath }}/index.php/adminCourseReserves/{{ itemHeading.getItemHeadingID }}/{{ reserve.getReservesRecordID }}">{{ reserve.getTitle }}</a></td>
					<td><a href="{{ basePath }}/index.php/viewReserve/{{ reserve.getReservesRecordID }}">Manage items for this?</a></td>
				</tr>
			{% endfor %}
		</table>
	{% else %}
		<p>There are no reserves in this heading yet.  You are about to create one.</p>
	{% endif %}

	{{ reservesForm.display }}
	
{% endblock %}
