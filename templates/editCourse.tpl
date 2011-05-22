{% extends "index.tpl" %}

{% block listspace %}

	<script type="text/javascript" src="{{ basePath }}/js/jquery.autocomplete.js"></script>
	<script type="text/javascript">
		<!--
			$("#prefix").autocomplete(
			"{{ basePath }}/index.php/listCoursePrefixes/0",
			{
				delay:5,
				minChars:1,
				matchSubset:1,
				matchContains:0,
				cacheLength:1,
				onItemSelect:selectItem,
				onFindValue:findValue,
				formatItem:formatItem,
				autoFill:true
			}
		);
			$("#coursenumber").autocomplete(
			"{{ basePath }}/index.php/listCourseNumbers/0",
			{
				delay:5,
				minChars:1,
				matchSubset:1,
				matchContains:0,
				cacheLength:1,
				onItemSelect:selectItem,
				onFindValue:findValue,
				formatItem:formatItem,
				autoFill:true
			}
		);
		// -->
	</script>
	
{% endblock %}

{% block greeting %}

{% endblock %}