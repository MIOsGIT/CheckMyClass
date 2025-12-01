<?php

namespace App\Db\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Db\Entity\Major;

#[ORM\Entity]
#[ORM\Table(name: 'User')]
class User
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;
    
    //  name: 'user_id' 추가
    #[ORM\Column(name: 'user_id', type: 'string', length: 50, unique: true)]
    private string $userId;

    //  name: 'user_password' 추가
    #[ORM\Column(name: 'user_password', type: 'string', length: 255)]
    private string $userPassword;

    //  name: 'user_name' 추가
    #[ORM\Column(name: 'user_name', type: 'string', length: 50)]
    private string $userName;

    //  name: 'phone_number' 추가
    #[ORM\Column(name: 'phone_number', type: 'string', length: 15, unique: true, nullable: true)]
    private ?string $phoneNumber = null;

    //  name: 'student_number' 추가
    #[ORM\Column(name: 'student_number', type: 'string', length: 20, unique: true)]
    private string $studentNumber;

    #[ORM\Column(name: 'create_time', type: 'datetime_immutable')]
    private \DateTimeImmutable $createTime;

    #[ORM\Column(name: 'delete_time', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $deleteTime = null;
    
    // 외래 키 매핑 (name='major_id'는 JoinColumn에 이미 있음)
    #[ORM\ManyToOne(targetEntity: Major::class)]
    #[ORM\JoinColumn(name: 'major_id', referencedColumnName: 'id', nullable: false)]
    private Major $major;
    
    // --- 생성자 및 Getter/Setter ---

    public function __construct()
    {
        $this->createTime = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getUserId(): string { return $this->userId; }
    public function setUserId(string $userId): static { $this->userId = $userId; return $this; }

    public function getUserPassword(): string { return $this->userPassword; }
    public function setUserPassword(string $userPassword): static { $this->userPassword = $userPassword; return $this; }
    
    public function getUserName(): string { return $this->userName; }
    public function setUserName(string $userName): static { $this->userName = $userName; return $this; }

    public function getStudentNumber(): string { return $this->studentNumber; }
    public function setStudentNumber(string $studentNumber): static { $this->studentNumber = $studentNumber; return $this; }
    
    public function getPhoneNumber(): ?string { return $this->phoneNumber; }
    public function setPhoneNumber(?string $phoneNumber): static { $this->phoneNumber = $phoneNumber; return $this; }

    public function getMajor(): Major { return $this->major; }
    public function setMajor(Major $major): static { $this->major = $major; return $this; }

    public function getCreateTime(): \DateTimeImmutable { return $this->createTime; }
    public function getDeleteTime(): ?\DateTimeImmutable { return $this->deleteTime; }
    public function setDeleteTime(?\DateTimeImmutable $deleteTime): static { $this->deleteTime = $deleteTime; return $this; }
}