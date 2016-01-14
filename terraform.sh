#!/bin/bash

# (C) Stephen "TheCodeAssassin" Hoogendijk 2015
# Terraform run script for Hatchup

# create a strong and random password using openssl
MYSQL_PASSWORD=$(openssl rand -hex 16)
CURPWD=$(pwd)
OPERATION=$1
KEY_NAME=$2
KEY_PATH=$3
echo

change_config_entry() {
 SEARCH=$(printf "%q" "$1" )
 REPLACE=$(printf "%q" "$2" )

 `sed -i'' -e "s/$SEARCH/$REPLACE/g" ${CONFIG_PATH}/config.ini`
}

cd terraform


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
    echo
    echo "=> Creating config.ini file for the application"

    cp ${CONFIG_PATH}/config.ini.template ${CONFIG_PATH}/config.ini

    # set the mysql root user password
    change_config_entry "MYSQL_PASSWORD" "${MYSQL_PASSWORD}"
else
    # use the mysql password from the configuration file
    MYSQL_PASSWORD=$(sed -n 's/mysql_pass = "\(.*\)"/\1/p' ${CONFIG_PATH}/config.ini | xargs)
fi

echo "=> Running terraform..."
terraform ${OPERATION} -var "key_name=${KEY_NAME}" -var "public_key_path=${KEY_PATH}" -var "mysql_root_password=${MYSQL_PASSWORD}"

if [ -e terraform.tfstate ] && [ $? = 0 ] && [[ ${OPERATION} = "apply" ]]; then
    echo
    echo "=> Writing configuration values..."
    echo

    MYSQL_HOST=$(terraform output mysql_address)
    MYSQL_PORT=$(terraform output mysql_port)
    ES_HOST=$(terraform output es_address)
    WEB_HOST=$(terraform output web_address)

    change_config_entry "MYSQL_HOST" "${MYSQL_HOST}"
    change_config_entry "MYSQL_PORT" "${MYSQL_PORT}"
    change_config_entry "ELASTICSEARCH_HOST" "${ES_HOST}"
    change_config_entry "ELASTICSEARCH_PORT" "9200"

    echo
    echo "=> Application available at http://${WEB_HOST}"
    echo

elif [ -e terraform.tfstate ] && [ ! $? = 0 ]; then
    echo "=> Terraform failed to ${OPERATION}"
fi

cd ${CURPWD}

exit 0