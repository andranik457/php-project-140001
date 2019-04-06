#!/usr/bin/env bash

dumpMongo() {
    date=$1

    mongodump --gzip --host localhost:27017 --db festa --out /daily_dump/festa_${date}
    mongodump --gzip --host localhost:27017 --db files --out /daily_dump/files_${date}
}

dumpDate=$(date -d '-1 day 00:00:00' "+%Y-%m-%d")

dumpMongo ${dumpDate}