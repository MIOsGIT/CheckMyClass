<?php
session_start();

// 1. 로그인 확인
if (!isset($_SESSION['is_logged_in'])) {
    echo "<script>alert('로그인 후 이용 가능합니다.'); location.href='login.html';</script>";
    exit();
}

// 2. DB 연결 (DB명 team002로 수정)
$conn = new mysqli("localhost", "root", "", "team002");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 3. POST 요청 처리
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 데이터 수신
    $class_id = $_POST['class_id'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $purpose = trim($_POST['purpose']);

    $user_id = $_SESSION['user_db_id']; 

    // 시간 유효성 검사
    if ($start_time >= $end_time) {
        echo "<script>alert('종료 시간은 시작 시간보다 늦어야 합니다.'); history.back();</script>";
        exit();
    }
    
    // 사용 목적 입력 확인
    if (empty($purpose)) {
        echo "<script>alert('대여 목적을 입력해주세요.'); history.back();</script>";
        exit();
    }

    // 4. 중복 예약 체크 (Reservation 테이블 기준)
    $check_sql = "SELECT id FROM Reservation 
                    WHERE class_id = ? 
                    AND reservation_date = ? 
                    AND status != 'REJECTED'
                    AND (
                        (start_time < ? AND end_time > ?) OR
                        (start_time >= ? AND start_time < ?)
                    )";
    
    // 시간 비교 로직:
    // 1. 기존 예약이 내 시작시간보다 늦게 끝나고, 내 종료시간보다 빨리 시작함 (겹침)
    
    // 간단한 중복 체크 쿼리 (새로 예약하려는 시간대와 겹치는게 있는지 확인)
    $check_sql = "SELECT id FROM Reservation 
                    WHERE class_id = ? 
                    AND reservation_date = ? 
                    AND status != 'REJECTED'
                    AND NOT (end_time <= ? OR start_time >= ?)";

    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("isss", $class_id, $date, $start_time, $end_time);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        echo "<script>alert('해당 시간에는 이미 다른 예약이 존재합니다.'); history.back();</script>";
    } else {
        // 5. 예약 정보 저장 (Reservation 테이블 사용)
        $sql = "INSERT INTO Reservation (user_id, class_id, reservation_date, start_time, end_time, purpose, status, create_time) 
                VALUES (?, ?, ?, ?, ?, ?, 'WAITING', NOW())";
        
        $stmt_insert = $conn->prepare($sql);
        // user_id(int), class_id(int), date(string), start(string), end(string), purpose(string) -> iissss
        $stmt_insert->bind_param("iissss", $user_id, $class_id, $date, $start_time, $end_time, $purpose);

        if ($stmt_insert->execute()) {
            echo "<script>
                    alert('예약 신청이 완료되었습니다.');
                    location.href = 'mypage.php'; 
                  </script>";
        } else {
            echo "<script>alert('오류 발생: " . $conn->error . "'); history.back();</script>";
        }
        $stmt_insert->close();
    }
    $stmt_check->close();
    $conn->close();

} else {
    // POST가 아니면 예약 페이지로 돌려보냄
    echo "<script>location.href = 'reservation.php';</script>";
    exit();
}
?>