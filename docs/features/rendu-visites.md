# Rendu des visites

## Description

Le rendu est la façon dont un projet est affiché au visiteur. Chaque projet a un mode d'affichage (renderer) parmi trois : panorama simple, galerie, ou visite virtuelle. Le front s'appuie sur Photo Sphere Viewer 5.

## Parcours utilisateur

- **Panorama simple** (`simple_panorama`) : affiche le premier média du projet, seul.
- **Galerie** (`gallery`) : affiche les médias du projet dans une galerie navigable (ordre du projet).
- **Visite virtuelle** (`virtual_visit`) : navigation de panorama en panorama via les liens cliquables.

Deux chemins d'accès au même rendu :
- **Preview** (`/preview/{id}`) : réservé au propriétaire connecté, URLs de médias privées.
- **View** (`/view/{shareUid}`) : accès public quand le partage est actif, URLs de médias publiques.

## Règles métier

- Toute évolution du rendu doit fonctionner sur les **deux** chemins (view et preview) — règle de cohérence impérative.
- L'orientation de départ de chaque panorama vient de `initialPitch`/`initialYaw` du média ; elle doit être imposée explicitement au viewer pour contourner les métadonnées XMP/GPano des images (piège documenté en détail dans AGENTS.md).
- En visite virtuelle, seuls les liens complets (4 angles renseignés) sont affichés.

## Points d'entrée dans le code

- `src/Controller/RendererController.php` — routes view (public) et preview (propriétaire).
- `src/Renderer/` — pattern stratégie : un renderer par mode, sélectionné par `ProjectRendererEnum`.
- `templates/renderer/*.html.twig`, `assets/renderer/*.js` — templates et JS Photo Sphere Viewer par mode.
