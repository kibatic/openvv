<?php

namespace App\Validator;

use App\Service\CaptchaService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Valide le champ « captcha » de RegistrationFormType en comparant la saisie
 * au code stocké en session par CaptchaService. Fait partie de la protection
 * anti-bot de la page d'inscription.
 */
class ValidCaptchaValidator extends ConstraintValidator
{
    public function __construct(
        private readonly CaptchaService $captchaService,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidCaptcha) {
            throw new UnexpectedTypeException($constraint, ValidCaptcha::class);
        }

        // Le champ vide est déjà signalé par la contrainte NotBlank ; on évite
        // d'afficher deux erreurs pour la même cause.
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value) || !$this->captchaService->isValid($value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
