<div id="rightcolumn"> <!-- remove this section/div for oneCol -->
	<div class="innertube">
			{% block sideBarContent %}
	
			{% if user.isLoggedIn %}
				<h2>Welcome, {{ user.getFirstName }}</h2>
			{% else %}
				<div id="loginError" style="display: none;">
					<p class="caution">We're sorry, but your login information was incorrect.
					
					</p>
				</div>
				
				{{ loginForm.display }}
	
			{% endif %}
	
			<ul>
			{% if user.isLoggedIn %}
				<li><a href="{{ basePath }}/index.php/logout">Logout?</a></li>
				<li><a href="{{ basePath }}/index.php/viewCourses">View{% if user.isAdmin %} All Sections{% else %}Your Courses{% endif %}</a></li>
			{% endif %}
	
				 <li><a href="{{ basePath }}/index.php/viewAllReserves">View All Active Courses</a></li>
				 <li><a href="{{ basePath }}/index.php">Quick Search</a></li>
			</ul>
	
				{% if user.isAdmin and not user.isActing %}
					<p>Administration Tasks</p>
					<ul>
						<li><a href="{{ basePath }}/index.php/browseCourses">Browse Courses by Prefix</a></li>
					<!--	<li><a href="{{ basePath }}/index.php/createNewCourse">Create New Course</a></li>	-->
						<li><a href="{{ basePath }}/index.php/viewReserves">Maintain Your Own Reserves</a></li>
						<li><a href="{{ basePath }}/index.php/searchByUser">Find Courses for a User</a></li>
					<!--	<li><a href="{{ basePath }}/index.php/searchWC">Search Worldcat for a Citation</a></li>	-->
						<li><a href="{{ basePath }}/index.php/switchToStudent">Switch to Student View</a></li>
						<li><a href="{{ basePath }}/index.php/adminSemesters">Maintain Semester List</a></li>
					</ul>
				{% endif %}
	
			{% if user.isLoggedIn %}
				<small><em>last login: {{ user.getLastLogin }}</em></small>
			{% endif %}
			
			<div id="enrollmentRequired" style="display: none;">
				<p class="caution"><img src="{{ basePath }}/images/lock.png" alt="login required" height="25" />
				Some reserve items are restricted to current class members.  Only students currently enrolled in 
				this class can see those items.
				</p>
			</div>
			{% if user.isActing %}
				<small> (<sup></sup>Acting Enabled) </small>
			{% endif %}
		{% endblock %}
	</div><!-- end innertube -->
</div><!-- end rightcolumn -->
