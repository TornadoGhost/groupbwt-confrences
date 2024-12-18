<?php

namespace App\Entity;

use App\Repository\ConferenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use DateTimeInterface;

/**
 * @ORM\Entity(repositoryClass=ConferenceRepository::class)
 */
class Conference
{
    use TimestampableEntity, SoftDeleteableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column
     */
    private ?int $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="conferences")
     * @ORM\JoinTable(name="user_conference")
     */
    private ?Collection $users;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $title;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?DateTimeInterface $start;

    /**
     * @ORM\Column(type="json")
     */
    private array $address = [];

    /**
     * @ORM\Column(type="string", length=30)
     */
    private ?string $country;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @param User|UserInterface $user
     * @return $this
     */
    public function addUser($user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addConference($this);
        }

        return $this;
    }

    /**
     * @param User|UserInterface $user
     * @return $this
     */
    public function removeUser($user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeConference($this);
        }

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getStart(): ?DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(?DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getAddress(): ?array
    {
        return $this->address;
    }

    public function setAddress(?array $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }
}
