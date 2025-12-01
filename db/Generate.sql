-- -----------------------------------------------------
-- 1. Building (건물) 테이블 생성
-- -----------------------------------------------------
CREATE TABLE Building (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT '고유값 (Primary Key)',
    building_name VARCHAR(50) NOT NULL UNIQUE COMMENT '건물명',
    building_number VARCHAR(10) NOT NULL UNIQUE COMMENT '건물 번호',
    create_time DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '생성 시간',
    delete_time DATETIME NULL COMMENT '삭제 시간'
) COMMENT '건물';

-- -----------------------------------------------------
-- 2. College (단과대학) 테이블 생성
-- -----------------------------------------------------
CREATE TABLE College (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT '고유값 (Primary Key)',
    college_name VARCHAR(50) NOT NULL UNIQUE COMMENT '단과대명',
    create_time DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '생성 시간',
    delete_time DATETIME NULL COMMENT '삭제 시간'
) COMMENT '단과대학';

-- -----------------------------------------------------
-- 3. Major (학과) 테이블 생성
-- -----------------------------------------------------
CREATE TABLE Major (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT '고유값 (Primary Key)',
    major_name VARCHAR(50) NOT NULL UNIQUE COMMENT '학과명',
    college_id INT NOT NULL COMMENT '단과 id (Foreign Key)',
    create_time DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '생성 시간',
    delete_time DATETIME NULL COMMENT '삭제 시간',
    
    FOREIGN KEY (college_id) REFERENCES College(id) 
        ON UPDATE CASCADE 
        ON DELETE RESTRICT
) COMMENT '학과';

-- -----------------------------------------------------
-- 4. User (사용자) 테이블 생성
-- -----------------------------------------------------
CREATE TABLE User (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT '고유값 (Primary Key)',
    user_id VARCHAR(50) NOT NULL UNIQUE COMMENT '아이디',
    user_password VARCHAR(255) NOT NULL COMMENT '비밀번호',
    user_name VARCHAR(50) NOT NULL COMMENT '사용자 이름',
    phone_number VARCHAR(15) UNIQUE COMMENT '전화번호',
    student_number VARCHAR(20) NOT NULL UNIQUE COMMENT '학번',
    major_id INT NOT NULL COMMENT '학과 id (Foreign Key)',
    create_time DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '생성 시간',
    delete_time DATETIME NULL COMMENT '삭제 시간',
    
    FOREIGN KEY (major_id) REFERENCES Major(id) 
        ON UPDATE CASCADE 
        ON DELETE RESTRICT
) COMMENT '사용자';

-- -----------------------------------------------------
-- 5. Class (강의실) 테이블 생성
-- -----------------------------------------------------
CREATE TABLE Class (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT '고유값 (Primary Key)',
    class_number VARCHAR(20) NOT NULL COMMENT '강의실 번호',
    building_id INT NOT NULL COMMENT '건물 id (Foreign Key)',
    major_id INT NULL COMMENT '학과 id (Foreign Key)',
    capacity_number INT NOT NULL COMMENT '수용 가능 인원',
    is_practical BOOLEAN DEFAULT FALSE COMMENT '실기실 여부',
    board_type VARCHAR(20) COMMENT '칠판 타입',
    image_URL VARCHAR(255) COMMENT '구조도 URL',
    is_central_manage BOOLEAN DEFAULT TRUE COMMENT '냉난방 중앙관리 여부',
    is_layer BOOLEAN DEFAULT FALSE COMMENT '단층 여부',
    create_time DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '생성 시간',
    delete_time DATETIME NULL COMMENT '삭제 시간',
    
    UNIQUE KEY uix_class_location (building_id, class_number), 
    
    FOREIGN KEY (building_id) REFERENCES Building(id) 
        ON UPDATE CASCADE 
        ON DELETE RESTRICT,
    FOREIGN KEY (major_id) REFERENCES Major(id) 
        ON UPDATE CASCADE 
        ON DELETE SET NULL
) COMMENT '강의실';

-- -----------------------------------------------------
-- 6. MySchedule (예약 내역) 테이블 생성
-- -----------------------------------------------------
CREATE TABLE MySchedule (
    id INT PRIMARY KEY AUTO_INCREMENT COMMENT '고유값 (Primary Key)',
    user_id INT NOT NULL COMMENT '사용자 id (Foreign Key)',
    class_id INT NOT NULL COMMENT '강의실 id (Foreign Key)',
    rental_start_time DATETIME NOT NULL COMMENT '대여 시작 시간',
    rental_end_time DATETIME NOT NULL COMMENT '대여 종료 시간',
    create_time DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '생성 시간',
    delete_time DATETIME NULL COMMENT '삭제 시간',
    
    CHECK (rental_start_time < rental_end_time),
    
    FOREIGN KEY (user_id) REFERENCES User(id) 
        ON UPDATE CASCADE 
        ON DELETE RESTRICT,
    FOREIGN KEY (class_id) REFERENCES Class(id) 
        ON UPDATE CASCADE 
        ON DELETE RESTRICT
) COMMENT '예약 내역';