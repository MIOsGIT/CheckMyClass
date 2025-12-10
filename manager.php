<?php
session_start();

// 1. 관리자(ADMIN) 권한 확인
if (!isset($_SESSION['is_logged_in']) || ($_SESSION['role'] !== 'ADMIN' && $_SESSION['role'] !== 'STAFF')) {
    echo "<script>alert('관리자 권한이 필요합니다.'); location.href='main.php';</script>";
    exit();
}

// 2. DB 연결
$conn = new mysqli("localhost", "root", "", "team002");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 3. 승인 대기 목록 조회 (Waiting)
$sql_waiting = "SELECT 
            r.id as reservation_id,
            r.reservation_date,
            r.start_time,
            r.end_time,
            r.purpose,
            u.user_name,
            u.student_number,
            mj.major_name,
            c.class_number
        FROM Reservation r
        JOIN users u ON r.user_id = u.id
        LEFT JOIN Major mj ON u.major_id = mj.id
        JOIN Class c ON r.class_id = c.id
        WHERE r.status = 'WAITING'
        ORDER BY r.create_time ASC";
$result_waiting = $conn->query($sql_waiting);

// 4. 통계 1: 인기 강의실 그래프 데이터 (강의실별 예약 횟수)
// 상태가 REJECTED(반려)가 아닌 모든 예약을 카운트
$sql_graph = "SELECT c.class_number, COUNT(*) as cnt 
                FROM Reservation r 
                JOIN Class c ON r.class_id = c.id 
                WHERE r.status != 'REJECTED'
                GROUP BY c.class_number 
                ORDER BY cnt DESC";
$result_graph = $conn->query($sql_graph);
$graph_data = [];
while($row = $result_graph->fetch_assoc()) {
    $graph_data[] = $row;
}

// 5. [추가] 통계 2: 전체 예약 현황 표 데이터 (최근 예약 순)
$sql_table = "SELECT c.class_number, r.reservation_date, r.start_time, r.end_time, u.user_name, r.status
                FROM Reservation r
                JOIN Class c ON r.class_id = c.id
                JOIN users u ON r.user_id = u.id
                ORDER BY r.reservation_date DESC, r.start_time DESC
                LIMIT 50";    
$result_table = $conn->query($sql_table);
$table_data = [];
while($row = $result_table->fetch_assoc()) {
    $table_data[] = $row;
}
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
    <style>
        .btn-container {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            justify-content: flex-end;
        }
        .action-form {
            display: inline-block;
            width: 48%;
        }
        
        .stats-section {
            width: 350px;
            margin: 20px 0;
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border: 1px solid #ddd;
        }
        .stats-title {
            font-family: "Pretendard", sans-serif;
            font-weight: 800;
            font-size: 18px;
            color: var(--main);
            margin-bottom: 15px;
            text-align: center;
        }
        #chart_div, #table_div {
            width: 100%;
            overflow: hidden;
        }
    </style>

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {'packages':['bar', 'table']}); // bar와 table 패키지 로드
        google.charts.setOnLoadCallback(drawDashboard);

        function drawDashboard() {
            drawGraph();
            drawTable();
        }

        // 1. 인기 강의실 그래프 그리기
        function drawGraph() {
            var data = google.visualization.arrayToDataTable([
                ['강의실', '예약 횟수'],
                <?php
                    foreach ($graph_data as $g) {
                        echo "['" . $g['class_number'] . "호', " . $g['cnt'] . "],";
                    }
                ?>
            ]);

            var options = {
                chart: {
                    title: '강의실 예약 통계',
                    subtitle: '가장 많이 예약된 강의실 순위',
                },
                bars: 'horizontal',
                legend: { position: 'none' },
                colors: ['#203b55'],
                height: 300
            };

            var chart = new google.charts.Bar(document.getElementById('chart_div'));
            chart.draw(data, google.charts.Bar.convertOptions(options));
        }

        // 2. 전체 예약 현황 표 그리기
        function drawTable() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', '강의실');
            data.addColumn('string', '날짜');
            data.addColumn('string', '시간');
            data.addColumn('string', '예약자');
            data.addColumn('string', '상태');

            data.addRows([
                <?php
                    foreach ($table_data as $t) {
                        $time = substr($t['start_time'], 0, 5) . "~" . substr($t['end_time'], 0, 5);
                        $status_kor = $t['status'];
                        if($t['status'] == 'WAITING') $status_kor = '대기';
                        else if($t['status'] == 'APPROVED') $status_kor = '승인';
                        else if($t['status'] == 'REJECTED') $status_kor = '반려';

                        echo "['{$t['class_number']}호', '{$t['reservation_date']}', '{$time}', '{$t['user_name']}', '{$status_kor}'],";
                    }
                ?>
            ]);

            var table = new google.visualization.Table(document.getElementById('table_div'));
            
            var cssClassNames = {
                'headerRow': 'google-header-row',
                'tableRow': 'google-table-row',
                'oddTableRow': 'google-odd-row',
                'selectedTableRow': 'google-selected-row',
                'hoverTableRow': 'google-hover-row',
                'headerCell': 'google-header-cell',
                'tableCell': 'google-table-cell',
                'rowNumberCell': 'google-row-number-cell'
            };

            table.draw(data, {
                showRowNumber: false, 
                width: '100%', 
                height: '100%',
                page: 'enable',
                pageSize: 10,
                cssClassNames: cssClassNames
            });
        }
    </script>
