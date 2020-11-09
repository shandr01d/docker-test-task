<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Dto\TaskDto;
use App\Dto\TaskUpdateDto;
use App\Repository\TaskRepository;
use App\Validator\Constraints\CorrectTaskStatus;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={
 *          "get"={
 *              "normalization_context"={"groups"="task:list"}
 *          },
 *          "post"={
 *              "normalization_context"={"groups"="task:list"},
 *              "input"=TaskDto::class
 *          }
 *     },
 *     itemOperations={
 *          "get"={
 *              "normalization_context"={"groups"="task:item"}
 *          },
 *          "patch"={
 *              "normalization_context"={"groups"="task:item"},
 *              "input"=TaskUpdateDto::class,
 *              "input_formats"={"json"={"application/merge-patch+json"}}
 *          },
 *          "delete"
 *     },
 *     order={"createdAt"="DESC"},
 *     paginationEnabled=false
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={"list": "exact"})
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Task
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"task:list", "task:item"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"taskList:item", "task:list", "task:item"})
     *
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @ORM\Column(type="smallint")
     *
     * @Groups({"taskList:item", "task:list", "task:item"})
     *
     * @Assert\NotBlank()
     * @CorrectTaskStatus()
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     **/
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=TaskList::class, inversedBy="tasks", cascade={"persist"})
     *
     * @Groups({"task:item"})
     */
    private $list;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getList(): ?TaskList
    {
        return $this->list;
    }

    public function setList(?TaskList $list): self
    {
        $this->list = $list;

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps(): void
    {
        $this->setUpdatedAt(new \DateTime('now'));
        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new \DateTime('now'));
        }
    }
}
