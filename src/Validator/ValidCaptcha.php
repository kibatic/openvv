<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Contrainte de validation du champ « captcha » de RegistrationFormType.
 * La comparaison effective est déléguée à ValidCaptchaValidator (qui s'appuie
 * sur CaptchaService). Fait partie de la protection anti-bot de /register.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ValidCaptcha extends Constraint
{
    public string $message = 'The security code is incorrect.';
}
