<?php
// PHP 가 동작하는지 확인하는 파일. 확인 후 삭제한다.
// /www 에 올리고 브라우저에서 https://www.lasagna.kr/phptest.php 접속.
//
//   "PHP OK" 와 아래 정보가 보이면      → PHP 사용 가능
//   코드가 그대로 보이거나 다운로드되면  → PHP 사용 불가
echo "PHP OK\n\n";
echo "HTTPS            : " . (isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : '(없음)') . "\n";
echo "X-Forwarded-Proto: " . (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : '(없음)') . "\n";
echo "SERVER_PORT      : " . (isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : '(없음)') . "\n";
