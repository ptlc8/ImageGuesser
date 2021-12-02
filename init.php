<?php

define('DATA_FILE', 'data.json');
define('WORDS_FILE', 'liste_francais.txt');
define('LANG', 'fr');

class Player {
	public $name; // String : nom du joueur
	public $score = 0;	// int : score du joueur
	public $level = 0; // int : niveau du joueur
	public $startLevelTime = 0; // int : temps où le joueur a commencé le niveau en s
	public function __construct($name) {
		$this->name = $name;
	}
	public static function fromStdClass($std) { // Player : convertie stdClass en Player
		$new = new self($std->name);
		$new->score = $std->score;
		$new->level = $std->level;
		$new->startLevelTime = $std->startLevelTime; 
		return $new;
	}	
}

class Quiz {
	public $responses = []; // String[] : propositions de réponse
	public $response; // Srting : réponse
	public $images = []; // String[] : url des images
	public function __construct($n) {
		if ($n == 0) return;
		$words = explode("\r\n", file_get_contents(WORDS_FILE));
		do {
			$responses = [];
			for ($i = 0; $i < $n; $i++)
				array_push($this->responses, $words[random_int(0, count($words)-1)]);
			$this->response = $this->responses[random_int(0, count($this->responses)-1)];
			$result = json_decode(file_get_contents('https://pixabay.com/api/?key=15601609-98b6a08239d3ab0b6000e1f4f&q='.$this->response.'&image_type=photo&lang='.LANG));
		} while ($result->totalHits < $n);
		for ($i = 0; $i < $n; $i++)
			array_push($this->images, $result->hits[$i]->webformatURL);
	}
	public static function fromStdClass($std) {
		$new = new self(0);
		if (isset($std->responses))
			foreach($std->responses as $response)
				array_push($new->responses, $response);
		if (isset($std->images))
			foreach($std->images as $image)
				array_push($new->images, $image);
		$new->response = $std->response;
		return $new;
	}
	public function toPublicStdClass() {
		$std = new stdClass();
		$std->responses = $this->responses;
		$std->images = $this->images;
		return $std;
	}
}

class Game {
	public $startTime = 0; // long : temps où la partie commença en s
	public $players = []; // Player[] : joueurs
	public $quizzies = []; // Quiz[] : les quiz
	public function __construct($quizzesN, $responsesN) {
		$startTime = time();
		for ($i = 0; $i < $quizzesN; $i++)
			array_push($this->quizzies, new Quiz($responsesN));
	}
	public static function fromStdClass($std) { // Game : convertie stdClass en Game
		$new = new self(0,0);
		$new->startTime = $std->startTime ?? time();
		if (isset($std->players))
			foreach(((array)$std->players) as $name=>$stdPlayer)
				$new->players[$name] = Player::fromStdClass($stdPlayer);
		if (isset($std->quizzies))
			foreach($std->quizzies as $stdQuiz)
				array_push($new->quizzies, Quiz::fromStdClass($stdQuiz));
		return $new;
	}
	public function join($name) { // Ajoute un joueur
		if (in_array($name, array_keys($this->players)))
			throw new Exception('already in game');
		$this->players[$name] = new Player($name);
	}
	public function leave($name) { // Retire un joueur
		if (!in_array($name, array_keys($this->players)))
			throw new Exception('not in game');
		unset($this->players[$name]);
	}
	public function answer($name, $response) {
		$success = false;
		$player = $this->players[$name];
		if ($player == NULL)
			throw new Exception('not in game');
		if ($player->level >= count($this->quizzies))
			throw new Exception('game ended');
		if ($this->quizzies[$player->level]->response == $response) {
			$player->score += max(0, intval(-10*(time()-$player->startLevelTime)+100));
			$success = true;
		} else if (!in_array($response, $this->quizzies[$player->level]->responses)) {
			throw new Exception('unknow response');
		}
		$player->level++;
		$player->startLevelTime = time();
		return $success;
	}
	public function getQuiz($name) {
		$player = $this->players[$name];
		if ($player == NULL)
			throw new Exception('not in game');
		$quiz = $this->quizzies[$player->level];
		return $quiz==NULL?NULL:$quiz->toPublicStdClass();
	}
}

function save($data) {
	file_put_contents(DATA_FILE, json_encode($data ?? new stdClass()));
}

function getSave() {
	if (!file_exists(DATA_FILE)) return NULL;
	return json_decode(file_get_contents(DATA_FILE));
}

function getCurrentGame() {
	return Game::fromStdClass(getSave());
}

function saveCurrentGame($game) {
	return save($game);
}

function returnError($error) {
	exit(json_encode(['success'=>false,'error'=>$error]));
}

function returnSuccess($result) {
	exit(json_encode(['success'=>true,'result'=>$result]));
}

?>
