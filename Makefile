help: ## list available targets (this page)
	@awk 'BEGIN {FS = ":.*?## "} /^[0-9a-zA-Z_-]+:.*?## / {printf "\033[36m%-45s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install-cs: ## install cs
	docker exec wordpress /bin/sh -c 'php ./wp-content/plugins/fapi-member/wpcs/vendor/bin/phpcs --config-set installed_paths ./wp-content/plugins/fapi-member/wpcs/'

cs: ## cs
	docker exec wordpress /bin/sh -c 'php ./wp-content/plugins/fapi-member/wpcs/vendor/bin/phpcs ./wp-content/plugins/fapi-member/src --standard=./wp-content/plugins/fapi-member/wpcs/WordPress/ruleset.xml --encoding=utf-8 --tab-width=4 --colors -sp'

cbf: ## cbf
	docker exec wordpress /bin/sh -c 'php ./wp-content/plugins/fapi-member/wpcs/vendor/bin/phpcbf ./wp-content/plugins/fapi-member/src --standard=./wp-content/plugins/fapi-member/wpcs/WordPress/ruleset.xml --encoding=utf-8 --tab-width=4 --colors -sp'

composer-wpcs-install: ## Run composer update
	docker run --rm --interactive --tty --volume "$$PWD/wpcs:/app" --user "$$(id -u):$$(id -g)" --volume ~/.ssh:/root/.ssh  composer:2 install --ignore-platform-reqs

composer-wpcs-update: ## Run composer update
	docker run --rm --interactive --tty --volume "$$PWD/wpcs:/app" --user "$$(id -u):$$(id -g)" --volume ~/.ssh:/root/.ssh  composer:2 update --ignore-platform-reqs

composer-install: ## Run composer update
	docker run --rm --interactive --tty --volume "$$PWD:/app" --user "$$(id -u):$$(id -g)" --volume ~/.ssh:/root/.ssh  composer:2 install --ignore-platform-reqs

composer-dump: ## Run composer dump auto load
	docker run --rm --interactive --tty --volume "$$PWD:/app" --user "$$(id -u):$$(id -g)" --volume ~/.ssh:/root/.ssh  composer:2 dump-autoload --ignore-platform-reqs

composer-update: ## Run composer update
	docker run --rm --interactive --tty --volume "$$PWD:/app" --user "$$(id -u):$$(id -g)" --volume ~/.ssh:/root/.ssh  composer:2 update --ignore-platform-reqs

composer-require: ## Run composer outdated only linked dependencies
	docker run --rm --interactive --tty --volume "$$PWD:/app" --user "$$(id -u):$$(id -g)" --volume ~/.ssh:/root/.ssh  composer:2 require $(filter-out $@,$(MAKECMDGOALS)) --ignore-platform-reqs

composer-outdated: ## Run composer outdated only linked dependencies
	docker run --rm --interactive --tty --volume "$$PWD:/app" --user "$$(id -u):$$(id -g)" --volume ~/.ssh:/root/.ssh  composer:2 outdated -oD

js-build: ## Build editor
	docker exec node /bin/sh -c 'yarn webpack'

js-upgrade: ## Upgrade editor dependencies
	docker exec node /bin/sh -c 'yarn upgrade'

js-add: ## Add editor dependencies
	docker exec node /bin/sh -c 'yarn add $(filter-out $@,$(MAKECMDGOALS))'

js-outdated: ## List editor outdated dependencies
	docker exec node /bin/sh -c 'yarn outdated'

build: ## Builds the plugin source code
	rm -d -r wp-build
	mkdir wp-build
	mkdir wp-build/multiple-blocks
	cp fapi-member.php wp-build/fapi-member.php
	cp uninstall.php wp-build/uninstall.php
	cp -r src wp-build/src
	cp -r templates wp-build/templates
	cp -r vendor wp-build/vendor
	cp -r media wp-build/media
	cp -r languages wp-build/languages
	cp -r _sources wp-build/_sources
	cp -r multiple-blocks/build wp-build/multiple-blocks/build
	cp -r multiple-blocks/includes wp-build/multiple-blocks/includes
	cp multiple-blocks/multiple-blocks.php wp-build/multiple-blocks/multiple-blocks.php
	cp multiple-blocks/package.json wp-build/multiple-blocks/package.json
	cp multiple-blocks/webpack.config.js wp-build/multiple-blocks/webpack.config.js
	find wp-build -type f -name '*.scss' -delete
	find wp-build -type f -name '*.map' -delete
	find wp-build -type f -name '*.txt' -delete
	find wp-build -type f -name '*.html' -delete
	cp readme.txt wp-build/readme.txt
	rm -d -r wp-build/media/font/specimen_files
