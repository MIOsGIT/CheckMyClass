<!DOCTYPE html>
<html lang="ko">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <title>CheckMyClass - 로그인</title>
    <link rel="stylesheet" href="globals.css" />
    <link rel="stylesheet" href="styleguide.css" />
    <link rel="stylesheet" href="style.css" />
  </head>
  <body>
    <div class="log-in">
      <div class="view">
        <div class="check-my-class">
          <img class="check-my-class" src="Logo_main_CheckMyClass.png" alt="Logo_main" />
        </div>
      </div>
      
      <div class="login">
        <form action="login_process.php" method="POST">
          <div class="info">
            
            <div class="text-wrapper">학번</div>
            <div class="input">
              <input type="text" name="student_id" class="frame" required placeholder="학번 입력" style="border:none; width:100%; height:100%; padding:0 10px;">
            </div>

            <div class="div">비밀번호</div>
            <div class="input">
              <input type="password" name="password" class="frame" required placeholder="비밀번호 입력" style="border:none; width:100%; height:100%; padding:0 10px;">
            </div>
            
          </div>

          <div class="button">
            <button class="button-wrapper" type="submit" style="border:none; cursor:pointer;">
              <div class="button-2">로그인</div>
            </button>
          </div>
        </form>
      </div>
    </div>
  </body>
</html>