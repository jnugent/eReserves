{% extends "index.tpl" %}

{% block listspace %}

	{% set id = section.getSectionID %}
	{% if itemHeadings|length == 0 %}
			<p>No headings were found for this section.  Create one?</p>
	{% endif %}

		<script type="text/javascript">
			$().ready(function() {
				$('#itemHeadings-{{ id }}').tableDnD();

				$('#itemHeadings-{{ id }}').tableDnD({
					onDragClass: "onDragClass",
					onDrop: function(table, row) {
							serializedOrder = $.tableDnD.serialize();
							$.post("{{ basePath }}/index.php/reorderItemHeadings/{{ id }}", serializedOrder);
						}
					});
				});
		</script>

	{% if itemHeadings|length > 0 %}
		<table id="itemHeadings-{{ id }}"  class="reservesTable" cellpadding="5" border="0">
			<caption><p>There are {{ itemHeadings|length }} headings for this section. You can drag and drop them to reorder the sequence, if you like.</p></caption>
			<tr class="nodrag nodrop"><th>Position</th><th>Heading Name</th><th colspan="2">Status</th></tr>
			{% for heading in itemHeadings %}
				<tr id="{{ heading.getItemHeadingID }}" {% if loop.index|even %}class="plain"{% endif %}>
					<td>{{ loop.index }}</td><td>{{ heading.getHeadingName|e }}</td><td>{{ heading.getListedReserves|length }} reserves assigned</td>
					<td><a href="{{ basePath }}/index.php/adminCourseReserves/{{ heading.getItemHeadingID }}/0">Maintain Reserves?</a> </td>
				</tr>
			{% endfor %}
		</table>
	{% endif %}

{% endblock %}