</head>
<body>
    <div class="manager">
        <header class="header">
            <div class="frame">
                    <a href="main.php"><img class="checkmyclass-x" src="source/Logo_img.png" alt="Logo" /></a>
            </div>
            <img class="check-my-class" src="source/Logo_eng.png" alt="CheckMyClass" />
        </header>

        <h2 style="padding: 20px 0 0 20px; color: #333; align-self: flex-start; margin-left: 20px;">승인 대기 목록</h2>

        <?php if ($result_waiting->num_rows > 0): ?>
            <?php while($row = $result_waiting->fetch_assoc()): ?>
                <?php
                    $date_str = date('Y년 n월 j일', strtotime($row['reservation_date']));
                    $time_str = substr($row['start_time'], 0, 5) . " ~ " . substr($row['end_time'], 0, 5);
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
                            <span class="value"><?php echo htmlspecialchars($row['class_number']); ?>호</span>
                        </div>
                        <div class="info-row">
                            <span class="label" style="display:block; margin-bottom:5px;">대여 목적</span>
                            <div class="purpose-box">
                                <?php echo htmlspecialchars($row['purpose']); ?>
                            </div>
                        </div>
                    </div>

                    <div class="btn-container">
                        <form action="manager_process.php" method="POST" class="action-form">
                            <input type="hidden" name="reservation_id" value="<?php echo $row['reservation_id']; ?>">
                            <input type="hidden" name="action" value="approve"> 
                            <button type="submit" class="approve-btn" onclick="return confirm('승인하시겠습니까?');">승인</button>
                        </form>

                        <form action="manager_process.php" method="POST" class="action-form">
                            <input type="hidden" name="reservation_id" value="<?php echo $row['reservation_id']; ?>">
                            <input type="hidden" name="action" value="reject"> 
                            <button type="submit" class="reject-btn" onclick="return confirm('반려하시겠습니까?');">반려</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="padding: 40px; text-align: center; color: #888; width: 100%;">
                현재 승인 대기 중인 예약 요청이 없습니다.
            </div>
        <?php endif; ?>
        
        <div class="stats-section">
            <div class="stats-title">최신 예약 현황 (전체)</div>
            <div id="table_div"></div>
        </div>
        <div class="stats-section">
            <div class="stats-title">인기 강의실 TOP 현황</div>
            <div id="chart_div"></div>
        </div>
        
    </div>
</body>
</html>
<?php
$conn->close();
?>