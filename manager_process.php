<?php
session_start();

// 1. 관리자 권한 확인
if (!isset($_SESSION['is_logged_in']) || ($_SESSION['role'] !== 'ADMIN')) {
    echo "<script>alert('권한이 없습니다.'); location.href='main.php';</script>";
    exit();
}

// 2. DB 연결
$conn = new mysqli("localhost", "root", "", "team002");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 3. POST 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservation_id = $_POST['reservation_id'];
    $action = $_POST['action'];

    if (empty($reservation_id) || empty($action)) {
        echo "<script>alert('잘못된 접근입니다.'); history.back();</script>";
        exit();
    }

    $status = '';
    $message = '';

    // 액션에 따라 상태값 결정
    if ($action === 'approve') {
        $status = 'APPROVED';
        $message = '예약이 승인되었습니다.';
    } elseif ($action === 'reject') {
        $status = 'REJECTED';
        $message = '예약이 반려되었습니다.';
    } else {
        echo "<script>alert('알 수 없는 명령입니다.'); history.back();</script>";
        exit();
    }

    $sql = "UPDATE Reservation SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $reservation_id);

    if ($stmt->execute()) {
        echo "<script>
                alert('$message');
                location.href = 'manager.php';
              </script>";
    } else {
        echo "<script>alert('처리 중 오류 발생: " . $conn->error . "'); history.back();</script>";
    }

    $stmt->close();
} else {
    // POST가 아니면 목록으로 리다이렉트
    header("Location: manager.php");
    exit();
}

$conn->close();
?>