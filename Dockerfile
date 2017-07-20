FROM ipunktbs/nginx:1.10.2-7-1.3.0
ADD . /var/www/app
WORKDIR /var/www/app
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
	&& php -r "if (hash_file('SHA384', 'composer-setup.php') === '669656bab3166a7aff8a7506b8cb2d1c292f042046c5a994c43155c0be6190fa0355160742ab2e1c88d40d5be660b410') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
	&& php composer-setup.php \
	php -r "unlink('composer-setup.php');" \
	&& cd /var/www/app && ./composer.phar install && rm composer.phar