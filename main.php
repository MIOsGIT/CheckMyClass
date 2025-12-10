<?php
session_start();

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='login.html';</script>";
    exit();
}
$user_name = $_SESSION['user_name'];
$major_name = $_SESSION['department'];
$user_role = $_SESSION['role'];

if (empty($major_name)) {
    $major_display = "소속 학과 없음"; 
} else {
    $major_display = $major_name;
}

if ($user_role === 'STUDENT') {
    $role_display = "학생회원";
} elseif ($user_role === 'PROFESSOR') {
    $role_display = "교직원";
} else {
    $role_display = "회원";
}
?>
<!DOCTYPE html>
<html lang="ko">

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta charset="utf-8" />
  <title>CheckMyClass - 강의실 예약 시스템</title>
  <link rel="stylesheet" href="globals.css" />
  <link rel="stylesheet" href="styleguide.css" />
  <link rel="stylesheet" href="style.css" />
</head>

<body>
  <main class="main">
    <header class="header">
      <div class="frame">
        <img class="checkmyclass-x" src="source/Logo_img.png" alt="CheckMyClass 로고" />
      </div>
      <img class="check-my-class" src="source/Logo_eng.png" alt="Check My Class" />
    </header>
    
    <section class="profile" aria-label="사용자 프로필" onclick="location.href='mypage.php'" style="cursor: pointer;">
      <div class="div">
        <div class="view" role="img" aria-label="프로필 이미지">
          <img class="deu" src="source/Logo_dongeui.png" alt="Logo_dongeui" />
        </div>
        <p class="text-wrapper">
            <?php echo $major_display; ?><br />
            <?php echo $user_name; ?>
        </p>
      </div>
      <div class="div-wrapper">
        <span class="text-wrapper-2"><?php echo $role_display; ?></span>
      </div>
    </section>
    
    <nav class="view-2" aria-label="위치 선택">
      <button class="frame-2" type="button" aria-label="산학협력관 선택">
        <span class="text-wrapper-3">산학협력관</span>
      </button>
      <button class="frame-2" type="button" aria-label="4층 선택">
        <span class="text-wrapper-3">4층</span>
      </button>
    </nav>
    
    <span class="text-wrapper-3">강의실 구조도</span>
    
    <section aria-label="강의실 구조도" style="width: 100%; display: flex; flex-direction: column; align-items: center;">
      <div class="map-container" style="width: 90%; max-width: 100%;">
        <div class="map-wrapper" id="mapWrapper">
            <img src="source/map.png" alt="강의실 구조도 이미지" class="map-image" style="width: 100%; height: auto;">
            <div class="gradient-overlay"></div>
        </div>
      </div>
      <button type="button" id="toggleMapBtn" class="more-btn">
        지도 전체보기 ∨
      </button>
    </section>

    <button class="view-4" type="button" aria-label="예약하기" onclick="location.href='reservation.php'">
      <span class="text-wrapper-4">예약하기</span>
    </button>
  </main>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
        const mapWrapper = document.getElementById("mapWrapper");
        const toggleBtn = document.getElementById("toggleMapBtn");

        toggleBtn.addEventListener("click", () => {
            mapWrapper.classList.toggle("expanded");
            if (mapWrapper.classList.contains("expanded")) {
                toggleBtn.textContent = "지도 접기 ∧";
            } else {
                toggleBtn.textContent = "지도 전체보기 ∨";
            }
        });
    });
  </script>
</body>
</html>