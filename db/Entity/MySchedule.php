<?php

namespace App\Db\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'MySchedule')]
class MySchedule
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    //  name: 'rental_start_time' 추가
    #[ORM\Column(name: 'rental_start_time', type: 'datetime')]
    private \DateTimeImmutable $rentalStartTime;

    //  name: 'rental_end_time' 추가
    #[ORM\Column(name: 'rental_end_time', type: 'datetime')]
    private \DateTimeImmutable $rentalEndTime;

    #[ORM\Column(name: 'create_time', type: 'datetime')]
    private \DateTimeImmutable $createTime;

    #[ORM\Column(name: 'delete_time', type: 'datetime', nullable: true)]
    private ?\DateTimeImmutable $deleteTime = null;

    // 외래 키
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: ClassEntity::class, inversedBy: 'schedules')]
    #[ORM\JoinColumn(name: 'class_id', referencedColumnName: 'id', nullable: false)]
    private ClassEntity $class;

    // --- 생성자 및 Getter/Setter ---
    
    public function __construct()
    {
        $this->createTime = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    
    public function getRentalStartTime(): \DateTimeImmutable { return $this->rentalStartTime; }
    public function setRentalStartTime(\DateTimeImmutable $rentalStartTime): static { $this->rentalStartTime = $rentalStartTime; return $this; }
} 