#!/bin/sh

rsvg-convert -o "$2" "$1"
pngcrush -q -reduce -brute "$2" "$2.crushed"
mv "$2.crushed" "$2"
