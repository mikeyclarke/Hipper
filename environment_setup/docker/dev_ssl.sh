#!/bin/bash
parent_path=$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )

cd "$parent_path"
echo Provisioning development certificates using mkcert
mkcert -install
echo Creating naked domain certificate
mkcert -cert-file naked.tryhleo.test.crt -key-file naked.tryhleo.test.key "tryhleo.test"
echo Creating wildcard certificate
mkcert -cert-file wildcard.tryhleo.test.crt -key-file wildcard.tryhleo.test.key "*.tryhleo.test"
echo Moving files to docker folder
mv naked.tryhleo.test.crt ../../config/docker/web/naked.tryhleo.test.crt
mv naked.tryhleo.test.key ../../config/docker/web/naked.tryhleo.test.key
mv wildcard.tryhleo.test.crt ../../config/docker/web/wildcard.tryhleo.test.crt
mv wildcard.tryhleo.test.key ../../config/docker/web/wildcard.tryhleo.test.key
echo Done
