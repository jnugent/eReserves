<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<meta name="HandheldFriendly" content="True" />
	<title>UNB Libraries - Secure Login</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no;"/>
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />
	<link rel="shortcut icon" href="https://www.lib.unb.ca/favicon.ico" type="image/x-icon"> 
	<link rel="stylesheet" href="https://www.lib.unb.ca/core/css/proxy/handheld.proxy.lib.css" media="handheld">
	<link rel="stylesheet" type="text/css" media="screen" href="/core/css/validforms.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="{{ basePath }}/css/reserves.css" />
	<link rel="stylesheet" href="https://www.lib.unb.ca/core/css/proxy/responsive.proxy.lib.css" media="screen">

	<!--[if lte IE 8]>
	<link rel="stylesheet" href="https://www.lib.unb.ca/core/css/proxy/ie.proxy.lib.css" media="screen">
	<![endif]-->
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
	<script type="text/javascript" src="{{ basePath }}/js/reserves.js"></script>
	<script src="/core/js/jquery.validate.js" type="text/javascript"></script>
	
</head>

<body class="downloadOnly">
<div id="wrapper">

<div id="header"><div class="innertube">
<ul class="skiplist">
	<li class="skip"><a href="#contentcolumn">Skip to main content</a></li>
</ul>
	<div id="mainNav">
 		<h1 id="UNBLib"><a href="http://lib.unb.ca/" title="University of New Brunswick Libraries">University of New Brunswick Libraries</a></h1>    
			<ul id="utilities">
				<li><a href="http://lib.unb.ca/help/ask.php">Ask Us</a></li>
				<li><a href="http://lib.unb.ca/about/hours.php">Hours</a></li>
				<li class="last"><a href="http://lib.unb.ca/news/comment">Comments</a></li>
			</ul>
	</div><!-- end mainNav -->

</div></div><!-- end header + innertube -->

<div id="contentwrapper">
<div id="contentcolumn">
	<div class="innertube">

	{% if noAccess %}<p class="caution">You must log in to download this resource.</p>{% endif %}
	 {%if loginError %}<p class="caution"You do not appear to have access to this item.</p>{% endif %}
	 
	{{ loginForm.display }}

	<div id="loginNotes">
		<dl>
		<dt>Login Problems:</dt>
	
		<dd><span>Please direct questions or authentication problems to the appropriate Help Desk:</span>
		<dl>
		<dt>UNB Fredericton &amp; other locations</dt>
		<dd><span><a href="http://www.unb.ca/its/">ITS Help Desk</a> | </span>(506) 453-5199 | <a href="mailto:helpdesk@unb.ca?subject=Login%20Problems%20with%20UNB%20Libraries%20Services">helpdesk@unb.ca</a></dd>
	
		<dt>UNB Saint John</dt>
		<dd><span><a href="http://www.unb.ca/its/">ITS Help Desk</a> |</span>(506) 648-5555 | 
		<a href="mailto:studenthelpdesk@unbsj.ca?subject=Login%20Problems%20with%20UNB%20Libraries%20Services">studenthelpdesk@unbsj.ca</a> (students) or <a href="mailto:helpline@unbsj.ca?subject=Login%20Problems%20with%20UNB%20Libraries%20Services">helpline@unbsj.ca</a>  (faculty/staff)</dd>
		
		<dt>Copyright Restrictions</dt>
		<dd>Resources are licensed to the University of New Brunswick for academic purposes ONLY. The content may not be reproduced, retransmitted, disseminated, sold, distributed, published, broadcast  
			or circulated.  Remote access restricted to members of the University of New Brunswick/St. Thomas University community.</dd>
	
		</dl>
	</div><!-- footNotes -->

	</div><!-- end innertube -->
</div><!-- end contentcolumn -->
</div><!-- end contentwrapper -->

<div id="footer">
	<div class="innertube">
		<p id="copy"><a href="http://www.unb.ca/copyright/">&copy; University of New Brunswick</a></p>

		<ul id="footLinks">
			<li><a href="http://lib.unb.ca/help/accessibility.php" title="Accessibility initiatives and goals for this website">Accessibility</a></li>
			<li><a href="http://www.unb.ca/privacy.html" title="Your pivacy and UNB">Privacy</a></li>
			<li class="last"><a href="http://lib.unb.ca/libmail/to_web_admin.php" title="Report broken links or problems with web pages.">Contact Website Manager</a></li>
		</ul>
	</div><!-- end innertube -->
</div><!-- end footer -->

</div><!-- end wrapper -->
</body>
</html>
