<?php
// --- Database Configuration ---
// IMPORTANT: Replace with your actual database credentials.
$servername = "localhost"; // or your db host
$username = "root";        // your db username
$password = "";            // your db password
$dbname = "your_database_name"; // your db name

// --- Table Structure (for user reference) ---
// You need to create this table in your database.
//
// CREATE TABLE users (
//     id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//     name VARCHAR(30) NOT NULL,
//     student_id VARCHAR(30) NOT NULL UNIQUE,
//     password VARCHAR(255) NOT NULL,
//     phone VARCHAR(20),
//     department VARCHAR(50),
//     reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
// );

// --- Form Data Processing ---

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Create database connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get and sanitize form data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $student_id = mysqli_real_escape_string($conn, $_POST['student-id']);
    $pass = $_POST['password']; // Get password before hashing
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);

    // --- Input Validation ---
    if (empty($name) || empty($student_id) || empty($pass) || empty($phone) || empty($department)) {
        echo "<script>alert('모든 필드를 입력해주세요.'); window.history.back();</script>";
        exit();
    }
    
    // Hash the password for security
    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

    // --- SQL INSERT Statement ---
    $sql = "INSERT INTO users (name, student_id, password, phone, department) VALUES (?, ?, ?, ?, ?)";

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("sssss", $name, $student_id, $hashed_password, $phone, $department);

    // --- Execute and Provide Feedback ---
    if ($stmt->execute()) {
        echo "<script>
                alert('회원가입이 성공적으로 완료되었습니다. 로그인 페이지로 이동합니다.');
                window.location.href = 'login.php';
              </script>";
    } else {
        // Check for duplicate entry
        if ($conn->errno == 1062) {
            echo "<script>
                    alert('오류: 이미 등록된 학번입니다.');
                    window.history.back();
                  </script>";
        } else {
            echo "<script>
                    alert('회원가입 중 오류가 발생했습니다: " . addslashes($stmt->error) . "');
                    window.history.back();
                  </script>";
        }
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();

} else {
    // If not a POST request, redirect to the registration page
    header("Location: register.html");
    exit();
}
?>
