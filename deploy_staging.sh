#!/usr/bin/env sh

ansible-playbook -i ansible/hosts -i ansible/environments/staging ansible/deploy.yml --ask-vault-pass
