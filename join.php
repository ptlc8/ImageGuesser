<?php

if (!isset($_REQUEST['name']))
	exit('need name');

include('init.php');

try {
	$game = getCurrentGame();
	$game->join($_REQUEST['name']);
	save($game);
	returnSuccess('joined');
} catch (Exception $e) {
	returnError($e->getMessage());
}


?>
