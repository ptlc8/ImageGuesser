ip=localhost
port=8080

all : open start

clean :
	fuser ${port}/tcp -k

start :
	php -S ${ip}:${port}

open :
	xdg-open http://${ip}:${port}
