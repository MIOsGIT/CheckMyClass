# 📘 CheckMyClass 프로젝트 ORM 통합 가이드

이 문서는 **PHP**와 **Doctrine ORM**을 사용하여 구축된 `CheckMyClass` 프로젝트의 데이터베이스 연동 및 관리 방법을 설명합니다.

## 1\. 📂 프로젝트 폴더 구조 (Directory Structure)

ORM이 정상적으로 작동하기 위해 다음 파일 및 폴더 구조를 유지해야 합니다.

```
CheckMyClass/
├── .env                  # [설정] DB 접속 정보 (보안 주의)
├── bootstrap.php         # [핵심] Doctrine 실행 및 EntityManager 반환
├── cli-config.php        # [도구] Doctrine CLI 명령어 설정
├── composer.json         # [의존성] 라이브러리 목록
├── api/                  # [API] 실제 기능 구현 (회원가입, 로그인 등)
│   └── register.php
├── db/
│   └── Entity/           # [엔티티] DB 테이블과 매핑되는 클래스들
│       ├── User.php
│       ├── Major.php
│       ├── Building.php
│       ├── ...
└── vendor/               # [라이브러리] Composer로 설치된 패키지들
```

---

## 2\. ⚙️ 핵심 설정 파일 점검

### 2.1. `bootstrap.php` (ORM 엔진)

이 파일은 프로젝트의 심장입니다. DB 연결을 맺고 `EntityManager` 객체를 생성하여 반환합니다.

- **역할:** API 파일들이 이 파일을 `require`하여 DB 기능을 가져다 씁니다.
- **주의사항:** Doctrine 3.x 버전에 맞춰 `DriverManager`와 `new EntityManager` 방식을 사용해야 합니다.

### 2.2. `.env` (환경 변수)

DB 접속 정보는 코드에 직접 적지 않고 이곳에서 관리합니다.

```ini
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=checkmyclass
```

---

## 3\. 데이터베이스 스키마 동기화 (Migration)

엔티티(`db/Entity/*.php`) 코드를 수정했다면, 실제 데이터베이스(MySQL)에 반영해 주어야 합니다.

### 터미널 명령어

프로젝트 루트 폴더(`CheckMyClass/`)에서 다음 명령어를 실행합니다.

**1. 변경 사항 확인 (SQL 미리보기)**

```bash
vendor/bin/doctrine orm:schema-tool:update --dump-sql
```

> 실행될 SQL 쿼리를 미리 보여줍니다. 데이터 삭제 등의 위험한 쿼리가 있는지 확인하세요.

**2. 변경 사항 적용 (DB 업데이트)**

```bash
vendor/bin/doctrine orm:schema-tool:update --force
```

> **주의:** 이 명령어는 DB 구조를 엔티티에 맞춰 강제로 변경합니다. 운영 중인 서비스에서는 데이터 유실에 주의해야 합니다.

---

## 4\. API에서 ORM 사용하기

`api/` 폴더 내의 PHP 파일에서 ORM을 가져와 사용하는 표준 패턴입니다.

### 4.1. 기본 코드 패턴

```php
<?php
// 1. EntityManager 불러오기 (bootstrap.php 경로 주의)
/** @var \Doctrine\ORM\EntityManager $entityManager */
$entityManager = require __DIR__ . '/../bootstrap.php';

use App\Db\Entity\User; // 사용할 엔티티 import

// 2. 사용 예시
// ...
```

### 4.2. 주요 기능 예제 (CRUD)

#### 📝 데이터 생성 (INSERT)

```php
$user = new User();
$user->setUserId('student123');
$user->setUserName('홍길동');
// ... 필요한 데이터 set

$entityManager->persist($user); // 영속성 컨텍스트에 저장 (메모리)
$entityManager->flush();        // 실제 DB에 쿼리 전송 (COMMIT)
```

#### 🔍 데이터 조회 (SELECT)

```php
$userRepository = $entityManager->getRepository(User::class);

// ID(PK)로 찾기
$user = $userRepository->find(1);

// 조건으로 찾기
$user = $userRepository->findOneBy(['userId' => 'student123']);
```

#### ✏️ 데이터 수정 (UPDATE)

```php
$user = $userRepository->find(1);
if ($user) {
    $user->setUserName('이름변경'); // 객체 값만 변경하면 됨
    $entityManager->flush();        // 변경 감지 후 자동 UPDATE 쿼리 실행
}
```

#### 🗑️ 데이터 삭제 (DELETE)

```php
$user = $userRepository->find(1);
if ($user) {
    $entityManager->remove($user);
    $entityManager->flush();
}
```

---

## 5\. ✅ 개발 체크리스트 (Troubleshooting)

개발 중 자주 발생하는 문제와 해결법입니다.

1.  **Column not found 에러**

    - **원인:** PHP 변수명(camelCase)과 DB 컬럼명(snake_case)이 달라서 발생.
    - **해결:** 엔티티 파일의 `#[ORM\Column]` 속성에 `name: '실제_컬럼명'`을 반드시 명시해야 합니다.

2.  **Class not found 에러**

    - **원인:** 네임스페이스(`App\Db\Entity`)가 잘못되었거나 파일 경로가 틀림.
    - **해결:** `composer.json`의 `autoload` 설정 확인 후 `composer dump-autoload` 명령어를 실행해 보세요.

3.  **EntityManager 관련 에러**

    - **원인:** `bootstrap.php` 설정 오류 또는 Doctrine 버전 불일치.
    - **해결:** `bootstrap.php`가 정상적으로 `$entityManager` 객체를 반환(return)하고 있는지 확인하세요.

---
작성일: 2025. 12. 01. 작성자/제작자: 이호준