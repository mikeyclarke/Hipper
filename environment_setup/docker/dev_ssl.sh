#!/bin/bash
parent_path=$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )

cd "$parent_path"
echo Provisioning development certificates using mkcert
mkcert -install
echo Creating naked domain certificate
mkcert -cert-file naked.usehipper.test.crt -key-file naked.usehipper.test.key "usehipper.test"
echo Creating wildcard certificate
mkcert -cert-file wildcard.usehipper.test.crt -key-file wildcard.usehipper.test.key "*.usehipper.test"
echo Moving files to docker folder
mv naked.usehipper.test.crt ../../config/docker/web/naked.usehipper.test.crt
mv naked.usehipper.test.key ../../config/docker/web/naked.usehipper.test.key
mv wildcard.usehipper.test.crt ../../config/docker/web/wildcard.usehipper.test.crt
mv wildcard.usehipper.test.key ../../config/docker/web/wildcard.usehipper.test.key
echo Done
