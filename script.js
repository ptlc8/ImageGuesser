var Random = (function(){
	function get() {
		let x = Math.sin(_seed++) * 10000;
		return x - Math.floor(x);
	}
	function setSeed(seed) {
		_seed = seed;
	}
	return {get, setSeed};
})();

function sendRequest(method, url, body, headers={"Content-Type":"application/x-www-form-urlencoded"}) {
    var promise = new (Promise||ES6Promise)(function(resolve, reject) {
        var xhr = new XMLHttpRequest();
        xhr.open(method, url);
        for (let h of Object.keys(headers))
            xhr.setRequestHeader(h, headers[h]);
        xhr.onreadystatechange = function() {
            if (this.readyState == XMLHttpRequest.DONE && this.status == 200) {
                resolve(this.response);
            }
        };
        xhr.onerror = reject;
        xhr.send(body);
    });
    return promise;
}

async function getQuiz(lang="fr", n=4) {
    let solutions = [];
    let solution;
    let images;
    do {
	    for (let i = 0; i < n; i++)
	        solutions.push(words[parseInt(Random.get()*words.length)]);
	    solution = parseInt(Random.get()*n);
		response = await sendRequest("GET", "https://pixabay.com/api/?key=15601609-98b6a08239d3ab0b6000e1f4f&q="+solutions[solution]+"&image_type=photo&lang="+lang);
		images = JSON.parse(response);
	} while (images.totalHits < 4);
	console.log(images.totalHits+" results");
	images = images.hits.slice(0,4).map(hit=>hit.webformatURL);
	return {solutions, solution, images};
}

function nextQuiz() {
    getQuiz().then(displayQuiz);
}

function displayQuiz(quiz) {
	for (let i = 0; i < 4; i++) {
		let reponseSpan = document.getElementById("r"+i);
		reponseSpan.innerText = quiz.solutions[i];
		reponseSpan.style.backgroundColor = "";
	}
	for (let i = 0; i < 4; i++) {
		document.getElementById("i"+i).style.backgroundImage = "url('"+quiz.images[i]+"')";
	}
	solution = quiz.solution;
}

function choose(button, n) {
    if (solution===undefined)
        return;
    if (n != solution)
        button.style.backgroundColor = "red";
    document.getElementById("r"+solution).style.backgroundColor = "lime";
    solution = undefined;
    setTimeout(function() {
        nextQuiz();
    }, 1000);
}

var words = [];
var solution = undefined;
window.addEventListener("load", function() {
	Random.setSeed(Date.now()-Date.now()%60000);
	sendRequest("GET", "liste_francais.txt").then(function(response) {
		words = response.split("\n");
		getQuiz().then(displayQuiz);
	});
});
