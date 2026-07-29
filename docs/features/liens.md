# Liens entre médias

## Description

Un lien est une flèche orientée d'un média source vers un média cible du même projet. En mode visite virtuelle, c'est lui qui matérialise le point cliquable permettant de passer d'un panorama à l'autre.

## Parcours utilisateur

1. Sur la page d'un média, l'utilisateur crée un lien vers un autre média du projet.
2. Il règle visuellement les angles du lien : position du point cliquable sur le panorama source, et orientation de la caméra à l'arrivée sur le panorama cible.
3. Il peut créer en un clic le lien retour (« backlink ») ; les angles du lien retour restent à régler.

## Règles métier

- Un lien porte 4 angles : `sourcePitch`/`sourceYaw` (point cliquable sur le panorama source) et `targetPitch`/`targetYaw` (orientation d'arrivée).
- Un lien est « complet » quand les 4 angles sont renseignés (`isComplete()`). Les liens incomplets sont exclus du rendu visite virtuelle (le mode `positionMode: 'manual'` de Photo Sphere Viewer exige une position par lien).
- Le backlink est un simple lien inverse (cible → source) créé sans angles.

## Points d'entrée dans le code

- `src/Entity/Link.php` — entité.
- `src/Controller/LinkController.php` — édition, backlink, suppression.
- `assets/linkEdit.js` — réglage visuel des angles.
