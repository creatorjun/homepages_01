<?php
// public/send_sms.php

header('Content-Type: application/json; charset=UTF-8');

// --- .env 파일에서 설정값 로드 ---
$env_path = __DIR__ . '/.env';
if (!file_exists($env_path)) {
    echo json_encode([
        "success"    => false,
        "message"    => "PHP 서버 설정 오류: .env 파일을 찾을 수 없습니다.",
        "munja_code" => "CFG_ERR",
        "munja_msg"  => "Missing .env file"
    ]);
    exit;
}

$env = parse_ini_file($env_path);

$munja_remote_id       = $env['MUNJA_REMOTE_ID']       ?? '';
$munja_remote_pass     = $env['MUNJA_REMOTE_PASS']     ?? '';
$munja_remote_callback = $env['MUNJA_REMOTE_CALLBACK'] ?? '';
$admin_phone_number    = $env['ADMIN_PHONE_NUMBER']    ?? '';
// --- 설정값 로드 끝 ---

// 설정값 유효성 검사
if (empty($munja_remote_id) || empty($munja_remote_pass) || empty($munja_remote_callback) || empty($admin_phone_number)) {
    echo json_encode([
        "success"    => false,
        "message"    => "PHP 서버 설정 오류: .env 파일의 값이 올바르게 설정되지 않았습니다. .env.example 파일을 참고하여 .env 파일을 확인해주세요.",
        "munja_code" => "CFG_ERR",
        "munja_msg"  => "Configuration Error"
    ]);
    exit;
}

$response_array = [
    "success"    => false,
    "message"    => "요청 처리 중 오류가 발생했습니다.",
    "munja_code" => "",
    "munja_msg"  => ""
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response_array["message"]    = "잘못된 요청입니다. (POST 방식 필요)";
    $response_array["munja_code"] = "REQ_ERR";
    echo json_encode($response_array);
    exit;
}

// action 파라미터로 처리 경로 분기 (기본값: contact)
$action = isset($_POST['action']) ? trim($_POST['action']) : 'contact';

if ($action === 'sms') {
    // --- 문자 상담 처리 (FloatingContainer) ---
    $message = isset($_POST['Message']) ? trim($_POST['Message']) : '';
    $mobile  = isset($_POST['Mobile'])  ? trim($_POST['Mobile'])  : '';

    if (empty($message)) {
        $response_array["message"] = "메시지 내용을 입력해주세요.";
        echo json_encode($response_array);
        exit;
    }
    if (!preg_match("/^\d{10,11}$/", preg_replace("/[^0-9]/", "", $mobile))) {
        $response_array["message"] = "올바른 형식의 연락처를 입력해주세요. (예: 01012345678)";
        echo json_encode($response_array);
        exit;
    }

    $sms_content_lines   = [];
    $sms_content_lines[] = "[상도 힐스더원 문자상담]";
    $sms_content_lines[] = "발신: " . $mobile;
    $sms_content_lines[] = "내용: " . $message;
    $sms_message_to_admin = implode("\n", $sms_content_lines);
    $lms_subject = "[상도 힐스더원] 문자상담";

} else {
    // --- 상담 신청 처리 (Contact 폼) ---
    $name       = isset($_POST['Name'])       ? trim($_POST['Name'])       : '';
    $mobile     = isset($_POST['Mobile'])     ? trim($_POST['Mobile'])     : '';
    $visit_day  = isset($_POST['Visit_Day'])  && !empty(trim($_POST['Visit_Day']))  ? trim($_POST['Visit_Day'])  : '미지정';
    $visit_time = isset($_POST['Visit_Time']) && $_POST['Visit_Time'] !== '시간선택없음' ? trim($_POST['Visit_Time']) : '미지정';
    $agree_yn   = isset($_POST['AgreeYN'])    ? $_POST['AgreeYN']          : '';

    if (empty($name)) {
        $response_array["message"] = "성명을 입력해주세요.";
        echo json_encode($response_array);
        exit;
    }
    if (empty($mobile)) {
        $response_array["message"] = "연락처를 입력해주세요.";
        echo json_encode($response_array);
        exit;
    }
    if (!preg_match("/^\d{10,11}$/", preg_replace("/[^0-9]/", "", $mobile))) {
        $response_array["message"] = "올바른 형식의 연락처를 입력해주세요. (예: 01012345678)";
        echo json_encode($response_array);
        exit;
    }
    if ($agree_yn !== 'Y') {
        $response_array["message"] = "개인정보 수집 및 이용에 동의해주세요.";
        echo json_encode($response_array);
        exit;
    }

    $customer_mobile_formatted = preg_replace("/[^0-9]/", "", $mobile);

    $sms_content_lines   = [];
    $sms_content_lines[] = "[상도 힐스더원 관심고객]";
    $sms_content_lines[] = "성명: " . $name;
    $sms_content_lines[] = "연락처: " . $customer_mobile_formatted;
    if ($visit_day !== '미지정') {
        $sms_content_lines[] = "방문일: " . $visit_day;
    }
    if ($visit_time !== '미지정') {
        $sms_content_lines[] = "방문시간: " . $visit_time;
    }
    $sms_message_to_admin = implode("\n", $sms_content_lines);
    $lms_subject = "[상도 힐스더원] 문의";
}

