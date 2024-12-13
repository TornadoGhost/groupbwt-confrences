<?php

namespace App\Entity;

use App\Repository\ConferenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConferenceRepository::class)
 */
class Conference
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column
     */
    private ?int $id;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="conference")
     */
    private Collection $users;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private ?string $title;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTimeInterface $start;

    /**
     * @ORM\Column(type="json")
     */
    private array $address = [];

    /**
     * @ORM\Column(type="string", length=30)
     */
    private ?string $country;

    /**
     * @ORM\Column(type="boolean", options={"default":0})
     */
    private ?bool $deletedAt;

    public function __construct()
    {
        $this->users = new ArrayCollection();
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

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addConference($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
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

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getAddress(): ?array
    {
        return $this->address;
    }

    public function setAddress(array $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function isDeletedAt(): ?bool
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(bool $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}
