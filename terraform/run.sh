#!/bin/bash

# (C) Stephen "TheCodeAssassin" Hoogendijk 2015
# Terraform run script for Hatchup

# create a strong and random password using openssl
MYSQL_PASSWORD=$(openssl rand -base64 24)
CURPWD=$(pwd)
OPERATION=$1
KEY_NAME=$2
KEY_PATH=$3
echo

GLEXEC=$(which ansible-galaxy)

if [ $? = 1 ]; then
  echo
  echo "ansible-galaxy not found, please make sure you have ansible 1.9+ installed."
  echo
  exit
fi

# make sure we are in the proper directory
if [ ! -e main.tf ] && [ -e "${CURPWD}/terraform" ] ; then
    cd terraform
fi

CONFIG_PATH="../application/app/config"

if [[ ! ${OPERATION} = "apply" ]] && [[ ! ${OPERATION} = "destroy" ]] ; then
    echo "=> Only apply and destroy are valid operation"
    echo
    exit 1
fi

if [[ $1 = "" ]] || [[ $2 = "" ]] ; then
    echo "=> Please use this script as ./run.sh <apply|destroy> <keyname> <public_key_path>"
    echo
    exit 1
fi

if [ ! -e ${CONFIG_PATH}/config.ini ] ; then
    echo "=> Creating config.ini file for the application"

    cp ${CONFIG_PATH}/config.ini.template ${CONFIG_PATH}/config.ini

    # set the mysql root user password
    `sed -i'' -e "s/MYSQL_PASSWORD/${MYSQL_PASSWORD}/g" ${CONFIG_PATH}/config.ini`
else
    # use the mysql password from the configuration file
    MYSQL_PASSWORD=$(sed -n 's/mysql_pass = "\(.*\)"/\1/p' ${CONFIG_PATH}/config.ini)
    AWS_ACCESS_KEY=$(sed -n 's/access_key = "\(.*\)"/\1/p' config.tf)
    AWS_SECRET_KEY=$(sed -n 's/secret_key = "\(.*\)"/\1/p' config.tf)
fi

# export the AWS credentials for ansible
export AWS_ACCESS_KEY_ID="${AWS_ACCESS_KEY}"
export AWS_SECRET_ACCESS_KEY="${AWS_SECRET_KEY}"

echo "=> Running terraform..."
#terraform ${OPERATION} -var "key_name=${KEY_NAME}" -var "public_key_path=${KEY_PATH}" -var "mysql_root_password=${MYSQL_PASSWORD}"

if [ -e terraform.tfstate ] && [ $? = 0 ]; then
    echo
    echo "=> Writing configuration values..."
    echo
    MYSQL_HOST=$(terraform output mysql_address)
    mysql_host = ""

    # install ansible requirements
    echo "=> Installing ansible requirements"
    echo
    $GLEXEC install -f -r ../ansible/requirements.txt

    echo "=> Running ansible..."
    echo
    ansible-playbook ansible/main.yml -i ec2.py -u ubuntu eu-central-1
else
    echo "=> Terraform failed to ${OPERATION}"
fi

cd ${CURPWD}

exit 0