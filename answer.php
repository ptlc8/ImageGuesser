<?php

if (!isset($_REQUEST['name']))
	exit('need name');
if (!isset($_REQUEST['response']))
	exit('need response');

include('init.php');

try {
	$game = getCurrentGame();
	$result = $game->answer($_REQUEST['name'], $_REQUEST['response']);
	save($game);
	returnSuccess(array('result'=>$result?'good':'wrong','next'=>$game->getQuiz($_REQUEST['name'])));
} catch (Exception $e) {
	returnError($e->getMessage());
}

?>
