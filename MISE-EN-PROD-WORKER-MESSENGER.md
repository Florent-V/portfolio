# Mise en production du worker Messenger (géoloc des IP du formulaire de contact)

> **Statut : à faire.** Le code applicatif est déjà en production (PR #64), mais aucun worker
> n'est installé et les étapes systemd du déploiement ne s'exécutent que si la variable
> de dépôt `ENABLE_MESSENGER_WORKER` vaut `true` (`.github/workflows/02-deploy.yml`).
>
> Détails de la feature : `docs/contact-security-and-workers.md`.

## 1. Rappel de la feature

Chaque envoi du formulaire de contact crée une ligne `contact_message_log` (synchrone), puis
dispatch `App\Message\EnrichContactMessageLog` sur le transport `async` (table
`messenger_messages`). Le handler `App\MessageHandler\EnrichContactMessageLogHandler` interroge
`http://ip-api.com/json/{ip}` (sans clé, timeout 3 s) et remplit pays, région, ville, FAI, org,
ASN et `geoLookupStatus` (`skipped_private_ip`, `error`…). Le résultat est visible dans EasyAdmin :
**Sécurité → Messages de contact**.

Un message `async` n'est traité que par un process `messenger:consume` : c'est le worker.
Sans lui, les colonnes géoloc restent à `null`.

> ℹ️ **Les emails ne dépendent pas du worker.** `config/packages/mailer.yaml` définit
> `message_bus: false` : le mailer envoie directement (SMTP/Brevo) pendant la requête, sans passer
> par Messenger. Le routage `SendEmailMessage: async` de `messenger.yaml` n'est donc jamais
> utilisé. Seul `EnrichContactMessageLog` part dans la file (`doctrine://default?auto_setup=0` en
> prod) : sans worker, les messages d'enrichissement s'y accumulent et la géoloc reste vide,
> mais rien d'autre n'est bloqué.

## 2. État actuel du dépôt

| Élément | État |
| --- | --- |
| Code (entité, handler, migration `Version20260911203637`, routing Messenger) | ✅ en prod |
| `deploy/systemd/portfolio-messenger-worker.service` | ❌ **non versionné** (fichier local uniquement) |
| `docs/contact-security-and-workers.md` | ❌ non versionné |
| Étapes « Transfer systemd unit files » et « Restart known Messenger workers » (`02-deploy.yml`) | ⏸️ désactivées (variable `ENABLE_MESSENGER_WORKER` absente) |

C'est l'absence de `deploy/systemd/` dans le dépôt qui faisait échouer le rsync de la CI
(`change_dir ".../deploy/systemd" failed`).

## 3. Préparer le dépôt

### 3.1 Corriger l'unité systemd

Le fichier `deploy/systemd/portfolio-messenger-worker.service` doit être ajusté :

- `After=mysql.service` : MySQL tourne dans **Docker** (`portfolio-db-local`), pas en service
  système. Remplacer par `After=network-online.target docker.service` et
  `Wants=network-online.target docker.service`.
- `WorkingDirectory=__DEPLOY_PATH__` est un placeholder : il est remplacé à l'installation (§4.2).
- Vérifier `User=` : il doit pouvoir lire le projet et écrire dans `var/` (souvent `www-data`,
  le même utilisateur que PHP-FPM).
- Vérifier le chemin de PHP (`command -v php` sur le serveur) pour `ExecStart`.

Version cible :

```ini
[Unit]
Description=Symfony Messenger Worker - Portfolio
Wants=network-online.target docker.service
After=network-online.target docker.service

[Service]
Type=simple
User=www-data
WorkingDirectory=__DEPLOY_PATH__
Environment=APP_ENV=prod
ExecStart=/usr/bin/php bin/console messenger:consume async --time-limit=3600 --memory-limit=128M --no-debug
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

`--time-limit=3600` + `Restart=always` : le worker se relance chaque heure, ce qui libère la
mémoire et recharge le code après un déploiement même sans restart explicite.

### 3.2 Versionner et réactiver la CI

1. `git add deploy/systemd/ docs/contact-security-and-workers.md`
2. Dans GitHub, **Settings → Secrets and variables → Actions → Variables**, créer la variable
   de dépôt `ENABLE_MESSENGER_WORKER` = `true` (aucune modification du workflow nécessaire).
3. Merger sur `prod`. Le déploiement copie l'unité dans `DEPLOY_PATH/deploy/systemd/`. Tant
   qu'elle n'est pas installée (§4), l'étape de restart l'ignore avec le message
   `Skipping …: not installed yet`.

## 4. Installation sur le serveur (une seule fois)

### 4.1 Pré-requis

```bash
cd DEPLOY_PATH
# La table de la file existe (migration Version20250624144244) ?
php bin/console doctrine:migrations:status --env=prod
# Le worker sait joindre la base Docker (127.0.0.1:DB_PORT, via .env.local) et le transport ?
sudo -u www-data php bin/console messenger:stats --env=prod
# Sortie HTTP vers ip-api.com autorisée ?
curl -s "http://ip-api.com/json/8.8.8.8?fields=status,country"
```

### 4.2 Installer et démarrer le service

```bash
DEPLOY_PATH=/chemin/reel/du/projet
sudo sed "s#__DEPLOY_PATH__#${DEPLOY_PATH}#" \
  "${DEPLOY_PATH}/deploy/systemd/portfolio-messenger-worker.service" \
  | sudo tee /etc/systemd/system/portfolio-messenger-worker.service >/dev/null
sudo systemctl daemon-reload
sudo systemctl enable --now portfolio-messenger-worker
sudo systemctl status portfolio-messenger-worker
```

> L'unité installée est une **copie** : si `deploy/systemd/*.service` change plus tard, relancer
> ce bloc (la CI ne fait que `restart`, jamais d'installation, pour ne pas donner de droit root
> au compte de déploiement).

### 4.3 Autoriser la CI à redémarrer le worker

Le compte SSH de déploiement doit pouvoir lancer `systemctl restart` sans mot de passe,
**uniquement** sur les services `portfolio-*` :

```bash
sudo visudo -f /etc/sudoers.d/portfolio-deploy
```

```
UTILISATEUR_DEPLOY ALL=(root) NOPASSWD: /usr/bin/systemctl restart portfolio-*
```

(Vérifier le chemin avec `command -v systemctl`.) Tout nouveau worker devra être nommé
`portfolio-*.service`.

## 5. Vérifications après mise en production

```bash
# Worker actif et logs
sudo systemctl status portfolio-messenger-worker
sudo journalctl -u portfolio-messenger-worker -f

# La file se vide (doit rester proche de 0)
php bin/console messenger:stats --env=prod

# Messages en échec après les 3 tentatives (retry_strategy)
php bin/console messenger:failed:show --env=prod
```

Test fonctionnel :

1. Envoyer un message via le formulaire de contact depuis une IP publique.
2. Dans l'admin (**Sécurité → Messages de contact**), la ligne doit avoir pays/ville/FAI remplis
   et un `geoLookupStatus` différent de `null` en quelques secondes.
3. Au prochain déploiement, le log GitHub Actions doit afficher
   `Restarting portfolio-messenger-worker.service`.

### Rattraper l'historique

Les messages `EnrichContactMessageLog` accumulés avant l'installation seront traités dès le
démarrage du worker : les anciens logs de contact seront donc géolocalisés a posteriori. ip-api.com
limite l'offre gratuite à 45 requêtes/minute ; au-delà, les messages passent en erreur puis sont
retentés (3 tentatives) avant d'aller dans le transport `failed`
(`messenger:failed:retry` pour les rejouer).

```sql
SELECT COUNT(*) FROM messenger_messages WHERE queue_name = 'default' AND delivered_at IS NULL;
```

## 6. Retour arrière

```bash
sudo systemctl disable --now portfolio-messenger-worker
sudo rm /etc/systemd/system/portfolio-messenger-worker.service
sudo systemctl daemon-reload
```

Supprimer la variable `ENABLE_MESSENGER_WORKER` (ou la passer à `false`). Les messages
d'enrichissement restent en file sans être perdus ; les emails ne sont pas affectés.

## Checklist

- [ ] Mesurer la file en attente avant installation (§5, « Rattraper l'historique »)
- [ ] Corriger l'unité systemd (`After=docker.service`, `User`, chemin PHP) (§3.1)
- [ ] Versionner `deploy/systemd/` et `docs/contact-security-and-workers.md` (§3.2)
- [ ] Créer la variable `ENABLE_MESSENGER_WORKER=true` et déployer (§3.2)
- [ ] Vérifier les pré-requis serveur (§4.1)
- [ ] Installer et démarrer le service (§4.2)
- [ ] Ajouter la règle sudoers pour le restart (§4.3)
- [ ] Tester le formulaire et la géoloc dans l'admin (§5)
