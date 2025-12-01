<?php
// 세션 시작 (로그인 유지에 필수)
session_start();

// --- Database Configuration ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "checkmyclass";

// 데이터베이스 연결 생성
$conn = new mysqli($servername, $username, $password, $dbname);

// 연결 확인
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Login Process ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. 입력 데이터 가져오기
    $input_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $input_pw = $_POST['password'];

    // 2. 필수 입력값 검증
    if (empty($input_id) || empty($input_pw)) {
        echo "<script>alert('학번과 비밀번호를 모두 입력해주세요.'); history.back();</script>";
        exit();
    }

    // 3. DB에서 사용자 정보 조회 (학번 기준)
    $sql = "SELECT id, user_id, user_password, user_name, role FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        die("Query Prepare Failed: " . $conn->error);
    }

    $stmt->bind_param("s", $input_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $hashed_password = $row['user_password'];

        if (password_verify($input_pw, $hashed_password)) {
            
            // 세션 변수에 사용자 정보 저장
            $_SESSION['is_logged_in'] = true;
            $_SESSION['user_db_id'] = $row['id'];      // DB의 고유 ID (PK)
            $_SESSION['user_id'] = $row['user_id'];    // 학번 (로그인 ID)
            $_SESSION['user_name'] = $row['user_name']; // 이름
            $_SESSION['role'] = $row['role'];          // 역할 (STUDENT, PROFESSOR 등)

            // 메인 페이지(대시보드)로 이동
            echo "<script>
                    alert('" . $row['user_name'] . "님 환영합니다!');
                    location.href = 'main.html'; 
                    </script>";
        } else {
            // 비밀번호 불일치
            echo "<script>alert('비밀번호가 일치하지 않습니다.'); history.back();</script>";
        }
    } else {
        // 아이디(학번)가 없음
        echo "<script>alert('존재하지 않는 학번입니다.'); history.back();</script>";
    }

    $stmt->close();
    $conn->close();

} else {
    // POST 접근이 아닐 경우 로그인 페이지로 이동
    header("Location: login.html");
    exit();
}
?>