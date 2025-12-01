<?php

namespace App\Db\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'Building')]
class Building
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    // 🚨 name: 'building_name' 추가
    #[ORM\Column(name: 'building_name', type: 'string', length: 50, unique: true)]
    private string $buildingName;

    // 🚨 name: 'building_number' 추가
    #[ORM\Column(name: 'building_number', type: 'string', length: 10, unique: true)]
    private string $buildingNumber;

    #[ORM\Column(name: 'create_time', type: 'datetime')]
    private \DateTimeImmutable $createTime;

    #[ORM\Column(name: 'delete_time', type: 'datetime', nullable: true)]
    private ?\DateTimeImmutable $deleteTime = null;

    #[ORM\OneToMany(targetEntity: ClassEntity::class, mappedBy: 'building')]
    private Collection $classes;

    // --- 생성자 및 Getter/Setter ---
    
    public function __construct()
    {
        $this->createTime = new \DateTimeImmutable();
        $this->classes = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getBuildingName(): string { return $this->buildingName; }
    public function setBuildingName(string $buildingName): static { $this->buildingName = $buildingName; return $this; }

    public function getBuildingNumber(): string { return $this->buildingNumber; }
    public function setBuildingNumber(string $buildingNumber): static { $this->buildingNumber = $buildingNumber; return $this; }
}