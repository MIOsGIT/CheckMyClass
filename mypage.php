<?php
session_start();

// 1. 로그인 체크
if (!isset($_SESSION['is_logged_in'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.html';</script>";
    exit();
}

// 2. 세션 정보 가져오기
$user_name = $_SESSION['user_name'];
$major_name = $_SESSION['department'];
$user_role = $_SESSION['role'] === 'STUDENT' ? '학생회원' : '교직원';
$user_db_id = $_SESSION['user_db_id'];

// 3. DB 연결
$conn = new mysqli("localhost", "root", "", "team002");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// 4. 예약 내역 조회 쿼리 (최신순 정렬)
$sql = "SELECT r.*, c.class_number 
        FROM Reservation r 
        JOIN Class c ON r.class_id = c.id 
        WHERE r.user_id = ? 
        ORDER BY r.reservation_date DESC, r.start_time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_db_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="ko">

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta charset="utf-8" />
  <title>CheckMyClass - 마이페이지</title>
  <link rel="stylesheet" href="globals.css" />
  <link rel="stylesheet" href="styleguide.css" />
  <link rel="stylesheet" href="style.css" />
</head>

<body>
  <main class="mypage">
    <header class="header">
      <div class="frame"> 
          <a href="main.php"><img class="checkmyclass-x" src="source/Logo_img.png" alt="CheckMyClass 로고 아이콘" /></a>
      </div>
      <img class="check-my-class" src="source/Logo_eng.png" alt="CheckMyClass" />
    </header>

    <section class="profile">
      <div class="div">
        <div class="view" role="img" aria-label="프로필 이미지">
            <img class="deu" src="source/Logo_dongeui.png" alt="Logo_dongeui" />
        </div>
        <p class="text-wrapper">
            <?php echo $major_name; ?><br />
            <?php echo $user_name; ?>
        </p>
      </div>
      <div class="div-wrapper"> <span class="text-wrapper-2"><?php echo $user_role; ?></span> </div>
    </section>

    <section class="view-2">
      <h1 class="text-wrapper-3">예약 내역</h1>
    </section>
    <hr class="line" />

    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <?php 
                $date_str = date('Y년 n월 j일', strtotime($row['reservation_date']));
                $time_str = substr($row['start_time'], 0, 5) . "~" . substr($row['end_time'], 0, 5);
                $status_text = "";
                $status_class = "";
                
                if ($row['status'] === 'WAITING') {
                    $status_text = "대기";
                    $status_class = "status-waiting";
                } else if ($row['status'] === 'APPROVED') {
                    $status_text = "승인됨";
                    $status_class = "status-approved";
                } else {
                    $status_text = "반려";
                    $status_class = "status-rejected"; 
                }
            ?>
            <article class="reservation" aria-label="예약 정보 - <?php echo $date_str; ?>">
              <dl class="frame-2">
                <div class="frame-3">
                  <dt class="text-wrapper-4">예약일</dt>
                  <dd class="input"> <span class="text-wrapper-5"><?php echo $date_str; ?></span> </dd>
                </div>
                <div class="frame-3">
                  <dt class="text-wrapper-4">예약시간</dt>
                  <dd class="input"> <span class="text-wrapper-5"><?php echo $time_str; ?></span> </dd>
                </div>
                <div class="frame-3">
                  <dt class="text-wrapper-4">강의실</dt>
                  <dd class="input"> <span class="text-wrapper-5"><?php echo $row['class_number']; ?>호</span> </dd>
                </div>
                <div class="frame-3">
                  <dt class="text-wrapper-4">대여 목적</dt>
                  <dd class="input-2"> <span class="text-wrapper-5"><?php echo htmlspecialchars($row['purpose']); ?></span> </dd>
                </div>
              </dl>
              <div class="<?php echo $status_class; ?>" role="status" aria-label="예약 상태: <?php echo $status_text; ?>"> 
                  <span class="<?php echo ($status_class === 'view-3') ? 'text-wrapper-6' : 'text-wrapper-7'; ?>">
                      <?php echo $status_text; ?>
                  </span> 
              </div>
            </article>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align:center; padding: 50px; color: #888;">예약 내역이 없습니다.</p>
    <?php endif; ?>

  </main>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>