# 📋 회원가입 API (User Registration)

## 1\. 개요

신규 사용자의 정보를 받아 계정을 생성하고 데이터베이스에 저장합니다.

- **URL:** `/api/register.php`
- **Method:** `POST`
- **Content-Type:** `application/json`

---

## 2\. 요청 (Request)

### 2.1 헤더 (Headers)

| Key            | Value              | 필수 여부 | 설명                             |
| :------------- | :----------------- | :-------- | :------------------------------- |
| `Content-Type` | `application/json` | **필수**  | 전송 데이터 형식을 JSON으로 지정 |

### 2.2 바디 파라미터 (Body Parameters)

클라이언트는 아래의 데이터를 JSON 객체로 전송해야 합니다.

| 필드명               | 타입    | 필수 | 설명              | 제약 조건                           |
| :------------------- | :------ | :--- | :---------------- | :---------------------------------- |
| **`user_id`**        | String  | O    | 사용자 아이디     | 중복 불가 (Unique)                  |
| **`user_password`**  | String  | O    | 비밀번호          | DB 저장 시 암호화됨                 |
| **`user_name`**      | String  | O    | 사용자 실명       |                                     |
| **`student_number`** | String  | O    | 학번              | 중복 불가 (Unique)                  |
| **`major_id`**       | Integer | O    | 소속 학과 고유 ID | `Major` 테이블에 존재하는 ID여야 함 |
| `phone_number`       | String  | X    | 전화번호          | 선택 입력, 중복 불가 (Unique)       |

### 2.3 요청 예시 (Example Request)

```json
{
  "user_id": "kim123",
  "user_password": "mypassword123!",
  "user_name": "김철수",
  "student_number": "20240001",
  "major_id": 5,
  "phone_number": "010-1234-5678"
}
```

---

## 3\. 응답 (Response)

### 3.1 성공 (Success)

회원가입이 정상적으로 완료되었을 때의 응답입니다.

- **Status Code:** `201 Created`

<!-- end list -->

```json
{
  "success": true,
  "message": "회원가입이 성공적으로 완료되었습니다.",
  "user_id": 15
}
```

### 3.2 실패 (Error)

요청이 실패했을 때의 시나리오별 응답입니다.

#### A. 필수 입력값 누락 (400 Bad Request)

필수 필드가 전송되지 않았을 때 발생합니다.

```json
{
  "success": false,
  "message": "user_id 필드는 필수입니다."
}
```

#### B. 존재하지 않는 학과 ID (400 Bad Request)

`major_id`가 DB의 `Major` 테이블에 없을 때 발생합니다.

```json
{
  "success": false,
  "message": "존재하지 않는 학과 ID입니다."
}
```

#### C. 아이디 중복 (409 Conflict)

이미 존재하는 `user_id`로 가입을 시도했을 때 발생합니다.

```json
{
  "success": false,
  "message": "이미 사용 중인 아이디입니다."
}
```

#### D. 기타 정보 중복 (409 Conflict)

`학번(student_number)` 또는 `전화번호(phone_number)`가 이미 등록되어 있을 때 발생합니다.

```json
{
  "success": false,
  "message": "이미 등록된 사용자 정보(학번 또는 전화번호)가 있습니다."
}
```

#### E. 잘못된 요청 방식 (405 Method Not Allowed)

`POST`가 아닌 `GET` 등의 방식으로 요청하거나, JSON 형식이 아닐 때 발생합니다.

```json
{
  "success": false,
  "message": "유효하지 않은 요청 방식 또는 JSON 형식입니다."
}
```

#### F. 서버 내부 오류 (500 Internal Server Error)

데이터베이스 연결 실패 등 예기치 못한 서버 오류 시 발생합니다.

```json
{
  "success": false,
  "message": "서버 오류가 발생했습니다."
}
```

---

## 4\. 참고 사항 (Notes)

- **비밀번호 보안:** 서버는 전달받은 비밀번호를 `password_hash()` (BCRYPT 알고리즘)를 사용하여 암호화 저장합니다.
- **CORS:** 현재 개발 편의를 위해 모든 도메인(`*`)에서의 요청을 허용하고 있습니다. 운영 배포 시 보안 설정 변경이 필요할 수 있습니다.

---
작성일: 2025. 12. 01. 작성자/제작자: 이호준