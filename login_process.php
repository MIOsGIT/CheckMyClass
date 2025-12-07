<?php
// 세션 시작
session_start();

// --- Database Configuration ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "team02";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Login Process ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $input_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $input_pw = $_POST['password'];

    if (empty($input_id) || empty($input_pw)) {
        echo "<script>alert('학번과 비밀번호를 모두 입력해주세요.'); history.back();</script>";
        exit();
    }

    // [수정된 부분] 
    // users 테이블에는 학과 ID만 있으므로, Major 테이블과 JOIN하여 '학과 이름(major_name)'을 가져옵니다.
    $sql = "SELECT u.id, u.user_id, u.user_password, u.user_name, u.role, m.major_name 
            FROM users u
            JOIN Major m ON u.major_id = m.id
            WHERE u.user_id = ?";
            
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
            $_SESSION['user_db_id'] = $row['id'];
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_name'] = $row['user_name'];
            $_SESSION['role'] = $row['role'];
            
            // [중요] DB에서 가져온 'major_name'을 'department' 세션에 저장
            // 이렇게 하면 main.php를 수정하지 않아도 학과 이름이 잘 뜹니다.
            $_SESSION['department'] = $row['major_name']; 

            echo "<script>
                    alert('" . $row['user_name'] . "님 환영합니다!');
                    location.href = 'main.php'; 
                  </script>";
        } else {
            echo "<script>alert('비밀번호가 일치하지 않습니다.'); history.back();</script>";
        }
    } else {
        echo "<script>alert('존재하지 않는 학번입니다.'); history.back();</script>";
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: login.html");
    exit();
}
?>