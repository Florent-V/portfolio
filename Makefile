# Makefile pour projet Symfony avec Webpack Encore et Docker
# ----------------------------------------------------------

# Variables de configuration
DOCKER_COMPOSE = docker compose
SYMFONY = symfony
CONSOLE = $(SYMFONY) console
COMPOSER = composer
NPM = npm
# Couleurs pour une meilleure lisibilité
RESET = \033[0m
GREEN = \033[32m
YELLOW = \033[33m
BLUE = \033[34m
RED = \033[31m

# -------------- Configuration de l'environnement --------------

# Par défaut, nous utilisons l'environnement de développement
.DEFAULT_GOAL := help
ENV ?= dev

# -------------- Help --------------

help:
	@echo ""
	@echo "${BLUE}Makefile pour projet Symfony avec Webpack Encore et Docker${RESET}"
	@echo "${BLUE}------------------------------------------------------------${RESET}"
	@echo ""
	@echo "${YELLOW}Usage:${RESET}"
	@echo "  make [commande]"
	@echo ""
	@echo "${YELLOW}Commandes disponibles:${RESET}"
	@echo "  ${GREEN}setup${RESET}              - Installation initiale du projet"
	@echo ""
	@echo "  ${GREEN}start${RESET}              - Démarrer le serveur Symfony et les services Docker"
	@echo "  ${GREEN}stop${RESET}               - Arrêter le serveur Symfony et les services Docker"
	@echo "  ${GREEN}restart${RESET}            - Redémarrer le serveur Symfony et les services Docker"
	@echo ""
	@echo "  ${GREEN}up${RESET}                 - Démarrer uniquement les services Docker (DB, mailer)"
	@echo "  ${GREEN}down${RESET}               - Arrêter uniquement les services Docker"
	@echo "  ${GREEN}db-status${RESET}          - Vérifier le statut des services Docker"
	@echo ""
	@echo "  ${GREEN}install${RESET}            - Installer les dépendances PHP et NPM"
	@echo "  ${GREEN}update${RESET}             - Mettre à jour les dépendances PHP et NPM"
	@echo ""
	@echo "  ${GREEN}db-create${RESET}          - Créer la base de données"
	@echo "  ${GREEN}db-drop${RESET}            - Supprimer la base de données"
	@echo "  ${GREEN}db-reset${RESET}           - Réinitialiser la base de données (drop + create)"
	@echo "  ${GREEN}db-migrate${RESET}         - Exécuter les migrations"
	@echo "  ${GREEN}db-fixtures${RESET}        - Charger les fixtures"
	@echo "  ${GREEN}db-recreate${RESET}        - Réinitialiser la base et recharger les fixtures"
	@echo ""
	@echo "  ${GREEN}migration-generate${RESET} - Générer une nouvelle migration"
	@echo "  ${GREEN}migration-migrate${RESET}  - Exécuter les migrations"
	@echo ""
	@echo "  ${GREEN}assets-build${RESET}       - Compiler les assets avec Webpack Encore"
	@echo "  ${GREEN}assets-dev${RESET}         - Compiler les assets en mode développement"
	@echo "  ${GREEN}assets-watch${RESET}       - Surveiller les modifications des assets"
	@echo "  ${GREEN}assets-dev-server${RESET}  - Démarrer le serveur de développement Webpack"
	@echo ""
	@echo "  ${GREEN}cache-clear${RESET}        - Vider le cache"
	@echo "  ${GREEN}tests${RESET}              - Exécuter les tests"
	@echo "  ${GREEN}lint${RESET}               - Vérifier la qualité du code"
	@echo "  ${GREEN}grumphp-run${RESET}        - Exécuter GrumPHP sur tous les fichiers"
	@echo "  ${GREEN}grumphp-git${RESET}        - Exécuter GrumPHP sur les fichiers Git modifiés"
	@echo "  ${GREEN}mailpit-open${RESET}       - Ouvrir l'interface Mailpit dans le navigateur"
	@echo "  ${GREEN}mailpit-logs${RESET}       - Afficher les logs de Mailpit"
	@echo ""

# -------------- ⚙️ Installation et configuration ⚙️ --------------

setup: install up db-create db-migrate db-fixtures assets-build start
	@echo "${GREEN}Projet configuré avec succès !${RESET}"

install:
	@echo "${BLUE}Installation des dépendances PHP...${RESET}"
	$(COMPOSER) install
	@echo "${BLUE}Installation des dépendances NPM...${RESET}"
	$(NPM) install

update:
	@echo "${BLUE}Mise à jour des dépendances PHP...${RESET}"
	$(COMPOSER) update
	@echo "${BLUE}Mise à jour des dépendances NPM...${RESET}"
	$(NPM) update

# -------------- 🎯  Gestion du serveur 🎯  --------------

start: install up db-migrate server-start assets-dev-server
	@echo "${GREEN}Serveur démarré ! Pensez à lancer 'make assets-watch' dans un autre terminal${RESET}"

stop: down
	@echo "${BLUE}Arrêt du serveur Symfony...${RESET}"
	$(SYMFONY) server:stop
	pkill -f "webpack" || true

restart: stop start
	@echo "${GREEN}Serveur redémarré !${RESET}"

clean-start: update up db-recreate assets-build
	@echo "${BLUE}Démarrage du serveur Symfony...${RESET}"
	$(SYMFONY) server:start -d

server-start:
	@echo "${BLUE}Démarrage du serveur Symfony...${RESET}"
	$(SYMFONY) server:start -d

# -------------- 🐳 Gestion des services Docker 🐳 --------------

up:
	@echo "${BLUE}Démarrage des services Docker (DB, mailer)...${RESET}"
	$(DOCKER_COMPOSE) up -d

prod-up:
	@echo "${BLUE}Démarrage des services Docker (DB, mailer) en production...${RESET}"
	$(DOCKER_COMPOSE) -f docker-compose.yml up -d

down:
	@echo "${BLUE}Arrêt des services Docker...${RESET}"
	$(DOCKER_COMPOSE) down

db-status:
	@echo "${BLUE}Statut des services Docker :${RESET}"
	$(DOCKER_COMPOSE) ps
	@echo "${BLUE}  ${RESET}"

# -------------- ⛁ Gestion de la base de données ⛁ --------------

db-create:
	@echo "${BLUE}Création de la base de données...${RESET}"
	$(CONSOLE) doctrine:database:create --if-not-exists

db-drop:
	@echo "${BLUE}Suppression de la base de données...${RESET}"
	$(CONSOLE) doctrine:database:drop --force --if-exists

db-reset: db-drop db-create
	@echo "${GREEN}Base de données réinitialisée !${RESET}"

db-migrate:
	@echo "${BLUE}Exécution des migrations...${RESET}"
	$(CONSOLE) doctrine:migrations:migrate --no-interaction

db-fixtures:
	@echo "${BLUE}Chargement des fixtures...${RESET}"
	$(CONSOLE) doctrine:fixtures:load --no-interaction

db-recreate: db-reset db-migrate db-fixtures
	@echo "${GREEN}Base de données recréée avec succès !${RESET}"

# -------------- 🔁 Gestion des migrations 🔁 --------------

migration-generate:
	@echo "${BLUE}Génération d'une nouvelle migration...${RESET}"
	$(CONSOLE) make:migration

migration-migrate:
	@echo "${BLUE}Exécution des migrations...${RESET}"
	$(CONSOLE) doctrine:migrations:migrate --no-interaction

# -------------- 🎨 Gestion des assets avec Webpack Encore 🎨 --------------

assets-build:
	@echo "${BLUE}Compilation des assets en mode production...${RESET}"
	$(NPM) run build

assets-dev:
	@echo "${BLUE}Compilation des assets en mode développement...${RESET}"
	$(NPM) run dev

assets-watch:
	@echo "${BLUE}Surveillance des assets avec Webpack...${RESET}"
	$(NPM) run watch

assets-dev-server:
	@echo "${BLUE}Démarrage du serveur de développement Webpack...${RESET}"
	$(NPM) run dev-server

# -------------- 🔧 Utilitaires 🔧 --------------

cache-clear:
	@echo "${BLUE}Vidage du cache...${RESET}"
	$(CONSOLE) cache:clear

setup-db-test:
	@echo "${BLUE}Configuration de la base de données pour les tests...${RESET}"
	$(CONSOLE) doctrine:database:create --env=test --if-not-exists
	$(CONSOLE) doctrine:migrations:migrate --env=test --no-interaction
	$(CONSOLE) doctrine:fixtures:load --env=test --no-interaction

tests:
	@echo "${BLUE}Exécution des tests...${RESET}"
	php bin/phpunit -c phpunit.xml.dist --testdox
	#vendor/bin/phpunit

lint:
	@echo "${BLUE}Vérification de la qualité du code...${RESET}"
	vendor/bin/php-cs-fixer fix --dry-run
	$(CONSOLE) lint:container
	$(CONSOLE) lint:twig templates/
	$(CONSOLE) lint:yaml config/

grumphp-run:
	@echo "${BLUE}Exécution de GrumPHP sur tous les fichiers...${RESET}"
	vendor/bin/grumphp run

grumphp-git:
	@echo "${BLUE}Exécution de GrumPHP sur les fichiers Git modifiés...${RESET}"
	vendor/bin/grumphp run --git

mailpit-open:
	@echo "${BLUE}Ouverture de l'interface Mailpit...${RESET}"
	open http://localhost:8025

mailpit-logs:
	@echo "${BLUE}Affichage des logs Mailpit...${RESET}"
	$(DOCKER_COMPOSE) logs -f mailpit

# -------------- 🚀 Environnement de production 🚀 --------------

prod-deploy:
	@echo "${BLUE}Déploiement en production...${RESET}"
	$(COMPOSER) install --no-dev --optimize-autoloader
	$(NPM) ci --production
	$(NPM) run build
	$(CONSOLE) cache:clear --env=prod
	$(CONSOLE) d:m:m --no-interaction --no-debug

# Pour éviter les conflits avec des fichiers du même nom
.PHONY: help setup install update start stop restart up down db-status db-create db-drop db-reset db-migrate db-fixtures db-recreate migration-generate migration-migrate assets-build assets-dev assets-watch assets-dev-server cache-clear tests lint grumphp-run grumphp-git prod-deploy mailpit-open mailpit-logs

## MySQL
db-root: ## Ouvre une session MySQL en root dans le conteneur kopeck-db-local
	docker exec -it omega-db-local mysql -u root -p

db-secure: ## Supprime root et donne tous les droits à db_user (⚠️ danger en prod)
	docker exec -i kopeck-db-local mysql -u root -p$$(grep MYSQL_ROOT_PASSWORD .env | cut -d '=' -f2) -e "\
	DROP USER IF EXISTS 'root'@'%'; \
	DROP USER IF EXISTS 'root'@'localhost'; \
	CREATE USER IF NOT EXISTS 'db_user'@'%' IDENTIFIED BY 'db_password'; \
	GRANT ALL PRIVILEGES ON *.* TO 'db_user'@'%' WITH GRANT OPTION; \
	FLUSH PRIVILEGES;"
