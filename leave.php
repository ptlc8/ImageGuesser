<?php

if (!isset($_REQUEST['name']))
	exit('need name');

include('init.php');

try {
	$game = getCurrentGame();
	$game->leave($_REQUEST['name']);
	save($game);
	returnSuccess('leaved');
} catch (Exception $e) {
	returnError($e->getMessage());
}


?>
