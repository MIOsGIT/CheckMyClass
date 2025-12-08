<?php
session_start();

// 1. 관리자(STAFF) 권한 확인
if (!isset($_SESSION['is_logged_in']) || $_SESSION['role'] !== 'STAFF') {
    echo "<script>alert('관리자 권한이 필요합니다.'); location.href='main.php';</script>";
    exit();
}

// 2. DB 연결
$conn = new mysqli("localhost", "root", "", "team002");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 3. 예약 목록 조회
$sql = "SELECT 
            m.id as reservation_id,
            m.rental_start_time,
            m.rental_end_time,
            m.purpose,
            u.user_name,
            u.student_number,
            mj.major_name,
            c.class_number
        FROM MySchedule m
        JOIN users u ON m.user_id = u.user_id
        JOIN Major mj ON u.major_id = mj.id
        JOIN Class c ON m.class_id = c.id
        WHERE m.status = '승인대기'
        ORDER BY m.create_time ASC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <title>CheckMyClass - 관리자</title>
    <link rel="stylesheet" href="globals.css" />
    <link rel="stylesheet" href="styleguide.css" />
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <div class="manager">
        <header class="header">
            <div class="frame"><img class="checkmyclass-x" src="Logo_img.png" alt="Logo" /></div>
            <img class="check-my-class" src="Logo_eng.png" alt="CheckMyClass" />
        </header>

        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <?php
                    $start_dt = new DateTime($row['rental_start_time']);
                    $end_dt = new DateTime($row['rental_end_time']);
                    $date_str = $start_dt->format('Y년 n월 j일');
                    $time_str = $start_dt->format('H:i') . "~" . $end_dt->format('H:i');
                ?>
                <div class="reservation">
                    
                    <div class="info-container">
                        
                        <div class="user-info">
                            <span class="user-label">예약자</span>
                            <div class="user-value">
                                <?php echo htmlspecialchars($row['major_name']); ?><br />
                                <?php echo htmlspecialchars($row['student_number']) . " " . htmlspecialchars($row['user_name']); ?>
                            </div>
                        </div>

                        <div class="info-row">
                            <span class="label">예약일</span>
                            <span class="value"><?php echo $date_str; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">예약시간</span>
                            <span class="value"><?php echo $time_str; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">강의실</span>
                            <span class="value"><?php echo htmlspecialchars($row['class_number']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">대여 목적</span>
                            <span class="purpose-box"><?php echo htmlspecialchars($row['purpose']); ?></span>
                        </div>
                    </div>

                    <form action="manager_process.php" method="POST">
                        <input type="hidden" name="reservation_id" value="<?php echo $row['reservation_id']; ?>">
                        <input type="hidden" name="action" value="approve">
                        
                        <button type="submit" class="approve-btn">
                            <div class="btn-view"><div class="btn-text">승인</div></div>
                        </button>
                    </form>

                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="padding: 40px; text-align: center; color: #888;">
                승인 대기 중인 예약이 없습니다.
            </div>
        <?php endif; ?>

        <div class="graph-box"><div class="graph-text">강의실 별 예약현황 그래프</div></div>
        <div class="graph-box"><div class="graph-text">인기 강의실 그래프</div></div>
    </div>
</body>
</html>
<?php
$conn->close();
?>