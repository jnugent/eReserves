<?php

require_once("lib/functions.php");

import('templates.ReservesPage');
import('auth.ReservesUser');
import('general.ReservesRequest');

ReservesRequest::forceSSL();

$reservesUser = new ReservesUser();

$extraArgs = array();
list ($op, $objectID, $extraArgs) = ReservesRequest::getURLOp();
$reservesPage = new ReservesPage('Electronic Reserves', $op);

if ($reservesUser->canPerformOp($op, $objectID)) {
	$opPerformed = performOp($op, $objectID, $reservesUser, $extraArgs);
	$reservesPage->showPage($op, $objectID, $reservesUser, $opPerformed, $extraArgs);
} else {
	$reservesPage->showSecurityException($reservesUser);
}
?>