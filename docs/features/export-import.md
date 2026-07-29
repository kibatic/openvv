# Export / Import de projets

## Description

Un projet complet peut être exporté en une archive ZIP autoporteuse, puis réimporté (sauvegarde, restauration, copie d'un projet ou migration entre comptes/instances).

## Parcours utilisateur

1. Depuis un projet, le propriétaire télécharge l'export (archive ZIP).
2. Depuis la liste des projets, un utilisateur importe une archive : un nouveau projet est créé, dont il devient propriétaire.

## Règles métier

- L'archive contient `data.json` (projet, médias, liens) plus les fichiers images (`media/`) et vignettes (`thumbnail/`).
- À l'import, les IDs sont remappés : le projet importé est une copie indépendante, rattachée à l'utilisateur qui importe (pas à l'exportateur d'origine).
- L'état de partage n'est pas exporté (le projet importé n'est pas partagé).

## Points d'entrée dans le code

- `src/ExportImport/Exporter.php` / `Importer.php` — sérialisation, archive ZIP, remappage des IDs.
- `src/Controller/ProjectController.php` — routes d'export et d'import.
- `tests/ExportImport/` — partie la plus testée du projet, référence de comportement.
