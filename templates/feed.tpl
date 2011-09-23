<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	>
	
	<channel>
		<title>Reserves for {{ sectionInfo.courseName }}</title>
		<atom:link href="{{ baseUrl }}{{ basePath }}/index.php/feed/{{ sectionInfo.sectionId }}" rel="self" type="application/rss+xml" />
		<description>{{ instructors }} - {{ sectionInfo.courseCode }}</description>
		<lastBuildDate>{{ date }}</lastBuildDate>
		<language>en</language>
		<sy:updatePeriod>hourly</sy:updatePeriod>
		<sy:updateFrequency>1</sy:updateFrequency>
		{% for item in items %}
			<item>
				<title>{{ item[0]|e }}</title>
				{% if item[5] == 'e' %}
					<link>{% if item[6] == 0 and item[7] == 0 %}{{ baseUrl }}{% endif %}{{ item[1] }}</link>
				{% else %}
					<link>{{ baseUrl }}{{ basePath }}/index.php/viewReserves/{{ sectionInfo.sectionId }}</link>
					<description>{{ item[1]|e }}, {{ item[4]|e }}{% if item[2] > 0 %}, {{ item[3]|e }}{% endif %}</description>
				{% endif %}
				<pubDate>{{ item[4]|e }}</pubDate>
				<slash:comments>0</slash:comments>
			</item>
		{% endfor %}
	</channel>
</rss>
