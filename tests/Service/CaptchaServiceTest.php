<?php

namespace Tests\Service;

use App\Service\CaptchaService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Process\ExecutableFinder;

class CaptchaServiceTest extends KernelTestCase
{
    /**
     * Construit le service avec une requête disposant d'une session en mémoire,
     * indispensable car CaptchaService stocke le code attendu en session.
     */
    private function createService(): CaptchaService
    {
        self::bootKernel();
        $container = static::getContainer();

        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));

        /** @var RequestStack $requestStack */
        $requestStack = $container->get(RequestStack::class);
        $requestStack->push($request);

        return $container->get(CaptchaService::class);
    }

    public function testGenerateCodeRespectsLengthAndAlphabet(): void
    {
        $service = $this->createService();
        $code = $service->generateCode();

        $this->assertSame(CaptchaService::CODE_LENGTH, strlen($code));
        $this->assertSame(1, preg_match('/^[' . CaptchaService::ALPHABET . ']+$/', $code));
    }

    public function testIsValidIsCaseInsensitive(): void
    {
        $service = $this->createService();
        $code = $service->generateCode();

        $this->assertTrue($service->isValid($code));
        $this->assertTrue($service->isValid(strtolower($code)));
        $this->assertTrue($service->isValid(' ' . $code . ' '));
    }

    public function testIsValidRejectsWrongOrEmptyInput(): void
    {
        $service = $this->createService();
        $service->generateCode();

        $this->assertFalse($service->isValid('________'));
        $this->assertFalse($service->isValid(''));
        $this->assertFalse($service->isValid(null));
    }

    public function testIsValidWithoutGeneratedCode(): void
    {
        $service = $this->createService();

        $this->assertFalse($service->isValid('ABCDE'));
    }

    public function testInvalidateClearsStoredCode(): void
    {
        $service = $this->createService();
        $code = $service->generateCode();
        $service->invalidate();

        $this->assertFalse($service->isValid($code));
    }

    public function testGenerateImagePngReturnsPngBinary(): void
    {
        if (null === (new ExecutableFinder())->find('convert')) {
            $this->markTestSkipped('ImageMagick (convert) indisponible dans cet environnement.');
        }

        $service = $this->createService();
        $code = $service->generateCode();
        $png = $service->generateImagePng($code);

        // Signature d'un fichier PNG : octets 0x89 'P' 'N' 'G'.
        $this->assertSame("\x89PNG", substr($png, 0, 4));
    }
}
