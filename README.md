# checkmyclass

## 디렉토리 구조

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

## Composer 설치법

> Composer는 npm처럼 외부에서 만든 라브러리를 설치해서 쓸수 있는 기능입니다.

### 설치방법

```
1. https://getcomposer.org/download/ 해당 사이트에 들어가서 윈도우 버전을 설치합니다.
2. 윈도우 버전을 설치를 한후 cmd에들어가서 composer이라고 치고 커맨드 리스트가 뜨면 성공입니다.
```

### 라이브러리 설치

```
라이브러리 같은 경우 아래 명령어를 이용해서 설치바람니다.
`composer require <설치할 라이브 버리 이름>`
```

## DB 및 ORM가이드

ORM_Guid.md 파일을 참고 바람니다.


## env
루트에 `.env`을 만들어서 아래 양식으로 저장바람니다.
```
APP_ENV=dev
# 데이터베이스 연결 정보
DB_HOST=localhost
DB_PORT=3306
DB_NAME=checkmyclass
DB_USER=root
DB_PASS=
```