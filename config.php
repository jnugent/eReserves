; <?php exit(); // DO NOT DELETE ?>

[general]

host_name="www.yourserver.com"
base_path = "/reserves"
force_ssl = On
reserves_email = your@email.com

[database]

user = reserves
password = yourPassword
host = localhost
dbname = reserves

; we define a small set of IP addresses that we consider 'local' to the machine
; most of these are SSL virtualhosts that can't be NameVirtualHosted with Apache

[localhosts]

localhost[] = 127.0.0.1

[ldap]

ldap_host = "ldap://ldap.yourserver.com"
administration_dn = "uid=something,dc=yourdomain.com"
administration_pass = "yourAdminPassword"

; PHP lower cases the array indexes when returning an LDAP object.
; If this isn't the case with your LDAP library, you can camel-caps these

student_course_field = studentcourse
instructor_course_field = instructorcourse
account_type_field = edupersonprimaryaffiliation

; define the contents of the account_type_field defined above
; for both types of user accounts.  For us, it is either
; student or staff, assigned by ITS.

student_field_string = "student"
staff_field_string = "staff"

[template]

template_dir = "/full/path/reserves/templates"
template_cache = "/full/path/reserves/t_cache"

[session]

; set this parameter to a number, in seconds, to
; determine how long inactive users should remain logged in.
; 0 means until the browser closes

session_cookie_timeout = 0

[search]

search_type = mysql ; or solr
solr_url = http://localhost:8080/solr ; not implemented yet

[form]

use_submission_time_limits = Off

[assetstore]

; the permissions on the directory below needs to allow RW access for the web server user
; maintain the trailing slash, please
asset_dir = /full/path/reserves/assetstore/
max_upload_size = 100240000 ; 10Mb

; the creation and deletion of physical reserve items also affects the live OPAC, if you want
; if you want this, edit the settings below

[catalogue]

catalogue_integration = On ; or Off
controller_url = "https://your.opac.server.com/uhtbin/reserves/index.cgi"
controller_disable_ssl_verify = On ; or Off.  Leave On if you sign your own certificate and use SSL

[bulk_reserves]
default_heading_title = "Documents" ; you can set this to whatever you'd like the default ItemHeading title to be, for bulk reserve creates

[worldcat]
wskey = YOURAPIKEY ; obviously, get this from OCLC for yourself.
