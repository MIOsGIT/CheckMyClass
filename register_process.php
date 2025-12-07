<?php
// --- Database Configuration ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "team02";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Form Data Processing ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. 데이터 수신
    $user_name = mysqli_real_escape_string($conn, $_POST['name']);
    $student_number = mysqli_real_escape_string($conn, $_POST['student-id']);
    $raw_password = $_POST['password']; 
    $phone_number = mysqli_real_escape_string($conn, $_POST['phone']);
    $department_name = mysqli_real_escape_string($conn, $_POST['department']); // 사용자가 입력한 "학과 이름"

    // 2. 필수값 검증
    if (empty($user_name) || empty($student_number) || empty($raw_password) || empty($phone_number) || empty($department_name)) {
        echo "<script>alert('모든 필수 필드를 입력해주세요.'); window.history.back();</script>";
        exit();
    }
    
    // 3. [추가된 로직] 학과 이름으로 ID 찾기
    // 사용자가 입력한 '응용소프트웨어공학과'가 Major 테이블에 있는지 확인하고 ID를 가져옵니다.
    $sql_major = "SELECT id FROM Major WHERE major_name = ?";
    $stmt_major = $conn->prepare($sql_major);
    $stmt_major->bind_param("s", $department_name);
    $stmt_major->execute();
    $result_major = $stmt_major->get_result();

    if ($result_major->num_rows > 0) {
        $row_major = $result_major->fetch_assoc();
        $major_id = $row_major['id']; // 찾은 학과 ID (예: 1)
    } else {
        // DB에 없는 학과를 입력했을 경우
        echo "<script>alert('존재하지 않는 학과입니다. 학과명을 정확히 입력해주세요. (예: 응용소프트웨어공학과)'); window.history.back();</script>";
        exit();
    }
    $stmt_major->close();

    // 4. 비밀번호 암호화
    $user_password = password_hash($raw_password, PASSWORD_DEFAULT);
    $user_id = $student_number; 

    // 5. 역할 설정
    if (isset($_POST['is-staff']) && $_POST['is-staff'] == 'staff') {
        $role = 'STAFF';
    } else {
        $role = 'STUDENT';
    }

    // 6. [수정됨] INSERT 쿼리 (department -> major_id)
    // 이제 users 테이블에는 학과 이름 대신 'major_id' 숫자가 들어갑니다.
    $sql = "INSERT INTO users (user_id, user_password, user_name, phone_number, student_number, role, major_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    // 데이터 바인딩 (s:문자열, i:정수 -> major_id는 정수이므로 마지막에 'i')
    $stmt->bind_param("ssssssi", $user_id, $user_password, $user_name, $phone_number, $student_number, $role, $major_id);
    
    // 실행 및 예외처리
    try {
        if ($stmt->execute()) {
            echo "<script>
                    alert('회원가입 완료! 로그인 페이지로 이동합니다.');
                    window.location.href = 'login.html';
                    </script>";
        }
    } catch (mysqli_sql_exception $e) {
        if ($conn->errno == 1062) {
            echo "<script>alert('오류: 이미 등록된 학번입니다.'); window.history.back();</script>";
        } else {
            echo "<script>alert('가입 오류: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        }
    }

    $stmt->close();
    $conn->close();

} else {
    echo "<script>window.location.href = 'register.html';</script>";
    exit();
}
?>