help: ## list available targets (this page)
	@awk 'BEGIN {FS = ":.*?## "} /^[0-9a-zA-Z_-]+:.*?## / {printf "\033[36m%-45s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install-cs: ## install cs
	docker exec wordpress /bin/sh -c 'php ./wp-content/plugins/fapi-member/wpcs/vendor/bin/phpcs --config-set installed_paths ./wp-content/plugins/fapi-member/wpcs/'

cs: ## cs
	docker exec wordpress /bin/sh -c 'php ./wp-content/plugins/fapi-member/wpcs/vendor/bin/phpcs ./wp-content/plugins/fapi-member/src --standard=./wp-content/plugins/fapi-member/wpcs/phpcs.xml --encoding=utf-8 --tab-width=4 --colors -sp'

cbf: ## cbf
	docker exec wordpress /bin/sh -c 'php ./wp-content/plugins/fapi-member/wpcs/vendor/bin/phpcbf ./wp-content/plugins/fapi-member/src --standard=WordPress --encoding=utf-8 --tab-width=4 --colors -sp'


build: ## Builds the plugin source code
	rm -d -r wp-build
	mkdir wp-build
	cp fapi-member.php wp-build/fapi-member.php
	cp uninstall.php wp-build/uninstall.php
	cp -r src wp-build/src
	cp -r templates wp-build/templates
	cp -r vendor wp-build/vendor
	cp -r media wp-build/media
	cp -r _sources wp-build/_sources
	find wp-build -type f -name '*.scss' -delete
	find wp-build -type f -name '*.map' -delete
	find wp-build -type f -name '*.txt' -delete
	find wp-build -type f -name '*.html' -delete
	cp readme.txt wp-build/readme.txt
	rm -d -r wp-build/media/font/specimen_files
