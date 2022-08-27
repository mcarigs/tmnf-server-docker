#!/bin/bash

GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'

> playlist.txt
TRACKS=(tracks/*)

for track in "${TRACKS[@]}"; do
  printf "${BLUE}Adding track: $track${NC}\n"
  echo "Challenges/Custom/$(echo $track | sed 's/tracks\///g')" >> playlist.txt
done

printf "${GREEN}Added ${#TRACKS[@]} tracks to the playlist${NC}\n"
