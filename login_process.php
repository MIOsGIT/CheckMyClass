<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "team002";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $input_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $input_pw = $_POST['password'];

    if (empty($input_id) || empty($input_pw)) {
        echo "<script>alert('학번과 비밀번호를 모두 입력해주세요.'); history.back();</script>";
        exit();
    }

    // users 테이블과 Major 테이블 조인 조회
    $sql = "SELECT u.id, u.user_id, u.user_password, u.user_name, u.role, m.major_name 
        FROM users u
        LEFT JOIN Major m ON u.major_id = m.id
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
            
            // 세션 변수 저장
            $_SESSION['is_logged_in'] = true;
            $_SESSION['user_db_id'] = $row['id'];
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_name'] = $row['user_name'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['department'] = $row['major_name']; 

            if ($row['role'] === 'ADMIN') {
                echo "<script>
                        alert('관리자로 로그인되었습니다.');
                        location.href = 'manager.php'; 
                        </script>";
            } else {
                echo "<script>
                        alert('" . $row['user_name'] . "님 환영합니다!');
                        location.href = 'main.php'; 
                        </script>";
            }

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