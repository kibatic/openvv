# Projets

## Description

Le projet est l'unité de travail : il regroupe des médias (panoramas 360°) et des liens entre eux, et porte le mode d'affichage (renderer) ainsi que l'état de partage public. Un projet appartient à un seul utilisateur (son `owner`).

## Parcours utilisateur

1. L'utilisateur connecté voit la liste de ses projets (`/project/`) avec leur état de partage (« shared until … » / « Not shared »).
2. Il crée un projet en donnant un nom et en choisissant un renderer.
3. Depuis la fiche projet, il gère les médias, les liens, la prévisualisation, le partage, l'export.

## Règles métier

- Toutes les pages projet sont réservées au propriétaire (`project->getOwner() === user`) ; seul l'accès par partage public y échappe (voir [partage-public](partage-public.md)).
- Valeurs par défaut à la création : renderer `simple_panorama`, durée de partage 30 jours.
- La suppression d'un projet supprime en cascade ses médias, leurs liens et leurs fichiers.

## Points d'entrée dans le code

- `src/Entity/Project.php` — entité (partage, renderer, cascade médias).
- `src/Controller/ProjectController.php` — CRUD, liste (datagrid Kibatic), import/export.
- `src/Enum/ProjectRendererEnum.php` — modes d'affichage possibles.
