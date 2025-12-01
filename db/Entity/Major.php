<?php

namespace App\Db\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'Major')]
class Major
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    //  name: 'major_name' 추가
    #[ORM\Column(name: 'major_name', type: 'string', length: 50, unique: true)]
    private string $majorName;

    #[ORM\Column(name: 'create_time', type: 'datetime_immutable')]
    private \DateTimeImmutable $createTime;

    #[ORM\Column(name: 'delete_time', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $deleteTime = null;

    // 외래 키 (college_id)
    #[ORM\ManyToOne(targetEntity: College::class, inversedBy: 'majors')]
    #[ORM\JoinColumn(name: 'college_id', referencedColumnName: 'id', nullable: false)]
    private College $college;
    
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'major')]
    private Collection $users;

    #[ORM\OneToMany(targetEntity: ClassEntity::class, mappedBy: 'major')]
    private Collection $classes;

    // --- 생성자 및 Getter/Setter ---
    
    public function __construct()
    {
        $this->createTime = new \DateTimeImmutable();
        $this->users = new ArrayCollection();
        $this->classes = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getMajorName(): string { return $this->majorName; }
    public function setMajorName(string $majorName): static { $this->majorName = $majorName; return $this; }

    public function getCollege(): College { return $this->college; }
    public function setCollege(College $college): static { $this->college = $college; return $this; }
    
    public function getCreateTime(): \DateTimeImmutable { return $this->createTime; }
}