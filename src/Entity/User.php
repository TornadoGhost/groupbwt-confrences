<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=false)
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimestampableEntity, SoftDeleteableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"api_reports_store", "api_report_comments_index"})
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     * @Groups({"auth"})
     */
    private ?string $email;

    /**
     * @ORM\Column(type="json")
     * @Groups({"auth"})
     */
    private ?array $roles;

    /**
     * @var string|null The hashed password
     * @ORM\Column(type="string")
     */
    private ?string $password;

    /**
     * @ORM\ManyToOne(inversedBy="users")
     * @Groups({"auth"})
     */
    private ?Type $type;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Conference", mappedBy="users")
     * @ORM\JoinTable(name="user_conference")
     */
    private ?Collection $conferences;

    /**
     * @ORM\Column(type="string", length=100)
     * @Groups({"api_report_comments_index", "auth"})
     */
    private ?string $firstname;

    /**
     * @ORM\Column(type="string", length=100)
     * @Groups({"api_report_comments_index", "auth"})
     */
    private ?string $lastname;

    /**
     * @var null|DateTimeInterface A "Y-m-d" formatted value
     * @ORM\Column(type="date")
     */
    private ?DateTimeInterface $birthdate;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private ?string $country;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private ?string $phone;

    /**
     * @ORM\OneToMany(targetEntity=ReportComment::class, mappedBy="user")
     */
    private ?Collection $reportComments;

    /**
     * @ORM\OneToMany(targetEntity=Report::class, mappedBy="user")
     */
    private ?Collection $reports;

    /**
     * @ORM\ManyToMany(targetEntity=Notification::class, mappedBy="users", fetch="EAGER")
     */
    private ?Collection $notifications;

    public function __construct()
    {
        $this->conferences = new ArrayCollection();
        $this->setRoles(['ROLE_USER']);
        $this->reportComments = new ArrayCollection();
        $this->reports = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): ?string
    {
        return (string)$this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): ?string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): ?array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(?Type $type): ?User
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection|null
     */
    public function getConferences(): ?Collection
    {
        return $this->conferences;
    }

    public function addConference(?Conference $conference): self
    {
        if (!$this->conferences->contains($conference)) {
            $this->conferences[] = $conference;
            // test
            $conference->addUser($this);
        }

        return $this;
    }

    public function removeConference(Conference $conference): self
    {
        if ($this->conferences->contains($conference)) {
            $this->conferences->removeElement($conference);
            $conference->removeUser($this);
        }

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getBirthdate(): ?DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(DateTimeInterface $birthdate): self
    {
        $this->birthdate = $birthdate;

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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return Collection<int, ReportComment>
     */
    public function getReportComments(): ?Collection
    {
        return $this->reportComments;
    }

    public function addReportComment(?ReportComment $reportComment): self
    {
        if (!$this->reportComments->contains($reportComment)) {
            $this->reportComments[] = $reportComment;
            $reportComment->setUser($this);
        }

        return $this;
    }

    public function removeReportComment(ReportComment $reportComment): self
    {
        if ($this->reportComments->removeElement($reportComment)) {
            if ($reportComment->getUser() === $this) {
                $reportComment->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Report>
     */
    public function getReports(): ?Collection
    {
        return $this->reports;
    }

    public function addReport(?Report $report): self
    {
        if (!$this->reports->contains($report)) {
            $this->reports[] = $report;
            $report->setUser($this);
        }

        return $this;
    }

    public function removeReport(Report $report): self
    {
        if ($this->reports->removeElement($report)) {
            if ($report->getUser() === $this) {
                $report->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(?Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->addUser($this);
        }

        return $this;
    }

    public function removeNotification(?Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            $notification->removeUser($this);
        }

        return $this;
    }
}
