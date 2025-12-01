<?php
// --- Database Configuration ---

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "checkmyclass";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// --- Form Data Processing ---

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. 폼 데이터 가져오기 및 변수명 통일
    // HTML 폼의 name 속성과 일치해야 합니다.
    $user_name = mysqli_real_escape_string($conn, $_POST['name']);
    $student_number = mysqli_real_escape_string($conn, $_POST['student-id']);
    $raw_password = $_POST['password']; 
    $phone_number = mysqli_real_escape_string($conn, $_POST['phone']);
    $department = mysqli_real_escape_string($conn, $_POST['department']); // 학과는 현재 DB 스키마상 major_id(INT)이므로 추후 로직 수정 필요할 수 있음

    // 2. 필수 입력값 검증
    if (empty($user_name) || empty($student_number) || empty($raw_password) || empty($phone_number)) {
        echo "<script>alert('모든 필수 필드를 입력해주세요.'); window.history.back();</script>";
        exit();
    }
    
    // 3. 비밀번호 해싱 (보안)
    $user_password = password_hash($raw_password, PASSWORD_DEFAULT);

    // 4. 아이디(user_id) 설정 (✨ 중요: 학번을 아이디로 사용)
    $user_id = $student_number; 

    // 5. 역할(Role) 설정
    $role = 'STUDENT';

    // --- SQL INSERT Statement ---
    // users 테이블의 컬럼 순서와 개수를 맞춰줍니다.
    $sql = "INSERT INTO users (user_id, user_password, user_name, phone_number, student_number, role) VALUES (?, ?, ?, ?, ?, ?)";
    
    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    
    // 6. 데이터 바인딩 (ssssss : 문자열 6개)
    // 순서: user_id, user_password, user_name, phone_number, student_number, role
    $stmt->bind_param("ssssss", $user_id, $user_password, $user_name, $phone_number, $student_number, $role);
    
    // --- Execute and Provide Feedback ---
    try {
        if ($stmt->execute()) {
            echo "<script>
                    alert('회원가입이 성공적으로 완료되었습니다. 로그인 페이지로 이동합니다.');
                    window.location.href = 'login.php';
                    </script>";
        }
    } catch (mysqli_sql_exception $e) {
        // 중복된 아이디(학번) 체크
        if ($conn->errno == 1062) {
            echo "<script>
                    alert('오류: 이미 등록된 학번입니다.');
                    window.history.back();
                    </script>";
        } else {
            echo "<script>
                    alert('회원가입 중 오류가 발생했습니다: " . addslashes($e->getMessage()) . "');
                    window.history.back();
                    </script>";
        }
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();

} else {
    // If not a POST request, redirect to the registration page
    echo "<script>window.location.href = 'register.html';</script>";
    exit();
}
?>