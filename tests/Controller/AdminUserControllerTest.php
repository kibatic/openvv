<?php

namespace Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels de l'interface d'administration des utilisateurs :
 * contrôle d'accès, activation/désactivation, suppression, refus de
 * connexion d'un compte désactivé.
 */
class AdminUserControllerTest extends WebTestCase
{
    // hash bcrypt de "testpass" (le même que dans les fixtures)
    private const PASSWORD_HASH = '$2y$13$hYZbAxA7.ySISMjHFKey..ANu44yDe1Ce1rQ1D86k8tPFdKywYAKC';

    private const TEST_EMAILS = [
        'admin-test-admin@example.com',
        'admin-test-user@example.com',
        'admin-test-target@example.com',
        'admin-test-disabled@example.com',
    ];

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->removeTestUsers();
    }

    protected function tearDown(): void
    {
        $this->removeTestUsers();
        parent::tearDown();
    }

    public function testAnonymousIsRedirectedToLogin(): void
    {
        $this->client->request('GET', '/admin/user/');

        $this->assertResponseRedirects('/login');
    }

    public function testRegularUserCannotAccessAdmin(): void
    {
        $user = $this->createUser('admin-test-user@example.com');

        $this->client->loginUser($user);
        $this->client->request('GET', '/admin/user/');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminCanListUsers(): void
    {
        $admin = $this->createUser('admin-test-admin@example.com', roles: ['ROLE_ADMIN']);
        $this->createUser('admin-test-target@example.com');

        $this->client->loginUser($admin);
        $this->client->request('GET', '/admin/user/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'admin-test-target@example.com');
    }

    public function testAdminCanDisableAndEnableUser(): void
    {
        $admin = $this->createUser('admin-test-admin@example.com', roles: ['ROLE_ADMIN']);
        $target = $this->createUser('admin-test-target@example.com');
        $targetId = $target->getId();

        $this->client->loginUser($admin);
        $this->client->request('GET', '/admin/user/'.$targetId);
        $this->client->submitForm('Disable');

        $this->assertResponseRedirects('/admin/user/'.$targetId);
        $this->assertFalse($this->reloadUser($targetId)->isEnabled());

        $this->client->followRedirect();
        $this->client->submitForm('Enable');

        $this->assertTrue($this->reloadUser($targetId)->isEnabled());
    }

    public function testAdminCannotDisableOwnAccount(): void
    {
        $admin = $this->createUser('admin-test-admin@example.com', roles: ['ROLE_ADMIN']);
        $adminId = $admin->getId();

        $this->client->loginUser($admin);
        $this->client->request('GET', '/admin/user/'.$adminId);
        $this->client->submitForm('Disable');

        $this->assertTrue($this->reloadUser($adminId)->isEnabled());
    }

    public function testAdminCanPromoteAndDemoteUser(): void
    {
        $admin = $this->createUser('admin-test-admin@example.com', roles: ['ROLE_ADMIN']);
        $target = $this->createUser('admin-test-target@example.com');
        $targetId = $target->getId();

        $this->client->loginUser($admin);
        $this->client->request('GET', '/admin/user/'.$targetId);
        $this->client->submitForm('Promote to admin');

        $this->assertResponseRedirects('/admin/user/'.$targetId);
        $this->assertContains('ROLE_ADMIN', $this->reloadUser($targetId)->getRoles());

        $this->client->followRedirect();
        $this->client->submitForm('Demote to user');

        $this->assertNotContains('ROLE_ADMIN', $this->reloadUser($targetId)->getRoles());
    }

    public function testAdminCannotDemoteOwnAccount(): void
    {
        $admin = $this->createUser('admin-test-admin@example.com', roles: ['ROLE_ADMIN']);
        $adminId = $admin->getId();

        $this->client->loginUser($admin);
        $this->client->request('GET', '/admin/user/'.$adminId);
        $this->client->submitForm('Demote to user');

        $this->assertContains('ROLE_ADMIN', $this->reloadUser($adminId)->getRoles());
    }

    public function testRoleChangeInvalidatesSession(): void
    {
        $user = $this->createUser('admin-test-user@example.com');
        $userId = $user->getId();

        $this->client->loginUser($user);
        $this->client->request('GET', '/project/');
        $this->assertResponseIsSuccessful();

        // changement de rôle en base : la session en cours doit être invalidée
        $entityManager = $this->getEntityManager();
        $entityManager->find(User::class, $userId)->setRoles(['ROLE_ADMIN']);
        $entityManager->flush();

        $this->client->request('GET', '/project/');
        $this->assertResponseRedirects('/login');
    }

    public function testAdminCanDeleteUser(): void
    {
        $admin = $this->createUser('admin-test-admin@example.com', roles: ['ROLE_ADMIN']);
        $target = $this->createUser('admin-test-target@example.com');
        $targetId = $target->getId();

        $this->client->loginUser($admin);
        $this->client->request('GET', '/admin/user/'.$targetId);
        $this->client->submitForm('Delete');

        $this->assertResponseRedirects('/admin/user/');
        $this->assertNull($this->reloadUser($targetId));
    }

    public function testDisabledUserCannotLogin(): void
    {
        $this->createUser('admin-test-disabled@example.com', enabled: false);

        $this->client->request('GET', '/login');
        $this->client->submitForm('login', [
            '_username' => 'admin-test-disabled@example.com',
            '_password' => 'testpass',
        ]);

        $this->assertResponseRedirects('/login');
        $crawler = $this->client->followRedirect();
        $this->assertStringContainsString('disabled', $crawler->text());
    }

    private function createUser(string $email, array $roles = [], bool $enabled = true): User
    {
        $user = new User();
        $user
            ->setEmail($email)
            ->setPassword(self::PASSWORD_HASH)
            ->setIsVerified(true)
            ->setRoles($roles)
            ->setEnabled($enabled)
        ;
        $entityManager = $this->getEntityManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    private function reloadUser(int $id): ?User
    {
        $entityManager = $this->getEntityManager();
        $entityManager->clear();

        return $entityManager->find(User::class, $id);
    }

    private function removeTestUsers(): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->createQuery('DELETE FROM App\Entity\User u WHERE u.email IN (:emails)')
            ->setParameter('emails', self::TEST_EMAILS)
            ->execute();
        $entityManager->clear();
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}
