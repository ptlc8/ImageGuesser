ip=localhost
port=8080

all : open start

start :
	php -S ${ip}:${port}

open :
	xdg-open http://${ip}:${port}
