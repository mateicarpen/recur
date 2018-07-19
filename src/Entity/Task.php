<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TaskRepository")
 */
class Task
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\FrequencyUnit")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $frequencyUnit;

    /**
     * @ORM\Column(type="integer")
     */
    private $frequency;

    /**
     * @ORM\Column(type="date")
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastCompleted;

    /**
     * @ORM\Column(type="boolean")
     */
    private $adjustOnCompletion;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updateDate;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TaskLog", mappedBy="task", orphanRemoval=true)
     */
    private $taskLogs;

    public function __construct()
    {
        $this->taskLogs = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFrequencyUnit(): ?FrequencyUnit
    {
        return $this->frequencyUnit;
    }

    public function setFrequencyUnit(FrequencyUnit $frequencyUnit): self
    {
        $this->frequencyUnit = $frequencyUnit;

        return $this;
    }

    public function getFrequency(): ?int
    {
        return $this->frequency;
    }

    public function setFrequency(int $frequency): self
    {
        $this->frequency = $frequency;

        return $this;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setAdjustOnCompletion(bool $adjustOnCompletion): self
    {
        $this->adjustOnCompletion = $adjustOnCompletion;

        return $this;
    }

    public function getAdjustOnCompletion(): ?bool
    {
        return $this->adjustOnCompletion;
    }

    public function setLastCompleted(\DateTimeInterface $lastCompleted = null): self
    {
        $this->lastCompleted = $lastCompleted;

        return $this;
    }

    public function getLastCompleted(): ?\DateTime
    {
        return $this->lastCompleted;
    }

    public function setCreateDate(\DateTimeInterface $createDate): self
    {
        $this->createDate = $createDate;

        return $this;
    }

    public function getCreateDate(): ?\DateTimeInterface
    {
        return $this->createDate;
    }

    public function getUpdateDate(): ?\DateTimeInterface
    {
        return $this->updateDate;
    }

    public function setUpdateDate(\DateTimeInterface $updateDate): self
    {
        $this->updateDate = $updateDate;

        return $this;
    }
}
