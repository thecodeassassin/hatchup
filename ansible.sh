#!/bin/bash

# (C) Stephen "TheCodeAssassin" Hoogendijk 2015
# Ansible run script for Hatchup

GLEXEC=$(which ansible-galaxy)
CURPWD=$(pwd)

change_config_entry() {
 SEARCH=$(printf "%q" "$1" )
 REPLACE=$(printf "%q" "$2" )

 `sed -i'' -e "s/$SEARCH/$REPLACE/g" ${CONFIG_PATH}/config.ini`
}

if [ $? = 1 ]; then
  echo
  echo "ansible-galaxy not found, please make sure you have ansible 1.9+ installed."
  echo
  exit
fi

CONFIG_PATH="application/app/config"


if [ ! -e terraform/terraform.tfstate ] ; then

    # install Ansible requirements
    echo "=> Installing Ansible requirements"
    echo
    ${GLEXEC} install -f -r ansible/requirements.txt

    #   make sure boto is installed
    pip install boto
fi


AWS_ACCESS_KEY=$(sed -n 's/access_key = "\(.*\)"/\1/p' terraform/config.tf | xargs)
AWS_SECRET_KEY=$(sed -n 's/secret_key = "\(.*\)"/\1/p' terraform/config.tf | xargs)

# export the AWS credentials for ansible
export AWS_ACCESS_KEY_ID="${AWS_ACCESS_KEY}"
export AWS_SECRET_ACCESS_KEY="${AWS_SECRET_KEY}"

echo "=> Running ansible..."
ansible-playbook ansible/playbook.yml -i ec2.py -u ubuntu

if [ ! $? = 0 ]; then
    echo "=> Ansible failed to run"
fi

cd ${CURPWD}

exit 0