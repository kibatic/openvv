# Partage public

## Description

Le propriétaire d'un projet peut le rendre accessible publiquement, sans compte, via une URL non devinable, pour une durée limitée.

## Parcours utilisateur

1. Depuis la fiche projet, le propriétaire active le partage en choisissant une durée en jours.
2. Il obtient une URL publique `/view/{shareUid}` à diffuser.
3. Il peut supprimer le partage à tout moment ; l'URL cesse immédiatement de fonctionner.

## Règles métier

- L'activation génère un `shareUid` (UUID v4) s'il n'existe pas déjà, et enregistre la date de début (`shareStartedAt`). Réactiver un partage existant conserve l'UID (l'URL ne change pas) mais repart de la date du jour.
- Le partage est actif tant que `shareStartedAt + shareDurationInDays` est dans le futur (`isShareActive()`). Passé ce délai, l'URL renvoie un accès refusé sans intervention du propriétaire.
- Durée par défaut : 30 jours.
- La suppression du partage remet `shareUid`, `shareStartedAt` et `shareDurationInDays` à null : un futur re-partage produira une **nouvelle** URL.
- Tout l'accès public (pages et fichiers médias) doit impérativement passer par `shareUid` + vérification `isShareActive()` — jamais par les IDs internes.

## Points d'entrée dans le code

- `src/Controller/ShareController.php` — activation / suppression du partage.
- `src/Entity/Project.php` — `isShareActive()`, `getShareEndedAt()`, `getShareRemainingDays()`.
- `src/Controller/RendererController.php` + `src/Renderer/AbstractRenderer.php` — accès public via `shareUid` (pages et URLs de fichiers).
