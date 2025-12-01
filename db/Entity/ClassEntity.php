<?php

namespace App\Db\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'Class')]
#[ORM\UniqueConstraint(name: 'unique_class_in_building', columns: ['building_id', 'class_number'])]
class ClassEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    // 🚨 name: 'class_number' 추가
    #[ORM\Column(name: 'class_number', type: 'string', length: 20)]
    private string $classNumber;

    //  name: 'capacity_number' 추가
    #[ORM\Column(name: 'capacity_number', type: 'integer')]
    private int $capacityNumber;
    
    // name: 'is_practical' 추가 (DB 컬럼명이 is_practical 이라고 가정)
    #[ORM\Column(name: 'is_practical', type: 'boolean')]
    private bool $isPractical = false;

    //  name: 'board_type' 추가
    #[ORM\Column(name: 'board_type', type: 'string', length: 20, nullable: true)]
    private ?string $boardType = null;

    //  name: 'image_URL' 추가
    #[ORM\Column(name: 'image_URL', type: 'string', length: 255, nullable: true)]
    private ?string $imageURL = null;

    //  name: 'is_central_manage' 추가
    #[ORM\Column(name: 'is_central_manage', type: 'boolean')]
    private bool $isCentralManage = true;

    //  name: 'is_layer' 추가
    #[ORM\Column(name: 'is_layer', type: 'boolean')]
    private bool $isLayer = false;

    #[ORM\Column(name: 'create_time', type: 'datetime')]
    private \DateTimeImmutable $createTime;

    #[ORM\Column(name: 'delete_time', type: 'datetime', nullable: true)]
    private ?\DateTimeImmutable $deleteTime = null;

    // 외래 키
    #[ORM\ManyToOne(targetEntity: Building::class, inversedBy: 'classes')]
    #[ORM\JoinColumn(name: 'building_id', referencedColumnName: 'id', nullable: false)]
    private Building $building;
    
    #[ORM\ManyToOne(targetEntity: Major::class, inversedBy: 'classes')]
    #[ORM\JoinColumn(name: 'major_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Major $major = null;
    
    #[ORM\OneToMany(targetEntity: MySchedule::class, mappedBy: 'class')]
    private Collection $schedules;

    // --- 생성자 및 Getter/Setter ---

    public function __construct()
    {
        $this->createTime = new \DateTimeImmutable();
        $this->schedules = new ArrayCollection();
    }
    
    public function getId(): ?int { return $this->id; }
    // ... 나머지 Getter/Setter는 필요에 따라 추가
}