#!/bin/bash

# internally used constants
TMP_DIR=/tmp
INI_FILE_NAME_XDEBUG=20-xdebug.ini
PHP_VERSION=8.1
CONTAINER_SERVICE=web
CONTAINER_ID=$(docker compose ps -q ${CONTAINER_SERVICE})
# can be coverage or debug
XDEBUG_MODE=undefined


print_help ()
{
	echo "The general script's help msg"
	printf 'Usage: %s [-h|--help] --mode [coverage|debug]\n' "$0"
	printf   "\t--mode [coverage|debug] : choose xdebug mode.\n"
	printf   "\t--off: desactivate xdebug. Ex: activate-xdebug.sh --off\n"
	printf   "\t-h,--help: Prints help\n"
	printf   "Exemples\n"
	printf "%s --mode debug\n" "$0"
	printf "%s --mode coverage\n" "$0"
	printf "%s --off\n" "$0"
}

activate_xdebug() {
	echo "activate xdebug and restart php-fpm"

	# create the xdebug config file
	cat > ${TMP_DIR}/${INI_FILE_NAME_XDEBUG} << EOF
zend_extension=xdebug.so

xdebug.client_host = 'host.docker.internal'
xdebug.idekey=PHPSTORM
xdebug.start_with_request=yes
xdebug.mode=${XDEBUG_MODE}
xdebug.max_nesting_level=512
EOF
	docker cp ${TMP_DIR}/${INI_FILE_NAME_XDEBUG} ${CONTAINER_ID}:/etc/php/${PHP_VERSION}/fpm/conf.d/${INI_FILE_NAME_XDEBUG}
	docker cp ${TMP_DIR}/${INI_FILE_NAME_XDEBUG} ${CONTAINER_ID}:/etc/php/${PHP_VERSION}/cli/conf.d/${INI_FILE_NAME_XDEBUG}
	rm ${TMP_DIR}/${INI_FILE_NAME_XDEBUG}

	docker exec -i ${CONTAINER_ID} supervisorctl restart php-fpm

	cat > /dev/stdout << EOF
===============================
Le container "${CONTAINER_ID}" a maintenant xdebug activé.

Pour faire marcher le débuggueur, il faut maintenant faire les opérations suivantes :

Dans phpstorm
-------------
* dans File > settings > PHP : Créer un cli interpreter en docker-compose exec
* dans File > settings > PHP > Servers : Créer un serveur "localhost" avec use path
mapping et faire poiter la racine du projet sur /var/www
* cliquer ensuite sur l'icône de debug (un insecte qui écoute un téléphone) en haut
de la fenêtre PHP Storm

Dans le navigateur
------------------

* Installer le plugin (Firefox) Xdebug Starter
* dans les préférences du plugin, mettre PHPSTORM dans le IDE key for remote debugging
* aller sur le site à débugguer
* cliquer sur l'icone en forme de bug en haut à droite du navigateur

Pour un script CLI
------------------

Dans votre docker, configurez les 2 variables suivantes :

---
# activer le debug
export XDEBUG_CONFIG=PHPSTORM
# relier la conf du debug à la conf PHP > Server des settings
export PHP_IDE_CONFIG="serverName=localhost"
# lancer votre script
./bin/console xxxx
---

et lancez votre script

Théoriquement, si vous mettez un point d'arrêt dans votre code, il devrait fonctionner.

Référence : https://www.christophe-meneses.fr/article/deboguer-en-php-avec-l-extension-xdebug-et-l-ide-phpstorm
EOF

}

desactivate_xdebug() {
	echo "Desactivate xdebug and restart php-fpm"
	docker exec -i ${CONTAINER_ID} rm -f /etc/php/${PHP_VERSION}/fpm/conf.d/${INI_FILE_NAME_XDEBUG}
	docker exec -i ${CONTAINER_ID} rm -f /etc/php/${PHP_VERSION}/cli/conf.d/${INI_FILE_NAME_XDEBUG}
	docker exec -i ${CONTAINER_ID} supervisorctl restart php-fpm
}

check_container() {
	docker inspect ${CONTAINER_ID} 2>&1 > /dev/null
	if [ $? -ne 0 ] ; then
		echo "ERROR : No container with this name"
		print_help
		exit 1
	fi
}
check_mode() {
  if [ "${XDEBUG_MODE}" != "coverage" -a "${XDEBUG_MODE}" != "debug" ]; then
    		echo "ERROR : mode shoud be coverage or xdebug"
    		print_help
    		exit 1
  fi
}

if [ $# -eq 0 ] ; then
	print_help
	exit 1
fi



while test $# -gt 0
do
	_key="$1"
	case "$_key" in
		-h*|--help)
			print_help
			exit 0
			;;
		--off)
			DESACTIVATE=yes
			check_container
			desactivate_xdebug
			exit
			;;
	  --mode)
	    XDEBUG_MODE=$2
      shift
      ;;
	esac
	shift
done

check_mode
check_container
activate_xdebug


