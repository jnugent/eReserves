{% if pageOffset > 0 or items|length == 25 %}
{% set quickSearchUrl = "/" ~ keywords|url_encode ~ "/" ~ semester|url_encode %}
{% set filterFragment = courseNameFilter|url_encode ~ "|" ~ courseCodeFilter|url_encode %}

	<tr>
		<td colspan="4" style="text-align: right;">
			{% if pageOffset > 0 %}
				<a title="First Page" href="{{ basePath }}/index.php/{{ action }}/0/0/{{ semester|url_encode }}"><img src="{{ basePath }}/images/paging/first.png" /></a> &nbsp;
			{% endif %}
			{% if pageOffset > 25 %}
				{% set backButton = true %}
				{%if pageOffset - 25 >= 0 %}{% set backLimit = pageOffset - 25 %}{% else %}{% set backLimit = 0 %}{% endif %}
				<a title="Previous Page" href="{{ basePath }}/index.php/{{ action }}/0/{{ backLimit }}/{{ semester|url_encode }}"><img src="{{ basePath }}/images/paging/prev.png" /></a>
			{% endif %}

			{% if items|length == 25 and totalRecords - pageOffset > 25 %}
				{% if backButton %}&nbsp;{% endif %}
				<a title="Next Page" href="{{ basePath }}/index.php/{{ action }}/0/{{ pageOffset + 25 }}/{{ semester|url_encode }}"><img src="{{ basePath }}/images/paging/next.png" /></a>
			{% endif %}
			{% if pageOffset < totalRecords - 25 %}
				&nbsp; <a title="Last Page" href="{{ basePath }}/index.php/{{ action }}/0/{{ totalRecords - 25 }}/{{ semester|url_encode }}"><img src="{{ basePath }}/images/paging/last.png" /></a>
			{% endif %}
		</td>
	</tr>
{% endif %}
