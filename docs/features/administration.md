# Administration des utilisateurs

## Description

Interface réservée aux administrateurs (rôle `ROLE_ADMIN`) pour gérer les comptes utilisateurs : consultation, activation/désactivation et suppression.

## Parcours utilisateur

1. L'administrateur connecté voit un lien « Admin » dans la barre de navigation.
2. La liste des utilisateurs (`/admin/user/`) affiche pour chaque compte : email (avec badge « disabled » le cas échéant), rôle (admin/user), date de création, date de dernière connexion.
3. La page de détail d'un utilisateur affiche en plus l'état de vérification de l'email et le nombre de projets, avec les actions : « Disable » / « Enable » (selon l'état), « Promote to admin » / « Demote to user » (selon le rôle) et « Delete ». Chaque action demande une confirmation JavaScript.

## Règles métier

- Toute la zone `/admin` est réservée au rôle `ROLE_ADMIN` (access_control + attribut sur le contrôleur). `ROLE_ADMIN` hérite de `ROLE_USER`.
- Un compte désactivé ne peut plus se connecter ; sa session en cours est invalidée dès la requête suivante.
- Un administrateur ne peut ni désactiver, ni supprimer, ni rétrograder son propre compte.
- Le changement de rôle (promotion ou rétrogradation) invalide la session en cours de l'utilisateur concerné : il doit se reconnecter, avec ses nouveaux droits.
- La suppression d'un utilisateur supprime aussi tous ses projets (avec leurs médias, liens et fichiers, comme une suppression de projet classique).
- La date de dernière connexion est enregistrée à chaque connexion réussie.
- Le rôle `ROLE_ADMIN` s'attribue depuis la page de détail d'un utilisateur, ou via la commande console `app:user:promote <email>` (option `--revoke` pour le retirer) — nécessaire pour le premier administrateur. Les fixtures créent un compte `admin@example.com`.
- Les actions d'administration sont protégées par jeton CSRF.

## Points d'entrée dans le code

- `src/Controller/AdminUserController.php` — liste (datagrid Kibatic), détail, activation/désactivation, suppression.
- `src/Command/UserPromoteCommand.php` — attribution/retrait du rôle admin.
- `src/Subscriber/UpdateLastLoginSubscriber.php` — enregistrement du dernier login.
- `src/Security/VerifiedUserChecker.php` — refus de connexion des comptes désactivés.
- `templates/admin/user/` — templates de la zone d'administration.
