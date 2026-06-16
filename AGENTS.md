# AGENTS.md

Ce fichier guide les instances de Claude Code (claude.ai/code) travaillant dans ce dépôt.

## Présentation

OpenVV (open virtual visit) : application Symfony 6.1 / PHP 8.1+ permettant de créer des visites virtuelles à partir de panoramas 360°. Le rendu front s'appuie sur [Photo Sphere Viewer](https://photo-sphere-viewer.js.org/) v5 (actuellement 5.14.x). Un utilisateur crée des projets contenant des médias (panoramas), relie ces médias entre eux par des liens orientés, puis partage publiquement le projet.

## Environnement de développement

Tout tourne dans Docker (services `web` PHP-FPM, `database` PostgreSQL 14, `mailer` mailcatcher). Les commandes s'exécutent dans le conteneur `web`.

Premier démarrage :
```bash
cp docker-compose.override.sample.yml docker-compose.override.yml
docker compose up -d
docker compose exec web composer install
docker compose exec web php bin/console doctrine:database:create
docker compose exec web php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec web yarn install
docker compose exec web yarn encore dev   # ou: yarn encore prod en production
```

`make permissions-dev` configure les ACL pour développer sans souci de droits sur les fichiers générés par le conteneur.

## Commandes courantes

```bash
# Shell root dans le conteneur web
make bash                      # = docker compose exec web bash

# Tests (PHPUnit, suite "Project Test Suite" = répertoire tests/)
docker compose exec web php bin/phpunit
docker compose exec web php bin/phpunit tests/ExportImport/ExporterTest.php          # un fichier
docker compose exec web php bin/phpunit --filter testExport                          # un test

# Fixtures (recrée la base de test et charge les fixtures)
make fixtures

# Assets front (Webpack Encore)
docker compose exec web yarn encore dev          # build dev
docker compose exec web yarn watch               # build + watch
docker compose exec web yarn encore prod         # build production

# Migrations Doctrine
docker compose exec web php bin/console doctrine:migrations:diff
docker compose exec web php bin/console doctrine:migrations:migrate --no-interaction
```

Les tests utilisent `APP_ENV=test` (forcé par `phpunit.xml.dist`) avec un stockage de fichiers et une base dédiés. `make install_prod` regroupe les étapes de déploiement (composer + yarn + migrations + restart php-fpm).

## Architecture

### Modèle de données (`src/Entity/`)

