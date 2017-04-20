#!/usr/bin/env bash
if ! hash docker 2> /dev/null; then
	echo "Docker not found. Please insatll docker."
fi
if [[ ! -z $1 ]]; then
	docker pull arush/rtmedia-build-tester
	docker run -it --rm -v $1:/mnt arush/rtmedia-build-tester
else
	echo "Usage: $0 /path/to/plugin"
fi 
