-- 1. 데이터베이스가 없으면 생성하고 선택
CREATE DATABASE IF NOT EXISTS team002;
USE team002;

-- 2. users 테이블이 없으면 생성
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT '고유 번호(PK)',
    user_id VARCHAR(50) NOT NULL UNIQUE COMMENT '로그인 아이디(학번과 동일)',
    user_password VARCHAR(255) NOT NULL COMMENT '암호화된 비밀번호',
    user_name VARCHAR(50) NOT NULL COMMENT '이름',
    student_number VARCHAR(20) NOT NULL UNIQUE COMMENT '학번/교번',
    phone_number VARCHAR(20) NOT NULL COMMENT '전화번호',
    department VARCHAR(50) NOT NULL COMMENT '학과',
    role VARCHAR(20) DEFAULT 'STUDENT' COMMENT '역할(STUDENT 등)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '가입일자'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



USE team002;

-- 1. 참조 테이블 생성 (순서 중요: FK 때문에 먼저 만들어야 함)

-- 1-1. 단과대학 (College)
CREATE TABLE IF NOT EXISTS College (
    id INT AUTO_INCREMENT PRIMARY KEY,
    college_name VARCHAR(50) NOT NULL COMMENT '단과대학명 (예: 공과대학)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 1-2. 학과 (Major)
CREATE TABLE IF NOT EXISTS Major (
    id INT AUTO_INCREMENT PRIMARY KEY,
    major_name VARCHAR(50) NOT NULL COMMENT '학과명',
    college_id INT NOT NULL,
    FOREIGN KEY (college_id) REFERENCES College(id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 1-3. 건물 (Building)
CREATE TABLE IF NOT EXISTS Building (
    id INT AUTO_INCREMENT PRIMARY KEY,
    building_name VARCHAR(50) NOT NULL COMMENT '건물명 (예: 정보공학관)',
    building_number VARCHAR(10) NOT NULL COMMENT '건물번호 (예: 6번건물)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. 강의실 정보 테이블 (Class)
CREATE TABLE IF NOT EXISTS Class (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_number VARCHAR(20) NOT NULL COMMENT '호실 번호 (예: 415호)',
    building_id INT NOT NULL,
    capacity_number INT DEFAULT 0 COMMENT '수용 가능 인원',
    is_practical CHAR(1) DEFAULT 'N' COMMENT '실기실 여부 (Y/N)',
    board_type VARCHAR(20) COMMENT '칠판 타입 (화이트보드/흑판)',
    image_URL VARCHAR(255) COMMENT '구조도 이미지 경로',
    FOREIGN KEY (building_id) REFERENCES Building(id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. 예약 내역 테이블 (MySchedule)
CREATE TABLE IF NOT EXISTS MySchedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL COMMENT '예약자 ID (users 테이블의 user_id 참조)',
    class_id INT NOT NULL COMMENT '강의실 ID',
    rental_start_time DATETIME NOT NULL,
    rental_end_time DATETIME NOT NULL,
    status VARCHAR(20) DEFAULT '승인대기' COMMENT '예약 상태 (승인대기/승인/반려)',
    create_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '예약 신청 시간',
    FOREIGN KEY (class_id) REFERENCES Class(id) ON UPDATE CASCADE
    -- 주의: users 테이블이 이미 존재해야 하며, user_id 컬럼 타입이 일치해야 합니다.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- [테스트 데이터 넣기] -- 
-- 화면에 뭐라도 나와야 하니까 기초 데이터를 넣어줍니다.

-- 1. 건물 등록 (정보공학관)
INSERT INTO Building (building_name, building_number) VALUES ('정보공학관', '6');

-- 2. 강의실 등록 (정보공학관 415호)
-- 방금 만든 정보공학관의 ID가 1번이라고 가정합니다.
INSERT INTO Class (class_number, building_id, capacity_number, is_practical, board_type, image_URL) 
VALUES ('415호', 1, 40, 'Y', '화이트보드', 'default_room.png');

INSERT INTO Class (class_number, building_id, capacity_number, is_practical, board_type, image_URL) 
VALUES ('410호', 1, 30, 'N', '흑판', 'default_room.png');


USE team002;

-- 1. 단과대학 (College) 데이터 삽입
-- id를 1로 고정해서 넣습니다. (나중에 Major에서 1번을 참조하기 위함)
INSERT INTO College (id, college_name) 
VALUES (1, '소프트웨어융합대학');

-- 2. 학과 (Major) 데이터 삽입
-- college_id 자리에 방금 만든 단과대의 id인 '1'을 넣습니다.
INSERT INTO Major (major_name, college_id) 
VALUES ('응용소프트웨어공학과', 1);


USE team002;

-- 1. 외래 키 제약 조건 잠시 해제 (삭제 에러 방지)
SET FOREIGN_KEY_CHECKS = 0;

-- 2. 기존 users 테이블 삭제
DROP TABLE IF EXISTS users;

-- 3. 외래 키 제약 조건 다시 활성화
SET FOREIGN_KEY_CHECKS = 1;

-- 4. users 테이블 새로 생성 (major_id 추가 및 연결)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT '고유 번호',
    user_id VARCHAR(50) NOT NULL UNIQUE COMMENT '로그인 아이디 (학번)',
    user_password VARCHAR(255) NOT NULL COMMENT '암호화된 비밀번호',
    user_name VARCHAR(50) NOT NULL COMMENT '이름',
    student_number VARCHAR(20) NOT NULL COMMENT '학번/교번',
    phone_number VARCHAR(20) NOT NULL COMMENT '전화번호',
    
    -- [핵심 변경 사항] 문자열 대신 학과 테이블의 ID(숫자)를 저장
    major_id INT NOT NULL COMMENT '학과 ID (Major 테이블 참조)',
    
    role VARCHAR(20) DEFAULT 'STUDENT' COMMENT '역할 (STUDENT/STAFF)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- [연결 고리] users 테이블의 major_id는 Major 테이블의 id를 가리킨다.
    FOREIGN KEY (major_id) REFERENCES Major(id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;