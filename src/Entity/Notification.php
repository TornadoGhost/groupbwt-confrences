<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 */
class Notification
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"api_notifications_user"})
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"api_notifications_user"})
     */
    private ?string $title;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"api_notifications_user"})
     */
    private ?string $message;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"api_notifications_user"})
     */
    private ?string $link;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="notifications")
     */
    private ?Collection $user;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     * @Groups({"api_notifications_user"})
     */
    private ?bool $viewed;

    /**
     * @var \DateTime|null
     *
     * @Gedmo\Timestampable(on="create")
     *
     * @ORM\Column(type="datetime")
     * @Groups({"api_notifications_user"})
     */
    protected $createdAt;

    public function __construct()
    {
        $this->user = new ArrayCollection();
        $this->viewed = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(?User $user): self
    {
        if (!$this->user->contains($user)) {
            $this->user[] = $user;
        }

        return $this;
    }

    public function removeUser(?User $user): self
    {
        $this->user->removeElement($user);

        return $this;
    }

    public function isViewed(): ?bool
    {
        return $this->viewed;
    }

    public function setViewed(?bool $viewed): self
    {
        $this->viewed = $viewed;

        return $this;
    }
}
