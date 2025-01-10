<?php

namespace App\Entity;

use App\Repository\ReportRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ReportRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=false)
 */
class Report
{
    use TimestampableEntity, SoftDeleteableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column
     * @Groups({"global_search", "api_reports_all", "api_reports_store", "api_reports_show"})
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"global_search", "api_reports_all", "api_reports_store", "api_reports_show"})
     */
    private ?string $title;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"api_reports_all", "api_reports_store", "api_reports_show"})
     */
    private ?DateTimeInterface $startedAt;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"api_reports_all", "api_reports_store", "api_reports_show"})
     */
    private ?DateTimeInterface $endedAt;

    /**
     * @ORM\Column(type="text", columnDefinition="TEXT")
     * @Groups({"api_reports_all", "api_reports_store", "api_reports_show"})
     */
    private ?string $description;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"api_reports_store", "api_reports_show"})
     */
    private ?string $document = null;

    /**
     * @ORM\ManyToOne(targetEntity=Conference::class, inversedBy="reports")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"global_search", "api_reports_store"})
     */
    private ?Conference $conference;

    /**
     * @ORM\OneToMany(targetEntity=ReportComment::class, mappedBy="report")
     */
    private ?Collection $reportComments;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="reports")
     * @Groups({"global_search", "api_reports_store"})
     */
    private ?User $user;

    /**
     * @var \DateTime|null
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     * @Groups({"api_reports_store"})
     */
    protected $createdAt = null;

    public function __construct()
    {
        $this->reportComments = new ArrayCollection();
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

    public function getStartedAt(): ?DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(?DateTimeInterface $startedAt): self
    {
        $this->startedAt = $startedAt;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDocument(): ?string
    {
        return $this->document;
    }

    public function setDocument(?string $document): self
    {
        $this->document = $document;

        return $this;
    }

    public function getConference(): ?Conference
    {
        return $this->conference;
    }

    public function setConference(?Conference $conference): self
    {
        $this->conference = $conference;

        return $this;
    }

    /**
     * @return Collection<int, ReportComment>
     */
    public function getReportComments(): Collection
    {
        return $this->reportComments;
    }

    public function addReportComment(ReportComment $reportComment): self
    {
        if (!$this->reportComments->contains($reportComment)) {
            $this->reportComments[] = $reportComment;
            $reportComment->setReport($this);
        }

        return $this;
    }

    public function removeReportComment(ReportComment $reportComment): self
    {
        if ($this->reportComments->removeElement($reportComment)) {
            if ($reportComment->getReport() === $this) {
                $reportComment->setReport(null);
            }
        }

        return $this;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

}
