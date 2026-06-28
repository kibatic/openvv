<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Process\Process;

/**
 * Génère et valide le captcha image affiché sur le formulaire d'inscription.
 *
 * Utilisé par :
 *  - CaptchaController (route /captcha/image qui sert l'image PNG) ;
 *  - ValidCaptchaValidator (contrainte du champ « captcha » de RegistrationFormType).
 *
 * Objectif : bloquer les inscriptions automatisées (bots) sur /register, sans
 * dépendance externe. Un code aléatoire est généré puis stocké en session ;
 * l'image déformée est produite par ImageMagick (binaire « convert », déjà
 * présent dans l'image Docker et déjà utilisé par MediaManager). À la
 * soumission, la saisie est comparée au code en session (insensible à la casse).
 */
class CaptchaService
{
    /** Clé de stockage du code attendu dans la session. */
    public const SESSION_KEY = 'registration_captcha_code';

    /** Alphabet sans caractères ambigus (pas de 0/O, ni 1/I/L) pour faciliter la lecture. */
    public const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    /** Nombre de caractères du code. */
    public const CODE_LENGTH = 5;

    /** Police TrueType fournie par l'image Docker. */
    private const FONT = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * Génère un nouveau code aléatoire, le stocke en session (en écrasant le
     * précédent) et le retourne. Ne produit pas l'image (cf. generateImagePng).
     */
    public function generateCode(): string
    {
        $max = strlen(self::ALPHABET) - 1;
        $code = '';
        for ($i = 0; $i < self::CODE_LENGTH; ++$i) {
            $code .= self::ALPHABET[random_int(0, $max)];
        }

        $this->requestStack->getSession()->set(self::SESSION_KEY, $code);

        return $code;
    }

    /**
     * Produit l'image PNG déformée correspondant au code fourni.
     *
     * Sécurité : la commande est passée à Process sous forme de tableau
     * d'arguments (aucune chaîne shell interprétée) et $code provient
     * exclusivement de self::ALPHABET : aucune injection n'est possible.
     */
    public function generateImagePng(string $code): string
    {
        // Deux traits parasites aux coordonnées aléatoires pour gêner l'OCR.
        $line1 = sprintf('line %d,%d %d,%d', random_int(0, 30), random_int(40, 60), random_int(170, 200), random_int(5, 25));
        $line2 = sprintf('line %d,%d %d,%d', random_int(0, 30), random_int(5, 25), random_int(170, 200), random_int(40, 60));

        $process = new Process([
            'convert',
            '-size', '200x70',
            'xc:white',
            '-font', self::FONT,
            '-pointsize', '40',
            '-fill', '#1a237e',
            '-gravity', 'Center',
            '-annotate', '8x4', $code,
            '-stroke', '#5c6bc0',
            '-strokewidth', '1',
            '-draw', $line1,
            '-draw', $line2,
            '-wave', '4x110',
            '-attenuate', '0.4',
            '+noise', 'Gaussian',
            '-strip',
            '-depth', '8',
            '-colors', '16',
            'png:-',
        ]);
        $process->mustRun();

        return $process->getOutput();
    }

    /**
     * Vérifie la saisie de l'utilisateur contre le code stocké en session.
     * La comparaison est insensible à la casse.
     */
    public function isValid(?string $userInput): bool
    {
        if (null === $userInput) {
            return false;
        }

        $input = trim($userInput);
        if ('' === $input) {
            return false;
        }

        $expected = $this->requestStack->getSession()->get(self::SESSION_KEY);

        return is_string($expected) && 0 === strcasecmp($input, $expected);
    }

    /**
     * Invalide le code courant. À appeler après une inscription réussie pour
     * empêcher le rejeu d'un même code.
     */
    public function invalidate(): void
    {
        $this->requestStack->getSession()->remove(self::SESSION_KEY);
    }
}
