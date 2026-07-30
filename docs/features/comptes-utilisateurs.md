# Comptes utilisateurs

## Description

Un visiteur peut créer un compte pour ensuite créer et partager des visites virtuelles. Le compte est identifié par un email et protégé par mot de passe.

## Parcours utilisateur

1. Le visiteur remplit le formulaire d'inscription (`/register`) : email, mot de passe, et recopie d'un captcha image.
2. Un email de vérification lui est envoyé avec un lien signé.
3. Tant que l'email n'est pas vérifié, la connexion est refusée.
4. Une fois le lien cliqué, le compte est vérifié et l'utilisateur peut se connecter (`/login`).

## Règles métier

- Le captcha est une image générée côté serveur ; le code est stocké en session et invalidé dès qu'une inscription réussit (anti-rejeu). Chaque affichage de l'image régénère un nouveau code.
- Un compte non vérifié ne peut pas se connecter (`VerifiedUserChecker`).
- Un compte désactivé par un administrateur ne peut pas se connecter, et sa session en cours est invalidée (voir [administration](administration.md)).
- La date de dernière connexion réussie est enregistrée sur le compte (`lastLoginAt`).
- La connexion utilise un formulaire classique avec protection CSRF.

## Points d'entrée dans le code

- `src/Controller/RegistrationController.php` — inscription + vérification d'email (symfonycasts/verify-email-bundle).
- `src/Controller/LoginController.php`, `config/packages/security.yaml` — connexion, firewall `main`.
- `src/Controller/CaptchaController.php`, `src/Service/CaptchaService.php` — captcha d'inscription.
