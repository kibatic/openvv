---
name: connaissance-metier
description: À utiliser au début de toute demande de développement, évolution, correction de bug ou question fonctionnelle sur OpenVV, avant d'explorer le code, puis de nouveau avant de déclarer le travail terminé.
---

# Connaissance métier OpenVV

## Vue d'ensemble

La connaissance fonctionnelle du projet vit dans `docs/features/*.md` (une fiche par fonctionnalité). Ce skill impose le cycle **lire avant / documenter après** : lire les fiches concernées avant de coder, les mettre à jour avant de déclarer le travail terminé. Objectif : le développeur ne doit plus avoir à redonner le contexte fonctionnel à chaque demande — les fiches SONT ce contexte.

## Carte des fonctionnalités

| Fiche | Contenu |
|---|---|
| [comptes-utilisateurs](../../../docs/features/comptes-utilisateurs.md) | Inscription avec captcha, vérification d'email, connexion |
| [projets](../../../docs/features/projets.md) | CRUD projets, propriétaire, choix du renderer, cascade de suppression |
| [medias](../../../docs/features/medias.md) | Upload de panoramas, filtre luminosité, vignettes, ordre, orientation initiale |
| [liens](../../../docs/features/liens.md) | Liens orientés entre médias, 4 angles, backlinks, notion de lien complet |
| [rendu-visites](../../../docs/features/rendu-visites.md) | 3 modes d'affichage, chemins view (public) / preview (propriétaire) |
| [partage-public](../../../docs/features/partage-public.md) | shareUid, durée limitée, activation/réactivation/suppression |
| [export-import](../../../docs/features/export-import.md) | Archive ZIP autoporteuse, remappage des IDs à l'import |

## Processus imposé

### Avant de coder

1. Identifier dans la carte les fonctionnalités touchées par la demande et lire les fiches correspondantes.
2. Ne pas demander au développeur du contexte fonctionnel déjà présent dans une fiche.
3. Si la demande contredit une règle métier documentée, le signaler avant d'implémenter.

### Pendant la planification

Tout plan de développement inclut explicitement une étape « Mettre à jour docs/features et la carte du skill ». Un plan sans cette étape est incomplet.

### Avant de déclarer le travail terminé

Le développement n'est **pas terminé** tant que cette checklist n'est pas traitée — au même titre que les tests :

- [ ] Le comportement visible ou une règle métier a changé → chaque fiche concernée est mise à jour.
- [ ] La demande crée une fonctionnalité nouvelle → une fiche est créée (gabarit ci-dessous) **et** ajoutée à la carte de ce SKILL.md.
- [ ] Changement purement technique sans effet fonctionnel (refactoring, dépendances, infra) → aucune fiche à modifier, mais le résumé final le dit explicitement (« aucun impact fonctionnel, docs/features inchangé »).

Le résumé final adressé au développeur mentionne toujours ce qui a été fait côté documentation.

## Gabarit de fiche

```markdown
# Nom de la fonctionnalité

## Description
Ce que permet la fonctionnalité, pour qui.

## Parcours utilisateur
Les étapes vécues par l'utilisateur.

## Règles métier
Comportements, valeurs par défaut, invariants, restrictions d'accès.

## Points d'entrée dans le code
Fichiers principaux (chemins seulement, pas de numéros de ligne).
```

Règles de rédaction : en français, niveau fonctionnel (ce que fait l'application, pas comment) ; les détails d'implémentation volatils restent dans le code ou AGENTS.md.

## Rationalisations interdites

| Excuse | Réalité |
|---|---|
| « Je mettrai à jour la doc si elle devient inexacte » | Toute évolution fonctionnelle rend une fiche inexacte ou incomplète. La mise à jour est une étape du dev, pas une éventualité. |
| « C'est un petit changement, pas besoin de doc » | Petite modif de comportement = petite mise à jour de fiche (souvent une ligne). Le coût est minime, l'oubli est définitif. |
| « Je documenterai dans un second temps » | Le second temps n'arrive jamais. Fiches à jour = condition de fin du dev. |
| « Le développeur est pressé, je saute la doc » | Sauter la doc ne se fait que si le développeur le demande explicitement. La pression du temps n'est pas une demande explicite. |

## Drapeaux rouges

Si tu t'apprêtes à dire « travail terminé » sans avoir ni touché `docs/features/` ni écrit « aucun impact fonctionnel » : STOP, reprends la checklist.
