#!/bin/bash

function kmkdir() {
	echo "Creating $1"
	mkdir $1 
	chmod 777 $1
}
cd ~etl

kmkdir ip2location
kmkdir logs
kmkdir events
kmkdir events/originals
kmkdir events/processed
kmkdir events/inprocess