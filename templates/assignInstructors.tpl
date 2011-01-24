{% extends "index.tpl" %}

{% block listspace %}

	<script type="text/javascript" src="{{ basePath }}/js/jquery.autocomplete.js"></script>
	<style type="text/css">
		.ac_results {
			padding: 0px;
			border: 1px solid #7FB7FB;
			overflow: hidden;
			background-color: #AFD2FD;
		}

		.ac_results ul {
			width: 100%;
			list-style-position: outside;
			list-style: none;
			padding: 0;
			margin: 0;
		}

		.ac_results iframe {
			display:none;/*sorry for IE5*/
			display/**/:block;/*sorry for IE5*/
			position:absolute;
			top:0;
			left:0;
			z-index:-1;
			filter:mask();
			width:3000px;
			height:3000px;

		}

		.ac_results li {
			margin: 0px;
			padding: 2px 5px;
			cursor: pointer;
			display: block;
			width: 100%;
			font: menu;
			font-size: 12px;
			overflow: hidden;
		}

		.ac_loading {
			background : #AFD2FD url('{{ basePath }}/images/ajax-loader.gif') right center no-repeat;
		}

		.ac_over {
			background-color: white;
			color: black;
		}
	</style>

	<script type="text/javascript">
		<!--

			function findValue(li) {
				if( li == null ) return alert("No match!");
				// if coming from an AJAX call, let's use the CityId as the value
				if( !!li.extra ) var sValue = li.extra[0];
				// otherwise, let's just display the value in the text box
				else var sValue = li.selectValue;
			}

			function selectItem(li) {
				findValue(li);
			}

			function formatItem(row) {
				return row[0] + " (" + row[1] + ")";
			}

			function lookupAjax(){
				var oSuggest = $("#instructor")[0].autocompleter;
				oSuggest.findValue();
				return false;
			}

			function lookupLocal(){
				var oSuggest = $("#instructor")[0].autocompleter;
				oSuggest.findValue();
				return false;
			}

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
				autoFill:true
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
				<tr><td>{{ user }}</td><td>{{ role.roleDesc }}</td><td><a href="{{ basePath }}/index.php/assignInstructors/{{ sectionID }}/{{ user }}">Remove?</a></td></tr>
			{% endfor %}
		</table>
	{% endif %}

{% endblock %}
