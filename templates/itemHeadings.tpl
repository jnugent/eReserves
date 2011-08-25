{% extends "index.tpl" %}

{% block listspace %}

	{% set id = section.getSectionID %}

	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>

	<script type="text/javascript">
		$().ready(function() {

			$("#deleteHeadingConfirm").dialog( {
				autoOpen: false,
				modal: true,
				resizable: false,
				draggable: false,
				title: 'Confirm Delete Heading'
			} );

			$('#itemHeadings-{{ id }}').tableDnD();

			$('#itemHeadings-{{ id }}').tableDnD({
				onDragClass: "onDragClass",
				onDrop: function(table, row) {
						serializedOrder = $.tableDnD.serialize();
						$.post("{{ basePath }}/index.php/reorderItemHeadings/{{ id }}", serializedOrder);
					}
			});

			$('.headingTitle').dblclick(function() {

				var id = $(this).parent().attr('id');
				$(this).html('<input type="text" class="headingTitleSubmit-"' + id + ' id="headingTitleText-' + id + '" value="' + $(this).text() + '"/>');

				$('#headingTitleText-' + id).change(function() {

					$.ajax({
						url: "{{ basePath }}/index.php/editItemHeading/{{ id }}/" + $(this).attr('id'),
						data: 'headingtitle=' +  encodeURIComponent($(this).attr('value')),
						type: 'POST',
						success: function(){ 

								newTitle = $('#headingTitleText-' + id).attr('value');
								parentNode = $('#headingTitleText-' + id).parent();
								parentNode.fadeTo(400, 0.1);
								parentNode.text(newTitle);
								parentNode.fadeTo(400, 1);
						}
					});
				});
			});
		});

		var totalHeadings = {{ itemHeadings|length }};
		function showDeleteModal(sectionID, itemHeadingID) {
			$("#deleteHeadingConfirm").html('This will delete this Item Heading.  This cannot be undone.  Are you sure?');
			$("#deleteHeadingConfirm").dialog("option", 
				"buttons", [
					{ text: "Ok", click: function() {
						$.ajax({
							url: "{{ basePath }}/index.php/deleteItemHeading/" + sectionID + "/" + itemHeadingID,
							type: 'GET',
							success: function(){ 

									headingLinkRow = $('#' + itemHeadingID);
									$("#deleteHeadingConfirm").dialog("close");
									headingLinkRow.fadeOut();
									totalHeadings = totalHeadings - 1;
									
									if (totalHeadings == 0) {
										$("#itemHeadings-{{ id }}").hide();
									}
								}
							});
						}
					},
					{ text: "Cancel", click: function() { $(this).dialog("close"); return false; } }
				]
			);
			$("#deleteHeadingConfirm").dialog("open");
		}
	</script>

	<div id="deleteHeadingConfirm"></div>
	{% if itemHeadings|length > 0 %}
		<table id="itemHeadings-{{ id }}"  class="reservesTable" cellpadding="5" border="0">
			<caption><p>Here are the headings for this section.  You can drag and drop them to reorder the sequence, if you like.</p></caption>
			<tr class="nodrag nodrop"><th>Position</th><th>Heading Name (double click to edit)</th><th colspan="2">Status</th></tr>
			{% for heading in itemHeadings %}
				<tr id="{{ heading.getItemHeadingID }}"{% if loop.index is even %} class="plain"{% endif %}>
					<td>{{ loop.index }}</td><td class="headingTitle">{{ heading.getHeadingName|e }}</td>
					<td>{{ heading.getListedReserves|length }} reserves assigned</td>
					<td>
						<a href="{{ basePath }}/index.php/adminCourseReserves/{{ heading.getItemHeadingID }}/0">Maintain Reserves</a>
						{%if heading.getListedReserves|length == 0%} | <a href="#" onClick="showDeleteModal({{ section.getSectionID }}, {{ heading.getItemHeadingID }})">Delete</a>{% endif %}
					</td>
				</tr>
			{% endfor %}
		</table>
	{% endif %}

{% endblock %}
