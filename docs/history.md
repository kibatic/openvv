# Historique des développements

## 2026-07-29

- Interface d'administration des utilisateurs (`/admin/user`, rôle `ROLE_ADMIN`) : datagrid (email, rôle, création, dernier login), page de détail avec activation/désactivation/suppression (confirmation JS, CSRF).
- Nouveaux champs `User.lastLoginAt` (mis à jour à chaque connexion réussie) et `User.enabled` ; un compte désactivé ne peut plus se connecter et sa session en cours est invalidée.
- Commande console `app:user:promote <email> [--revoke]` pour gérer le rôle admin ; compte `admin@example.com` ajouté aux fixtures.
- La suppression d'un utilisateur supprime aussi tous ses projets (cascade médias, liens, fichiers).
- Changement de rôle depuis la page de détail admin (« Promote to admin » / « Demote to user ») ; le changement de rôle invalide la session en cours de l'utilisateur concerné ; un admin ne peut pas rétrograder son propre compte.
