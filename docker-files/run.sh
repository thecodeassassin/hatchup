#!/bin/bash

USER=www-data
GROUP=www-data

# let apache run as the user running the container

sed -i "s/AllowOverride None/AllowOverride All/g" /etc/apache2/apache2.conf
a2enmod rewrite

# add the user
`useradd -ms /bin/bash $USER -g $GROUP`

usermod -u $UID $USER

`sed -i "s/APACHE_RUN_USER=.*/APACHE_RUN_USER=$USER/g" /etc/apache2/envvars`
`sed -i "s/APACHE_RUN_GROUP=.*/APACHE_RUN_GROUP=$GROUP/g" /etc/apache2/envvars`

source /etc/apache2/envvars
tail -F /var/log/apache2/* &
exec apache2 -D FOREGROUND