// --- 공통: 문자세상 API 호출 ---
$api_params = [
    'remote_id'       => $munja_remote_id,
    'remote_pass'     => $munja_remote_pass,
    'remote_num'      => '1',
    'remote_reserve'  => '0',
    'remote_phone'    => $admin_phone_number,
    'remote_callback' => $munja_remote_callback,
];

$temp_euckr  = iconv("UTF-8", "EUC-KR//IGNORE", $sms_message_to_admin);
$byte_length = strlen($temp_euckr);

if ($byte_length <= 90) {
    $api_params['remote_msg'] = $sms_message_to_admin;
    $munja_api_url = "https://www.sms010.co.kr/Remote/RemoteSms.html";
} else {
    $api_params['remote_subject'] = $lms_subject;
    $api_params['remote_msg']     = $sms_message_to_admin;
    $munja_api_url = "https://www.sms010.co.kr/Remote/RemoteMms.html";
}

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL,            $munja_api_url);
curl_setopt($curl, CURLOPT_POST,           true);
curl_setopt($curl, CURLOPT_POSTFIELDS,     http_build_query($api_params, '', '&'));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$munja_api_response_raw = curl_exec($curl);
$curl_error_msg = curl_error($curl);
$curl_error_no  = curl_errno($curl);
curl_close($curl);

if ($munja_api_response_raw === false) {
    $response_array["message"]    = "문자 발송 API 연동 중 오류가 발생했습니다. (cURL Error: " . $curl_error_no . " - " . $curl_error_msg . ")";
    $response_array["munja_code"] = "CURL_ERR";
    $response_array["munja_msg"]  = $curl_error_msg;
} else {
    $response_parts = explode("|", $munja_api_response_raw);

    $response_array["munja_code"] = isset($response_parts[0]) ? trim($response_parts[0]) : "PARSE_ERR";
    $response_array["munja_msg"]  = isset($response_parts[1]) ? trim($response_parts[1]) : "응답 메시지 없음";

    if ($response_array["munja_code"] === "0000" || strpos($response_array["munja_msg"], "성공") !== false) {
        $response_array["success"] = true;
        $response_array["message"] = ($action === 'sms')
            ? "메세지가 성공적으로 전송되었습니다."
            : "신청이 성공적으로 접수되었습니다. 담당자가 곧 연락드릴 예정입니다.";
    } else {
        $error_message_map = [
            "0001" => "접속에러",
            "0002" => "인증에러 (아이디 또는 비밀번호 확인)",
            "0003" => "잔여콜수 없음",
            "0004" => "메시지 형식에러",
            "0005" => "콜백번호(발신번호) 에러",
            "0006" => "수신번호 개수 에러",
            "0008" => "잔여콜수 부족",
            "0009" => "전송실패 (시스템 오류)",
            "0012" => "메시지 길이오류 (2000바이트 초과)",
            "0030" => "발신번호 사전등록 미등록",
            "0033" => "발신번호 형식에러",
            "9999" => "요금미납"
        ];
        $response_array["message"] = ($action === 'sms')
            ? "문자 발송에 실패했습니다. "
            : "신청 접수 중 오류가 발생했습니다. ";
        if (array_key_exists($response_array["munja_code"], $error_message_map)) {
            $response_array["message"] .= "(" . $error_message_map[$response_array["munja_code"]] . ")";
        } else {
            $response_array["message"] .= "오류코드: " . $response_array["munja_code"];
        }
        $response_array["message"] .= " - API 메시지: " . $response_array["munja_msg"];
    }
}

echo json_encode($response_array);
?>