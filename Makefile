composer-install ci:
	docker run --rm -v `pwd`:/package sidewave/php:8.2-apache bash -c 'cd /package && composer install -o'

composer-update cu:
	docker run --rm -v `pwd`:/package sidewave/php:8.2-apache bash -c 'cd /package && composer update -o'


phpstan:
	docker run --rm -v `pwd`:/package sidewave/php:8.2-apache bash -c 'cd /package && composer run phpstan'


phpcs:
	docker run --rm -v `pwd`:/package sidewave/php:8.2-apache bash -c 'cd /package && composer run phpcs'

phpcs-summary:
	docker run --rm -v `pwd`:/package sidewave/php:8.2-apache bash -c 'cd /package && composer run phpcs:summary'

phpcs-fix:
	docker run --rm -v `pwd`:/package sidewave/php:8.2-apache bash -c 'cd /package && composer run phpcs:fix'
