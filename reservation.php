<?php
session_start();

// 1. 로그인 체크
if (!isset($_SESSION['is_logged_in'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.html';</script>";
    exit();
}

// 2. DB 연결
$conn = new mysqli("localhost", "root", "", "team02");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// 3. 파라미터 받기
$date = $_GET['date'] ?? date('Y-m-d');
$start_time = $_GET['start_time'] ?? '17:00';
$end_time = $_GET['end_time'] ?? '18:00';
$filters = $_GET['filter'] ?? [];

// 4. 날짜 포맷팅
$days = ['일', '월', '화', '수', '목', '금', '토'];
$day_kor = $days[date('w', strtotime($date))];
$date_display = date('Y년 n월 j일', strtotime($date)) . " (" . $day_kor . ")";

// 5. 필터 조건 생성
$where_clause = "WHERE 1=1";
if (in_array('pc', $filters)) $where_clause .= " AND has_pc = 'Y'";
if (in_array('projector', $filters)) $where_clause .= " AND has_projector = 'Y'";
if (in_array('board', $filters)) $where_clause .= " AND board_type = '전자칠판'";
if (in_array('mic', $filters)) $where_clause .= " AND has_mic = 'Y'";

// 6. 강의실 조회 쿼리
$sql = "SELECT c.*, b.building_name 
        FROM Class c 
        JOIN Building b ON c.building_id = b.id 
        $where_clause";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <title>CheckMyClass - 예약 신청</title>
    <link rel="stylesheet" href="globals.css" />
    <link rel="stylesheet" href="styleguide.css" />
    <link rel="stylesheet" href="style.css" />

    <style>
        /* --- 모달 및 추가 스타일 --- */
        .modal {
            display: none; 
            position: fixed; z-index: 1000; left: 0; top: 0;
            width: 100%; height: 100%; overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; padding: 20px;
            border: 1px solid #888; width: 80%; max-width: 400px;
            border-radius: 8px; position: relative;
        }
        .close-btn {
            color: #aaa; float: right; font-size: 28px; font-weight: bold;
            position: absolute; top: 10px; right: 20px; cursor: pointer;
        }
        .close-btn:hover { color: #000; }
        
        /* 기능 작동을 위한 투명 스타일 */
        .hidden-input { display: none; }
        .transparent-trigger {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            opacity: 0; cursor: pointer; z-index: 10;
        }
        .filter-label { cursor: pointer; display: contents; }
        
        /* 모달 내부 텍스트 정렬 */
        .modal-row { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 16px; }
        .modal-label { font-weight: bold; color: #333; }
        .modal-value { font-weight: normal; color: #555; }
        .confirm-btn {
            width: 100%; padding: 12px; background-color: var(--main);
            color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;
            margin-top: 10px; font-size: 16px;
        }
    </style>
</head>
<body>
    <form id="searchForm" action="reservation.php" method="GET">
        <main class="reservation">
            <header class="header">
                <div class="frame">
                    <img class="checkmyclass-x" src="Logo_img.png" alt="Logo" />
                </div>
                <img class="check-my-class" src="Logo_eng.png" alt="CheckMyClass" />
            </header>
            
            <section class="view" aria-label="페이지 제목">
                <h1 class="text-wrapper">예약 신청</h1>
            </section>

            <section class="div" role="group" aria-label="장비 필터">
                <label class="filter-label">
                    <input type="checkbox" name="filter[]" value="pc" class="hidden-input" <?php echo in_array('pc', $filters) ? 'checked' : ''; ?> onchange="this.form.submit()">
                    <div class="<?php echo in_array('pc', $filters) ? 'div-wrapper' : 'frame-2'; ?>">
                        <span class="<?php echo in_array('pc', $filters) ? 'text-wrapper-2' : 'text-wrapper-3'; ?>">PC</span>
                    </div>
                </label>
                <label class="filter-label">
                    <input type="checkbox" name="filter[]" value="projector" class="hidden-input" <?php echo in_array('projector', $filters) ? 'checked' : ''; ?> onchange="this.form.submit()">
                    <div class="<?php echo in_array('projector', $filters) ? 'div-wrapper' : 'frame-2'; ?>">
                        <span class="<?php echo in_array('projector', $filters) ? 'text-wrapper-2' : 'text-wrapper-3'; ?>">빔프로젝터</span>
                    </div>
                </label>
                <label class="filter-label">
                    <input type="checkbox" name="filter[]" value="board" class="hidden-input" <?php echo in_array('board', $filters) ? 'checked' : ''; ?> onchange="this.form.submit()">
                    <div class="<?php echo in_array('board', $filters) ? 'div-wrapper' : 'frame-2'; ?>">
                        <span class="<?php echo in_array('board', $filters) ? 'text-wrapper-2' : 'text-wrapper-3'; ?>">전자칠판</span>
                    </div>
                </label>
                <label class="filter-label">
                    <input type="checkbox" name="filter[]" value="mic" class="hidden-input" <?php echo in_array('mic', $filters) ? 'checked' : ''; ?> onchange="this.form.submit()">
                    <div class="<?php echo in_array('mic', $filters) ? 'div-wrapper' : 'frame-2'; ?>">
                        <span class="<?php echo in_array('mic', $filters) ? 'text-wrapper-2' : 'text-wrapper-3'; ?>">마이크</span>
                    </div>
                </label>
            </section>

            <section class="frame-3" style="position:relative;">
                <input type="date" name="date" class="transparent-trigger" value="<?php echo $date; ?>" onchange="this.form.submit()">
                <time class="text-wrapper-4"><?php echo $date_display; ?></time>
            </section>

            <section class="frame-4">
                <div class="frame-5"><span class="text-wrapper-4">예약 희망 시간</span></div>
                <div class="frame-3" style="position:relative;">
                    <input type="time" name="start_time" class="transparent-trigger" value="<?php echo $start_time; ?>" onchange="this.form.submit()">
                    <time class="text-wrapper-4"><?php echo $start_time; ?></time>
                </div>
                <div class="frame-5"><span class="text-wrapper-4">~</span></div>
                <div class="frame-3" style="position:relative;">
                    <input type="time" name="end_time" class="transparent-trigger" value="<?php echo $end_time; ?>" onchange="this.form.submit()">
                    <time class="text-wrapper-4"><?php echo $end_time; ?></time>
                </div>
            </section>
    </form> <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <article class="frame-6" data-room-id="<?php echo $row['id']; ?>" data-room-name="<?php echo $row['class_number']; ?>">
                <div class="frame-7">
                    <div class="frame-5">
                        <h2 class="text-wrapper-5"><?php echo $row['class_number']; ?></h2>
                    </div>
                    <div class="frame-8">
                        <div class="frame-3"><time class="text-wrapper-4 start-time"><?php echo $start_time; ?></time></div>
                        <div class="frame-5"><span class="text-wrapper-4">~</span></div>
                        <div class="frame-3"><time class="text-wrapper-4 end-time"><?php echo $end_time; ?></time></div>
                    </div>
                </div>

                <figure class="frame-9">
                    <?php if(!empty($row['image_URL']) && file_exists($row['image_URL'])): ?>
                        <img src="<?php echo $row['image_URL']; ?>" alt="강의실" style="width:100%; height:100%; object-fit:cover;">
                    <?php else: ?>
                        <figcaption class="text-wrapper-6"><?php echo $row['class_number']; ?> 강의실</figcaption>
                    <?php endif; ?>
                </figure>

                <div class="frame-7">
                    <p class="p">
                        <?php echo $row['capacity_number']; ?>명 수용
                        <?php if($row['has_pc'] == 'Y') echo " / PC"; ?>
                        <?php if($row['board_type'] == '전자칠판') echo " / 전자칠판"; ?>
                    </p>
                    <button class="view-2 reservation-btn" type="button">
                        <span class="text-wrapper">예약하기</span>
                    </button>
                </div>
            </article>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align:center; padding:30px;">조건에 맞는 강의실이 없습니다.</p>
    <?php endif; ?>
    </main>

    <div id="reservationModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2 style="text-align:center; margin-bottom:20px; color:var(--main);">예약 정보 확인</h2>
            
            <div class="modal-row">
                <span class="modal-label">강의실:</span>
                <span class="modal-value" id="modal-room-display"></span>
            </div>
            <div class="modal-row">
                <span class="modal-label">날짜:</span>
                <span class="modal-value"><?php echo $date_display; ?></span>
            </div>
            <div class="modal-row">
                <span class="modal-label">시간:</span>
                <span class="modal-value" id="modal-time-display"></span>
            </div>
            
            <div class="modal-row" style="flex-direction:column; align-items:flex-start; gap:5px;">
                <span class="modal-label">대여 목적:</span>
                <input type="text" id="input-purpose" placeholder="예: 동아리 회의, 과제 등" 
                       style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; font-family:inherit;">
            </div>

            <form action="reservation_process.php" method="POST" id="confirmForm">
                <input type="hidden" name="class_id" id="modal-class-id">
                <input type="hidden" name="date" value="<?php echo $date; ?>">
                <input type="hidden" name="start_time" value="<?php echo $start_time; ?>">
                <input type="hidden" name="end_time" value="<?php echo $end_time; ?>">
                
                <input type="hidden" name="purpose" id="form-purpose">
                
                <button type="button" class="confirm-btn" id="realConfirmBtn">예약 확정</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const modal = document.getElementById("reservationModal");
            const closeBtn = modal.querySelector(".close-btn");
            const reservationBtns = document.querySelectorAll(".reservation-btn");
            
            // 모달 요소들
            const modalRoomDisplay = document.getElementById("modal-room-display");
            const modalTimeDisplay = document.getElementById("modal-time-display");
            const modalClassIdInput = document.getElementById("modal-class-id");
            
            // 목적 입력 관련
            const inputPurpose = document.getElementById("input-purpose");
            const formPurpose = document.getElementById("form-purpose");
            const realConfirmBtn = document.getElementById("realConfirmBtn");
            const confirmForm = document.getElementById("confirmForm");

            // 예약 버튼 클릭 (모달 열기)
            reservationBtns.forEach((button) => {
                button.addEventListener("click", (event) => {
                    const article = event.target.closest(".frame-6");
                    
                    const roomName = article.getAttribute("data-room-name");
                    const roomId = article.getAttribute("data-room-id"); 
                    const startTime = article.querySelector(".start-time").textContent;
                    const endTime = article.querySelector(".end-time").textContent;

                    modalRoomDisplay.textContent = roomName;
                    modalTimeDisplay.textContent = `${startTime} ~ ${endTime}`;
                    modalClassIdInput.value = roomId;
                    
                    // 입력창 초기화
                    inputPurpose.value = ""; 

                    modal.style.display = "block";
                });
            });

            // 예약 확정 버튼 클릭 (데이터 동기화 및 전송)
            realConfirmBtn.addEventListener("click", () => {
                if(inputPurpose.value.trim() === "") {
                    alert("대여 목적을 입력해주세요.");
                    inputPurpose.focus();
                    return;
                }
                // 입력된 목적을 hidden input에 복사
                formPurpose.value = inputPurpose.value;
                // 폼 제출
                confirmForm.submit();
            });

            closeBtn.addEventListener("click", () => { modal.style.display = "none"; });
            window.addEventListener("click", (event) => {
                if (event.target === modal) { modal.style.display = "none"; }
            });
        });
    </script>
</body>
</html>