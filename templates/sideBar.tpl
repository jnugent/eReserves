<div id="rightcolumn"> <!-- remove this section/div for oneCol -->
	<div class="innertube">
			{% block sideBarContent %}
	
			{% if user.isLoggedIn %}
				<h2>Welcome to Library E-Reserves, {{ user.getFirstName }}</h2>
			{% else %}
	
			<h2>Welcome to Library E-Reserves</h2>
	
				<p>Welcome to UNB's electronic reserves system for the Library. You may browse anonymously, 
				or you may choose to log in to look for things that are specifically associated with your courses.</p>
	
			{% endif %}
	
			<ul>
			{% if user.isLoggedIn %}
				<li><a href="{{ basePath }}/index.php/logout">Logout?</a></li>
				<li><a href="{{ basePath }}/index.php/viewCourses">View Your Courses</a></li>
			{% endif %}
	
				 <li><a href="{{ basePath }}/index.php/browseCourses">Browse Courses</a></li>
				 <li><a href="{{ basePath }}/index.php/viewAllReserves">View All Active Reserves</a></li>
				 <li><a href="{{ basePath }}/index.php">Quick Search</a></li>
			</ul>
	
				{% if user.isAdmin and not user.isActing %}
					<p>Administration Tasks</p>
					<ul>
						<li><a href="{{ basePath }}/index.php/createNewCourse">Create New Course</a></li>
						<li><a href="{{ basePath }}/index.php/viewReserves">Maintain Your Own Reserves</a></li>
						<li><a href="{{ basePath }}/index.php/searchByUser">Find Courses for a User</a></li>
					<!--	<li><a href="{{ basePath }}/index.php/searchWC">Search Worldcat for a Citation</a></li>	-->
					</ul>
				{% endif %}
	
	
			{% if user.isLoggedIn %}
				<small><em>last login: {{ user.getLastLogin }}</em></small>
			{% endif %}
			
			{% if user.isActing %}
				<small> (<sup></sup>Acting Enabled) </small>
			{% endif %}
		{% endblock %}
	</div><!-- end innertube -->
</div><!-- end rightcolumn -->