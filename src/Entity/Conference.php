<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ConferenceRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use DateTimeInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ConferenceRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=false)
 */
class Conference
{
    use TimestampableEntity, SoftDeleteableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column
     * @Groups({
     *     "global_search",
     *     "api_conferences_all",
     *     "api_reports_store",
     *     "api_conferences_store",
     *     "api_conferences_show",
     *     "api_conferences_subscribed"
     * })
     */
    private ?int $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="conferences")
     * @ORM\JoinTable(name="user_conference")
     */
    private ?Collection $users;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({
     *     "global_search",
     *     "api_conferences_all",
     *     "api_conferences_store",
     *     "api_conferences_show",
     * })
     */
    private ?string $title;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"global_search", "api_conferences_all", "api_conferences_store", "api_conferences_show"})
     */
    private ?DateTimeInterface $startedAt;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"global_search", "api_conferences_all", "api_conferences_store", "api_conferences_show"})
     */
    private ?DateTimeInterface $endedAt;

    /**
     * @ORM\Column(type="json")
     * @Groups({"api_conferences_store", "api_conferences_show"})
     */
    private ?array $address = [];

    /**
     * @ORM\Column(type="string", length=30)
     * @Groups({"api_conferences_store", "api_conferences_show"})
     */
    private ?string $country;

    /**
     * @ORM\OneToMany(targetEntity=Report::class, mappedBy="conference", orphanRemoval=true)
     */
    private ?Collection $reports;

    /**
     * @var DateTime|null
     *
     * @Gedmo\Timestampable(on="create")
     *
     * @ORM\Column(type="datetime")
     * @Groups({"api_conferences_store"})
     */
    protected $createdAt;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->reports = new ArrayCollection();
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
            $this->users->removeElement($user);
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

    public function getStartedAt(): ?DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(?DateTimeInterface $startedAt): self
    {
        $this->startedAt = $startedAt;

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

    /**
     * @return Collection<int, Report>
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function addReport(Report $report): self
    {
        if (!$this->reports->contains($report)) {
            $this->reports[] = $report;
            $report->setConference($this);
        }

        return $this;
    }

    public function removeReport(Report $report): self
    {
        if ($this->reports->removeElement($report)) {
            if ($report->getConference() === $this) {
                $report->setConference(null);
            }
        }

        return $this;
    }

    public function getEndedAt(): ?DateTimeInterface
    {
        return $this->endedAt;
    }

    public function setEndedAt(?DateTimeInterface $endedAt): self
    {
        $this->endedAt = $endedAt;

        return $this;
    }
}
