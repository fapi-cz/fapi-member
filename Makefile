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
	[ -d wp-build ] && rm -d -r wp-build
	[ -d wp-build-test ] && rm -d -r wp-build-test
	(cd app && npm install)
	make react-build
	make divi-build
	mkdir wp-build-test
	mkdir wp-build-test/fapi-member
	mkdir wp-build
	mkdir wp-build/app
	mkdir wp-build/multiple-blocks
	cp fapi-member.php wp-build/fapi-member.php
	cp uninstall.php wp-build/uninstall.php
	rsync -av --exclude='Divi' src/ wp-build/src/
	mkdir -p wp-build/src/Divi/includes
	mkdir -p wp-build/src/Divi/scripts
	cp -r src/Divi/includes/ wp-build/src/Divi/includes
	cp -r src/Divi/scripts/ wp-build/src/Divi/scripts
	cp src/Divi/FapiMemberDivi.php wp-build/src/Divi/FapiMemberDivi.php
	cp -r app/dist wp-build/app/dist
	cp -r vendor wp-build/vendor
	cp -r libs wp-build/libs
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
	cp -r wp-build/* wp-build-test/fapi-member/
	(cd wp-build-test && zip -r fapi-member.zip fapi-member)
	rm -rf wp-build-test/fapi-member

prepare-deploy: isset-version ## Prepares everything for a deploy
	docker exec node /bin/sh -c 'yarn --cwd multiple-blocks install'
	docker exec node /bin/sh -c 'yarn --cwd multiple-blocks build'
	composer install
	make -B build -i
	rm -rf wp-svn
	svn co https://plugins.svn.wordpress.org/fapi-member wp-svn
	mkdir wp-svn/tags/$(version)
	cp -r wp-build/* wp-svn/tags/$(version)/
	rm -rf wp-svn/trunk/*
	cp -r wp-build/* wp-svn/trunk/

isset-version:
ifndef version
	$(error version not found. Please provide a version like 'make prepare-deploy version=x.y.z')
endif

git-commit: ## Amends a commit. If parameter m="Commit Name" is added, it will create a new commit instead.
	git add -A
	@if [ -z "$(m)" ]; then \
		git commit --amend --no-edit; \
	else \
		git commit -m "$(m)"; \
	fi

git-push: ## Amends a commit and does a forced push. If parameter m="Commit Name" is added, it will create a new commit instead.
	@if [ -z "$(m)" ]; then \
		$(MAKE) git-commit; \
	else \
		$(MAKE) git-commit m="$(m)"; \
	fi
	git push --force

git-rebase-master: ## Fetches data and rebases branch with master
	git fetch --all --prune
	git rebase origin/master

src-build: ## Builds the src folder for deploy and creates a zip for testing on a live site.
	make -B build -i

react-build: ## Builds front end react
	npm --prefix ./app run build

divi-build: ## Builds divi
	npm --prefix ./src/divi run build

dc-up:
	make -C .docker-fapi-member dc-up

dc-down:
	make -C .docker-fapi-member dc-down

dc-restart:
	make -C .docker-fapi-member dc-down
	make -C .docker-fapi-member dc-up