- **User** — compte ; inscription avec vérification d'email (`symfonycasts/verify-email-bundle`).
- **Project** — appartient à un `User` (`owner`). Porte un `renderer` (`ProjectRendererEnum`) qui détermine le mode d'affichage. Le partage public repose sur `shareUid` + `shareStartedAt` + `shareDurationInDays` (voir `isShareActive()` / `getShareEndedAt()`).
- **Media** — un panorama d'un projet. Upload géré par `vich/uploader-bundle`. Ordonné dans le projet via `gedmo` sortable (`orderInProject`, groupe = projet). Champs `initialPitch`/`initialYaw` = position de caméra de départ ; filtre de luminosité optionnel.
- **Link** — lien orienté entre `sourceMedia` et `targetMedia`, avec angles `sourcePitch/Yaw` (point cliquable sur le panorama source) et `targetPitch/Yaw` (orientation à l'arrivée). `isComplete()` vérifie que les 4 angles sont renseignés.

### Renderers — pattern stratégie (`src/Renderer/`)

`ProjectRendererEnum` (`simple_panorama`, `gallery`, `virtual_visit`) sélectionne le renderer dans `RendererController`. Chaque renderer (`SimplePanoramaRenderer`, `GalleryRenderer`, `VirtualVisitRenderer`) étend `AbstractRenderer` et prépare les données passées au template Twig correspondant (`templates/renderer/*.html.twig`) puis au JS Photo Sphere Viewer (`assets/renderer/*.js`).

`RendererController` expose deux entrées :
- `app_renderer_view` (`/view/{shareUid}`) — accès **public** (URLs de médias passant par `shareUid`).
- `app_renderer_preview` (`/preview/{id}`) — accès **propriétaire authentifié uniquement** (URLs privées). Toute modification du rendu doit rester cohérente entre ces deux chemins.

`AbstractRenderer::getMediaUrl()` / `getThumbnailUrl()` aiguillent vers les routes publiques ou privées selon le flag `isPublic` — point clé du contrôle d'accès aux fichiers.

### Stockage des médias (`src/Service/MediaManager.php`)

Trois stockages Flysystem locaux (config `config/packages/flysystem.yaml`), pipeline en 3 niveaux :
1. **originalMedia.storage** — fichier uploadé brut (destination Vich, voir `config/packages/vich_uploader.yaml`).
2. **media.storage** — image servie, après application éventuelle du filtre de luminosité (Imagick via `symfony/process`).
3. **thumbnail.storage** — vignette générée.

`GenerateThumbnailSubscriber` écoute l'événement Vich `POST_UPLOAD` et déclenche `applyFiltersToMedia()` + `generateThumbnail()`. Le sous-dossier de stockage par média vient de `Media::vichDirectoryName()`.

### Export / Import (`src/ExportImport/`)

`Exporter` / `Importer` sérialisent un projet complet (médias + liens, avec remappage des IDs) en JSON pour la sauvegarde/restauration. C'est la logique la plus testée du projet (`tests/ExportImport/`).

### Front-end

Webpack Encore. Entrées (`webpack.config.js`) : `app`, `media`, `mediaEdit`, `linkEdit`, et un bundle par renderer (`renderer/gallery`, `renderer/virtual-visit`, `renderer/simple-panorama`). Bootstrap 5 + jQuery + Stimulus. Les plugins Photo Sphere Viewer (`virtual-tour`, `gallery`, `markers`, `video`, `autorotate`) sont des dépendances `package.json` (npm/yarn).

#### Piège central : métadonnées XMP/GPano vs orientation enregistrée

Photo Sphere Viewer applique les métadonnées XMP/GPano de l'image (`InitialViewHeadingDegrees`/`InitialViewPitchDegrees`, souvent 0,0) **et écrase** les `defaultYaw`/`defaultPitch` passés au constructeur — sauf si on lui fournit explicitement une position. La règle dans tout le code front :

- **Ne pas** passer `panorama`/`defaultYaw`/`defaultPitch` au constructeur `Viewer`.
- Charger l'image avec `viewer.setPanorama(url, { position: { yaw, pitch } })` : fournir `position` désactive l'application des métadonnées.
- Pour le plugin galerie, mettre la position dans `item.options.position` (le plugin la transmet à `setPanorama`).
- Pour le plugin virtual-tour, imposer l'orientation via un listener `node-changed` + `viewer.rotate()` attaché **avant** `setNodes()`.

`assets/mediaEdit.js` est l'implémentation de référence de ce pattern.

#### Breaking changes Photo Sphere Viewer 5

- Position d'un lien virtual-tour : dans la clé `position: { yaw, pitch }`, plus à la racine du lien (`VirtualVisitRenderer::getProjectLinks()`).
- Transitions : `transitionOptions: { effect, rotation }`, l'option `transition` n'existe plus.
- En mode `positionMode: 'manual'`, chaque lien exige une position : filtrer les liens dont `sourceYaw`/`sourcePitch` est `null`.

### Sécurité (`config/packages/security.yaml`)

Firewall `main` avec `form_login` (CSRF activé) et `VerifiedUserChecker` (refuse les comptes dont l'email n'est pas vérifié). L'autorisation par projet se fait dans les contrôleurs : vérifier que `project->getOwner() === user` pour tout accès non public, et passer impérativement par `shareUid` + `isShareActive()` pour l'accès public.

## Conventions

- Code et entités commentés en français (utilité de la classe en tête, champs non évidents).
- Toute nouvelle fonctionnalité de rendu doit être répercutée dans les **deux** actions `view` (public) et `preview` (propriétaire) de `RendererController`.
