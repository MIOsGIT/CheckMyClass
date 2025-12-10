<?php
session_start();

// 1. 로그인 확인
if (!isset($_SESSION['is_logged_in'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.html';</script>";
    exit();
}

// 2. DB 연결
$conn = new mysqli("localhost", "root", "", "team002");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// 3. 사용자 정보
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$department = $_SESSION['department'];
$role_text = ($_SESSION['role'] == 'STUDENT') ? '학생회원' : '교직원';

// 4. 예약 조회 (최신순)
$sql = "SELECT 
            m.rental_start_time, 
            m.rental_end_time, 
            m.purpose, 
            m.status,
            c.class_number
        FROM MySchedule m
        JOIN Class c ON m.class_id = c.id
        WHERE m.user_id = ?
        ORDER BY m.rental_start_time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta charset="utf-8" />
  <title>CheckMyClass - 예약 내역</title>
  <link rel="stylesheet" href="globals.css" />
  <link rel="stylesheet" href="styleguide.css" />
  <link rel="stylesheet" href="style.css" />
</head>

<body>
  <main class="mypage">
    
    <header class="header">
      <div class="frame"> 
        <img class="checkmyclass-x" src="Logo_img.png" alt="Logo" /> 
      </div>
      <img class="check-my-class" src="Logo_eng.png" alt="CheckMyClass" />
    </header>

    <section class="profile">
      <div class="div">
        <div class="view">
             <img src="Logo_dongeui.png" alt="학교로고">
        </div>
        <div class="text-wrapper">
            동의대학교<br />
            <?php echo htmlspecialchars($department) . " " . htmlspecialchars($user_name); ?>
        </div>
      </div>
      <div class="div-wrapper"> 
          <?php echo $role_text; ?>
      </div>
    </section>

    <section class="view-2">
      <div class="text-wrapper-3">예약 내역</div>
    </section>

    <hr class="line" />

    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <?php
                // 날짜/시간 포맷
                $start_dt = new DateTime($row['rental_start_time']);
                $end_dt = new DateTime($row['rental_end_time']);
                
                $date_str = $start_dt->format('Y년 n월 j일'); 
                $time_str = $start_dt->format('H:i') . "~" . $end_dt->format('H:i');
                
                // 상태별 클래스 및 텍스트 설정
                $status_class = 'status-waiting';
                $status_text = '대기';

                if ($row['status'] == '반려') {
                    $status_class = 'status-rejected';
                    $status_text = '반려';
                } elseif ($row['status'] == '승인') {
                    $status_class = 'status-approved';
                    $status_text = '승인됨';
                }
            ?>

            <article class="reservation">
              <dl class="frame-2">
                <div class="frame-3">
                  <dt class="text-wrapper-4">예약일</dt>
                  <dd class="text-wrapper-5"><?php echo $date_str; ?></dd>
                </div>
                <div class="frame-3">
                  <dt class="text-wrapper-4">예약시간</dt>
                  <dd class="text-wrapper-5"><?php echo $time_str; ?></dd>
                </div>
                <div class="frame-3">
                  <dt class="text-wrapper-4">강의실</dt>
                  <dd class="text-wrapper-5"><?php echo htmlspecialchars($row['class_number']); ?></dd>
                </div>
                <div class="frame-3">
                  <dt class="text-wrapper-4">대여 목적</dt>
                  <dd class="purpose-box"><?php echo htmlspecialchars($row['purpose']); ?></dd>
                </div>
              </dl>
              
              <div class="status-badge <?php echo $status_class; ?>">
                  <?php echo $status_text; ?>
              </div>
            </article>

        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align:center; padding:40px; color:#888;">예약 내역이 없습니다.</div>
    <?php endif; ?>

  </main>
</body>
</html>