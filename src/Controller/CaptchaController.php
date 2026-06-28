<?php

namespace App\Controller;

use App\Service\CaptchaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CaptchaController extends AbstractController
{
    public function __construct(
        private readonly CaptchaService $captchaService,
    ) {
    }

    /**
     * Sert l'image PNG du captcha affichée sur le formulaire d'inscription.
     * Chaque appel régénère un nouveau code (stocké en session), d'où les
     * en-têtes anti-cache.
     */
    #[Route('/captcha/image', name: 'app_captcha_image', methods: ['GET'])]
    public function image(): Response
    {
        $code = $this->captchaService->generateCode();
        $png = $this->captchaService->generateImagePng($code);

        return new Response($png, Response::HTTP_OK, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
