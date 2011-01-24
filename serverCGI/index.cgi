#!/usr/local/bin/perl -wT

##################################################################
##  CGI Script for Reserves.  Accepts incoming JSON				##
##  request, based on a valid JSON object.  Parses the			##
##  request, runs worldcat command, and serializes the			##
##  response into an outgoing SOAPjr/JSON response.				##
##################################################################
##  Author: Jason Nugent										##
##  Email: jnugent@unb.ca										##
##################################################################

require 5.8.8;

use strict;
use CGI;
use JSON;

# Sirsi expects a few of these to be tailored to the particular environment of the server.
# Override as needed.  We actually clobber the path to provide a secure environment in order to run
# the script with taint checking enabled.

$ENV{'PATH'} = '/usr/local/sirsi/Unicorn/Bincustom:/usr/local/sirsi/Unicorn/Bin:/usr/local/sirsi/Unicorn/Search/Bin';
$ENV{'UPATH'} = '/usr/local/sirsi/Unicorn/Config/upath';
$ENV{'BRSConfig'} = '/usr/local/sirsi/Unicorn/Config/brspath';
$ENV{'SIRSI_LANG'} = 'ENGLISH';

my $PHYSICAL_RESERVE_ITEM_CREATE	= 1;
my $PHYSICAL_RESERVE_ITEM_EDIT		= 2;
my $PHYSICAL_RESERVE_ITEM_DELETE	= 3;
my $PHYSICAL_RESERVE_ITEM_QUERY		= 4;

my $cgi = new CGI;
my $json = new JSON;

my $jsonContent		= $cgi->param('json');
my $cmd				= $cgi->param('cmd');

# the JSON decode function returns a reference to a hash containing the posted JSON content
my $hashRef = $json->decode($jsonContent);
my $apiServerCmd = '';
my %result = {};

if ($cmd == $PHYSICAL_RESERVE_ITEM_QUERY) { # this is a request to browse, based on PHYSICAL_RESERVE_ITEM_QUERY

	my $barCode = $$hashRef{'barCode'};
	if ($barCode =~ m{^\d+$}) { # barcodes are digits only
		$apiServerCmd = 'E201012070805000052R ^S34DAFFADMIN^FEUNBF^FcNONE^FW100759935^NQ' . $barCode . '^DXALL^NFBRIEF^NlASCENDING^iSx^NdBOTH^Fv400000^Fx0^^O';
		my $output = `echo "$apiServerCmd" | /usr/local/sirsi/Unicorn/Bin/apiserver`;
		if ($output ne "") {

			if ($output =~ m{\^IA(.*?)\^}s ) {
				$result{'author'} = $1;
			}
			if ($output =~ m{\^IB(.*?)\^}s ) {
				$result{'title'} = $1;
			}
			if ($output =~ m{\^\^ZNH260(.*?)\^\^}s ) {
				$result{'pubInfo'} = $1;
			}
			if ($output =~ m{\^ns(.*?)\^}s ) {
				$result{'library'} = $1;
			}
			if ($output =~ m{\^no(.*?)\^}s ) {
				$result{'location'} = $1;
			}
			if ($output =~ m{\^Is(.*?)\^}s ) {
				$result{'callNumber'} = $1;
			}
		}
	}
}

#my $cmd = 'E201006241224440062R ^S69YBFFADMIN^FEUNBF^FcNONE^FW100759935^oEBUNK^oAR^RJALL^DC100^RKACTIVE^Fv400000^Fx0^^O';


# generate output
print $cgi->header();
print $json->encode(\%result);