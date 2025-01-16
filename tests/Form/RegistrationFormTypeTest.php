<?php

declare(strict_types=1);

namespace App\Test\Form;

use App\Entity\Type;
use App\Entity\User;
use App\Form\ConferenceType;
use App\Form\RegistrationFormType;
use App\Tests\AbstractTypeTestCase;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\PreloadedExtension;

class RegistrationFormTypeTest extends AbstractTypeTestCase
{
    private array $testTypes;
    protected function setUp(): void
    {
        parent::setUp();

        $type1 = (new Type())->setName('Listener');
        $this->setEntityId($type1, 1);

        $type2 = (new Type())->setName('Announcer');
        $this->setEntityId($type2, 2);

        $this->testTypes = [
            '1' => $type1,
            '2' => $type2
        ];

        $this->type = $this->createMock(Type::class);
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            'email' => 'test@example.com',
            'plainPassword' => '12345678',
            'firstname' => 'John123',
            'lastname' => 'Deep123',
            'birthdate' => '1997-04-29',
            'country' => 'US',
            'phone' => '+1234567890',
            'type' => 0,
        ];

        $choiceLoader = new CallbackChoiceLoader(function () {
            return $this->testTypes;
        });

        $user = new User();
        $form = $this->factory->create(RegistrationFormType::class, $user, [
            'test_choice_loader' => $choiceLoader,
        ]);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized(), 'Form is not synchronized.');
        $this->assertTrue($form->isValid(), 'Form is not valid.');

        $this->assertSame($formData['email'], $user->getEmail());
        $this->assertSame($formData['firstname'], $user->getFirstname());
        $this->assertSame($formData['lastname'], $user->getLastname());
        $this->assertEquals(new \DateTime($formData['birthdate']), $user->getBirthdate());
        $this->assertSame($formData['country'], $user->getCountry());
        $this->assertSame($formData['phone'], $user->getPhone());
        $this->assertSame($formData['type'] + 1, $user->getType()->getId());
    }

    public function testSubmitInvalidData(): void
    {
        $formData = [
            'email' => '',
            'plainPassword' => '',
            'firstname' => '',
            'lastname' => '',
            'birthdate' => '',
            'country' => '',
            'phone' => '',
            'type' => '',
        ];

        $form = $this->factory->create(ConferenceType::class);
        $form->submit($formData);

        $this->assertFalse($form->isValid(), 'Form should not be valid.');
        $this->assertGreaterThan(
            0,
            count($form->getErrors(true, false)), 'Form should contain errors.'
        );
    }

    protected function getExtensions()
    {
        $query = $this->createMock(AbstractQuery::class);
        $query->method('execute')
            ->willReturn([$this->type]);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('getQuery')
            ->willReturn($query);

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('createQueryBuilder')
            ->with('e', null)
            ->willReturn($queryBuilder);

        $metaData = $this->createMock(ClassMetadata::class);
        $metaData = new ClassMetadata(Type::class);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')
            ->with(Type::class)
            ->willReturn($repository);
        $entityManager->method('getClassMetadata')
            ->with(Type::class)
            ->willReturn($metaData);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')
            ->with(Type::class)
            ->willReturn($entityManager);

        $entityType = new EntityType($registry);

        return [
            new PreloadedExtension([$entityType], []),
        ];
    }
}
