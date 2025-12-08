<?php
session_start();

// 1. 관리자 권한 체크
if (!isset($_SESSION['is_logged_in']) || $_SESSION['role'] !== 'STAFF') {
    echo "<script>alert('권한이 없습니다.'); location.href='login.html';</script>";
    exit();
}

// 2. DB 연결
$conn = new mysqli("localhost", "root", "", "team002");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 3. 승인 처리 로직
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $reservation_id = $_POST['reservation_id'];
    $action = $_POST['action'];

    if ($action == 'approve') {
        // 상태를 '승인'으로 변경
        // (주의: DB 테이블에 '승인', '승인대기', '반려' 중 어떤 텍스트를 쓰는지 확인 필요. 여기서는 '승인'으로 업데이트)
        $sql = "UPDATE MySchedule SET status = '승인' WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $reservation_id);
        
        if ($stmt->execute()) {
            echo "<script>
                    alert('예약이 승인되었습니다.');
                    location.href = 'manager.php';
                  </script>";
        } else {
            echo "<script>
                    alert('오류 발생: " . $conn->error . "');
                    history.back();
                  </script>";
        }
        $stmt->close();
    }
} else {
    header("Location: manager.php");
    exit();
}

$conn->close();
?>