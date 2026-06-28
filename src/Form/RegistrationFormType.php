<?php

namespace App\Form;

use App\Entity\User;
use App\Validator\ValidCaptcha;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue(
                        message: 'You should agree to our terms.',
                    ),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(
                        message: 'Please enter a password',
                    ),
                    new Length(
                        min: 6,
                        max: 4096, // longueur max imposée par Symfony pour des raisons de sécurité
                        minMessage: 'Your password should be at least {{ limit }} characters',
                    ),
                ],
            ])
            // Champ non mappé : la valeur est comparée au code stocké en session
            // par CaptchaService (cf. ValidCaptcha), elle n'est pas persistée.
            ->add('captcha', TextType::class, [
                'mapped' => false,
                'label' => 'Security code',
                'attr' => [
                    'autocomplete' => 'off',
                    'autocapitalize' => 'off',
                    'spellcheck' => 'false',
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'Please enter the security code.',
                    ),
                    new ValidCaptcha(),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
