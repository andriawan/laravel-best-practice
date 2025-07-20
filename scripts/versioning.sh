#!/bin/bash

VERSION=$1
GIT_HEAD=$2
COMMIT_DATE=$3
SHORT_HASH=$4

echo "Creating version.json with version: $VERSION, gitHead: $GIT_HEAD"

cat <<EOF > version.json
{
  "version": "$VERSION",
  "hash": "$GIT_HEAD",
  "short_hash": "$SHORT_HASH",
  "date": "$COMMIT_DATE"
}
EOF

composer config version $VERSION --no-plugins