# Médias (panoramas)

## Description

Un média est un panorama 360° uploadé dans un projet. C'est la brique de base des visites : chaque média peut être relié aux autres par des liens et possède une orientation de caméra initiale.

## Parcours utilisateur

1. Depuis un projet, l'utilisateur uploade une image panoramique.
2. Il édite ensuite le média : nom, orientation de départ de la caméra (réglée visuellement dans le viewer), filtre de luminosité.
3. Il peut réordonner les médias au sein du projet (l'ordre pilote la galerie et le panorama affiché en mode simple).

## Règles métier

- Pipeline de stockage en 3 niveaux : fichier original uploadé → image servie (avec filtre de luminosité éventuel, appliqué via Imagick) → vignette générée. Le filtre et la vignette sont générés automatiquement après l'upload.
- Le filtre de luminosité est activé par défaut à la création d'un média.
- `initialPitch` / `initialYaw` définissent l'orientation de la caméra à l'ouverture du panorama.
- Les médias sont ordonnés dans le projet (`orderInProject`, sortable par projet).
- L'accès aux fichiers est contrôlé : URLs privées pour le propriétaire, URLs publiques passant par le `shareUid` (voir [partage-public](partage-public.md)).

## Points d'entrée dans le code

- `src/Entity/Media.php` — entité (Vich uploader, sortable).
- `src/Controller/MediaController.php` — CRUD, page média (création de liens).
- `src/Service/MediaManager.php`, `src/Subscriber/GenerateThumbnailSubscriber.php` — pipeline fichiers (original / filtré / vignette).
- `assets/mediaEdit.js` — édition visuelle de l'orientation initiale (implémentation de référence Photo Sphere Viewer).
