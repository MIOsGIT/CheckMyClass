<?php
session_start();

// 1. 로그인 확인
if (!isset($_SESSION['is_logged_in'])) {
    echo "<script>alert('로그인 후 이용 가능합니다.'); location.href='login.html';</script>";
    exit();
}

// 2. DB 연결
$conn = new mysqli("localhost", "root", "", "team02");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 3. POST 요청 처리
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 데이터 수신
    $class_id = $_POST['class_id'];
    $date = $_POST['date'];
    $start_time_raw = $_POST['start_time'];
    $end_time_raw = $_POST['end_time'];
    
    // [추가] 사용 목적 받기
    $purpose = mysqli_real_escape_string($conn, $_POST['purpose']); 

    $user_id = $_SESSION['user_id']; 

    // DATETIME 형식 변환
    $rental_start_time = $date . ' ' . $start_time_raw . ':00';
    $rental_end_time = $date . ' ' . $end_time_raw . ':00';

    // 시간 유효성 검사
    if (strtotime($rental_start_time) >= strtotime($rental_end_time)) {
        echo "<script>alert('종료 시간은 시작 시간보다 늦어야 합니다.'); history.back();</script>";
        exit();
    }
    
    // 사용 목적 입력 확인
    if (empty($purpose)) {
        echo "<script>alert('대여 목적을 입력해주세요.'); history.back();</script>";
        exit();
    }

    // 중복 예약 체크
    $check_sql = "SELECT * FROM MySchedule 
                  WHERE class_id = ? 
                  AND rental_start_time < ? 
                  AND rental_end_time > ?
                  AND status != '반려'"; 
    
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("iss", $class_id, $rental_end_time, $rental_start_time);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        echo "<script>alert('해당 시간에는 이미 예약이 존재합니다.'); history.back();</script>";
    } else {
        // [수정] 예약 정보 저장 (purpose 추가)
        $status = '승인대기';
        $insert_sql = "INSERT INTO MySchedule (user_id, class_id, rental_start_time, rental_end_time, purpose, status) VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt_insert = $conn->prepare($insert_sql);
        // 바인딩 타입: s가 6개 (user_id, rental_start, rental_end, purpose, status) + i 1개 (class_id) -> 순서 맞춰서 sissss
        $stmt_insert->bind_param("sissss", $user_id, $class_id, $rental_start_time, $rental_end_time, $purpose, $status);

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
    header("Location: reservation.php");
    exit();
}
?>