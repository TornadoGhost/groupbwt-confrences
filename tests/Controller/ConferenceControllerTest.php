<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Conference;
use App\Tests\AbstractControllerTest;

class ConferenceControllerTest extends AbstractControllerTest
{
    private array $conferenceData = [
        'title' => 'test111',
        'country' => 'GY',
        'latitude' => '123',
        'longitude' => '321',
        'startedAt' => '2025-04-22 10:00',
        'endedAt' => '2025-04-22 17:00'
    ];

    private array $conferenceDataWithErrors = [
        'title' => 'te',
        'country' => 'AA',
        'latitude' => '123',
        'longitude' => '321',
        'startedAt' => '2025-04-22 10:00',
        'endedAt' => '2025-04-22 10:00'
    ];

    public function testConferencesIndex(): void
    {
        $conference = (new Conference())
            ->setTitle('For Test')
            ->setAddress([12321, 123213])
            ->setCountry('BA')
            ->setStartedAt(new \DateTime('2025-02-27 10:00'))
            ->setEndedAt(new \DateTime('2025-02-27 18:00'));

        $this->em->persist($conference);
        $this->em->flush();

        $this->client->request('GET', '/api/v1/conferences');
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertJsonDocumentMatchesSchema($responseContent, [
            'type' => 'object',
            'required' => ['data'],
            'properties' => [
                'data' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'required' => ['id', 'title', 'startedAt', 'endedAt'],
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'title' => ['type' => 'string'],
                            'startedAt' => ['type' => 'string'],
                            'endedAt' => ['type' => 'string'],
                        ]
                    ]
                ]
            ]
        ]);
    }

    public function testConferencesStoreUserUnauthorized(): void
    {
        $this->client->request('POST', '/api/v1/conferences');
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(401);
        $this->assertEquals(['code' => 401, 'message' => 'JWT Token not found'], $response);
    }

    public function testConferencesStoreFormValidationErrors(): void
    {
        $this->loginUserByRole('ROLE_ADMIN');
        $this->client->request(
            'POST',
            '/api/v1/conferences',
            [],
            [],
            [],
            json_encode($this->conferenceDataWithErrors)
        );

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonDocumentMatchesSchema($responseContent, [
            'type' => 'object',
            'required' => ['errors'],
            'properties' => [
                'type' => 'array',
                'properties' => [
                    'title' => ['type' => 'string'],
                    'startedAt' => ['type' => 'string'],
                    'endedAt' => ['type' => 'string'],
                    'latitude' => ['type' => 'string'],
                    'longitude' => ['type' => 'string'],
                    'country' => ['type' => 'string'],
                ]
            ]
        ]);
    }

    public function testConferencesUserNoPermissions()
    {
        $this->loginUserByRole("ROLE_LISTENER");
        $this->client->request(
            'POST',
            '/api/v1/conferences',
            [],
            [],
            [],
            json_encode($this->conferenceData)
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testConferencesStore()
    {
        $this->loginUserByRole('ROLE_ADMIN');
        $this->client->request(
            'POST',
            '/api/v1/conferences',
            [],
            [],
            [],
            json_encode($this->conferenceData)
        );

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonDocumentMatchesSchema($responseContent, [
            'type' => 'object',
            'properties' => [
                'type' => 'array',
                'required' => ['id', 'title', 'startedAt', 'endedAt', 'address', 'country', 'createdAt'],
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'title' => ['type' => 'string'],
                    'startedAt' => ['type' => 'string'],
                    'endedAt' => ['type' => 'string'],
                    'address' => ['type' => 'array',  'properties' => 'string'],
                    'country' => ['type' => 'string'],
                    'createdAt' => ['type' => 'string'],
                ]
            ]
        ]);
    }
}
