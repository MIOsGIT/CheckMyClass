<?php
use App\Db\Entity\User;
use App\Db\Entity\Major;

// 1. Doctrine EntityManager 및 Autoload 로드
/** @var \Doctrine\ORM\EntityManager $entityManager */
$entityManager = require __DIR__ . '/../bootstrap.php';

// 응답 헤더 설정 (JSON으로 응답)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // CORS 허용 (개발 단계에서만 사용 권장)

// POST 요청의 JSON 본문 읽기
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '유효하지 않은 요청 방식 또는 JSON 형식입니다.']);
    exit;
}

// 2. 입력 데이터 유효성 검사 및 필수 필드 확인
$required_fields = ['user_id', 'user_password', 'user_name', 'student_number', 'major_name'];
foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "$field 필드는 필수입니다."]);
        exit;
    }
}

// 3. Major (학과) 엔티티 조회
$majorRepository = $entityManager->getRepository(Major::class);
$major = $majorRepository->findOneBy(['majorName' => $data['major_name']]);

if (!$major) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '존재하지 않는 학과 이름입니다.', 'major' => $data['major_name']]);
    exit;
}

// 4. 아이디 중복 검사 (옵션)
$existingUser = $entityManager->getRepository(User::class)->findOneBy(['userId' => $data['user_id']]);
if ($existingUser) {
    http_response_code(409); // Conflict
    echo json_encode(['success' => false, 'message' => '이미 사용 중인 아이디입니다.']);
    exit;
}

try {
    // 5. User 엔티티 생성 및 데이터 설정
    $user = new User();
    $user->setUserId($data['user_id']);
    // 비밀번호 해시 (필수 보안 조치)
    $hashedPassword = password_hash($data['user_password'], PASSWORD_BCRYPT);
    $user->setUserPassword($hashedPassword);
    $user->setUserName($data['user_name']);
    $user->setStudentNumber($data['student_number']);
    
    // 선택적 필드 설정
    if (isset($data['phone_number'])) {
        $user->setPhoneNumber($data['phone_number']);
    }

    // 6. 관계 설정: Major 엔티티 연결
    $user->setMajor($major);

    // 7. DB에 저장
    $entityManager->persist($user);
    $entityManager->flush();

    // 8. 성공 응답
    http_response_code(201); // Created
    echo json_encode([
        'success' => true, 
        'message' => '회원가입이 성공적으로 완료되었습니다.',
        'user_id' => $user->getId()
    ]);

} catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
    // Unique 필드(학번, 아이디 등) 중복 시 처리
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => '이미 등록된 사용자 정보(학번 또는 전화번호)가 있습니다.']);
} catch (\Exception $e) {
    // 기타 예상치 못한 오류
    http_response_code(500);
    error_log($e->getMessage()); // 서버 로그에 기록
    echo json_encode(['success' => false, 'message' => '서버 오류가 발생했습니다.']);
}