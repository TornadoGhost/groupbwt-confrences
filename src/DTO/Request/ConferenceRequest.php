<?php

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class ConferenceRequest
{
    /**
     * @Assert\NotNull
     * @Assert\NotBlank
     * @Assert\Length(
     *     min = 2,
     *     max = 255,
     *     minMessage = "The title should be at least {{ limit }} characters",
     *     maxMessage = "The title should тbe not longer than {{ limit }} characters",
     * )
     * @Assert\Type("alnum")
     */
    private string $title;

    /**
     * @Assert\NotNull
     * @Assert\NotBlank
     * @Assert\DateTime(format = "Y-m-d H:i")
     */
    private string $startedAt;


    /**
     * @Assert\NotNull
     * @Assert\NotBlank
     * @Assert\DateTime(format = "Y-m-d H:i")
     */
    private string $endedAt;


    /**
     * @Assert\NotNull
     * @Assert\NotBlank
     * @Assert\Length(
     *     min = 1,
     *     max = 11,
     *     minMessage = "The Latitude should be at least {{ limit }} characters",
     *     maxMessage = "The Latitude should be less than {{ limit }} characters",
     * )
     * @Assert\Type("numeric")
     */
    private string $latitude;

    /**
     * @Assert\NotNull
     * @Assert\NotBlank
     * @Assert\Length(
     *     min = 1,
     *     max = 11,
     *     minMessage = "The Longitude should be at least {{ limit }} characters",
     *     maxMessage = "The Longitude should be less than {{ limit }} characters",
     * )
     * @Assert\Type("numeric")
     */
    private string $longitude;

    /**
     * @Assert\NotNull
     * @Assert\NotBlank
     * @Assert\Country
     */
    private string $country;

    public function __construct(
        string $title,
        string $startedAt,
        string $endedAt,
        string $latitude,
        string $longitude,
        string $country
    )
    {
        $this->title = $title;
        $this->startedAt = $startedAt;
        $this->endedAt = $endedAt;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->country = $country;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getStartedAt(): string
    {
        return $this->startedAt;
    }

    public function getEndedAt(): string
    {
        return $this->endedAt;
    }

    public function getLatitude(): string
    {
        return $this->latitude;
    }

    public function getLongitude(): string
    {
        return $this->longitude;
    }

    public function getCountry(): string
    {
        return $this->country;
    }
}
