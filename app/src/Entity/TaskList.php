<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\TaskListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     collectionOperations={"get"={"normalization_context"={"groups"="taskList:list"}}},
 *     itemOperations={"get"={"normalization_context"={"groups"="taskList:item"}}}
 * )
 * @ApiFilter(SearchFilter::class, properties={"dueDate": "exact"})
 * @ORM\Entity(repositoryClass=TaskListRepository::class)
 */
class TaskList
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups({"taskList:list", "taskList:item"})
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     * @Groups({"taskList:list", "taskList:item", "task:item", "task:list"})
     */
    private $dueDate;

    /**
     * @ORM\OneToMany(targetEntity=Task::class, mappedBy="list")
     *
     * @Groups({"taskList:list", "taskList:item"})
     */
    private $tasks;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="taskLists")
     */
    private $owner;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTimeInterface $dueDate): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    /**
     * @return Collection|Task[]
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setList($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getList() === $this) {
                $task->setList(null);
            }
        }

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?UserInterface $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
