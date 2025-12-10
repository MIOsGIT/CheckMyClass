<?php
error_reporting(0);
ini_set('display_errors', 0);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "team002";

// 데이터베이스 연결
$conn = new mysqli($servername, $username, $password, $dbname);

// 연결 오류 발생 시 스크립트 중단 및 알림
if ($conn->connect_error) {
    echo "<script>alert('서버 연결에 실패했습니다. 관리자에게 문의하세요.'); history.back();</script>";
    exit();
}
mysqli_set_charset($conn, "utf8mb4");

// POST 데이터 처리
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. 데이터 가져오기 및 공백 제거
    $user_name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $student_number = isset($_POST['student-id']) ? trim($_POST['student-id']) : '';
    $raw_password = isset($_POST['password']) ? $_POST['password'] : '';
    $phone_number = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $department_name = isset($_POST['department']) ? trim($_POST['department']) : '';

    // 2. 필수 입력값 검증
    if (empty($user_name) || empty($student_number) || empty($raw_password) || empty($phone_number) || empty($department_name)) {
        echo "<script>alert('모든 필수 항목을 입력해주세요.'); history.back();</script>";
        exit();
    }

    // 3. 학과 ID 조회 (입력한 학과명으로 ID 찾기)
    $major_id = null;
    $sql_major = "SELECT id FROM Major WHERE major_name = ?";
    
    if ($stmt_major = $conn->prepare($sql_major)) {
        $stmt_major->bind_param("s", $department_name);
        $stmt_major->execute();
        $result_major = $stmt_major->get_result();

        if ($result_major->num_rows > 0) {
            $row_major = $result_major->fetch_assoc();
            $major_id = $row_major['id'];
        } else {
            // DB에 없는 학과명일 경우
            echo "<script>
                    alert('등록되지 않은 학과입니다.\\n학과명을 정확히 입력했는지 확인해주세요. (예: 응용소프트웨어공학과)');
                    history.back();
                    </script>";
            $stmt_major->close();
            $conn->close();
            exit();
        }
        $stmt_major->close();
    } else {
        echo "<script>alert('시스템 오류(학과 조회 실패). 관리자에게 문의하세요.'); history.back();</script>";
        $conn->close();
        exit();
    }

    // 4. 비밀번호 암호화 및 데이터 준비
    $user_password = password_hash($raw_password, PASSWORD_DEFAULT);
    $user_id = $student_number; // 학번을 아이디로 사용

    $role = (isset($_POST['is-PROFESSOR']) && $_POST['is-PROFESSOR'] == 'PROFESSOR') ? 'PROFESSOR' : 'STUDENT';

    // 5. 회원 정보 DB 저장 (INSERT)
    $sql = "INSERT INTO users (user_id, user_password, user_name, phone_number, student_number, role, major_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        // s:문자열, i:정수 (순서: user_id, pw, name, phone, std_num, role, major_id)
        $stmt->bind_param("ssssssi", $user_id, $user_password, $user_name, $phone_number, $student_number, $role, $major_id);
        
        try {
            if ($stmt->execute()) {
                // 성공 시 로그인 페이지로 이동
                echo "<script>
                        alert('회원가입이 완료되었습니다!\\n로그인 페이지로 이동합니다.');
                        location.href = 'login.html';
                        </script>";
            } else {
                throw new Exception($stmt->error);
            }
        } catch (Exception $e) {
            // 중복된 학번(ID)인 경우
            if ($conn->errno == 1062) {
                echo "<script>alert('이미 가입된 학번(아이디)입니다.'); history.back();</script>";
            } else {
                    $error_msg = addslashes($e->getMessage());
                    echo "<script>alert('오류 상세: $error_msg'); history.back();</script>";
                }
        }
        $stmt->close();
    } else {
        echo "<script>alert('시스템 오류(쿼리 준비 실패). 관리자에게 문의하세요.'); history.back();</script>";
    }

    $conn->close();

} else {
    // POST 요청이 아닐 경우 회원가입 페이지로 리다이렉트
    echo "<script>location.href = 'register.html';</script>";
    exit();
}
?>