<?php

define('DATA_FILE', 'data.json');

function save($data) {
	file_put_contents(DATA_FILE, json_encode($data));
}

function getSave() {
	if (!file_exists(DATA_FILE)) return NULL;
	json_decode(file_get_contents(DATA_FILE));
}

?>
