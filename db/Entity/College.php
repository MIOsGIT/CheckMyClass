<?php

namespace App\Db\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'College')]
class College
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    //  name: 'college_name' 추가
    #[ORM\Column(name: 'college_name', type: 'string', length: 50, unique: true)]
    private string $collegeName;

    #[ORM\Column(name: 'create_time', type: 'datetime')]
    private \DateTimeImmutable $createTime;

    #[ORM\Column(name: 'delete_time', type: 'datetime', nullable: true)]
    private ?\DateTimeImmutable $deleteTime = null;

    #[ORM\OneToMany(targetEntity: Major::class, mappedBy: 'college')]
    private Collection $majors;

    // --- 생성자 및 Getter/Setter ---

    public function __construct()
    {
        $this->createTime = new \DateTimeImmutable();
        $this->majors = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getCollegeName(): string { return $this->collegeName; }
    public function setCollegeName(string $collegeName): static { $this->collegeName = $collegeName; return $this; }

    public function getMajors(): Collection { return $this->majors; }
}