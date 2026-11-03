#!/bin/sh
set -e

if [ -f /app/package.json ]; then
	echo "package.json found, installing dependencies and starting dev server"
	npm install
	npm run dev -- --host=0.0.0.0 --port=5173
else
	echo "No package.json found in /app — skipping npm install and dev server"
	tail -f /dev/null
fi	