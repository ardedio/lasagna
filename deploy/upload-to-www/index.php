<?php
/* ---------------------------------------------------------------------------
   HTTP → HTTPS 리다이렉트 (서버 측)

   이 서버는 .htaccess 를 읽지 않아(openresty/nginx) PHP 로 처리한다.
   자바스크립트 방식과 달리 진짜 리다이렉트라 검색엔진이 주소 이전으로 인정한다.

   [중요] 이 블록은 파일 맨 처음이어야 한다. 앞에 공백/빈 줄도 있으면 안 된다.
          출력이 한 글자라도 먼저 나가면 header() 가 동작하지 않는다.

   [테스트 중] 지금은 302(임시). 정상 확인 후 아래 302 를 301 로 바꾼다.
   --------------------------------------------------------------------------- */
$is_https =
       (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
    || (!empty($_SERVER['HTTP_X_FORWARDED_SSL'])   && strtolower($_SERVER['HTTP_X_FORWARDED_SSL'])   === 'on')
    || (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

if (!$is_https) {
    $host = $_SERVER['HTTP_HOST'] ?? 'www.lasagna.kr';
    $uri  = $_SERVER['REQUEST_URI'] ?? '/';
    header('Location: https://' . $host . $uri, true, 302);   // 확인 후 301 로
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<!-- PHP 리다이렉트가 우선이지만, 만약을 위한 보조 장치로 남겨둔다. -->
<script>
(function () {
  if (location.protocol === 'http:') {
    location.replace('https://' + location.host
                     + location.pathname + location.search + location.hash);
  }
})();
</script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lasagna Empire</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700&family=Noto+Sans+KR:wght@300;400;700&display=swap" rel="stylesheet">
    
    <style>
        /* ==============================
           1. 공통 기본 스타일
           ============================== */
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            background-color: #000;
            font-family: 'Inter', 'Noto Sans KR', sans-serif;
            color: #fff;
        }

        a { text-decoration: none; color: inherit; }

        /* ==============================
           2. 데스크탑 전용 스타일 (PC)
           ============================== */
        #desktop-view {
            display: block; /* 기본적으로 보임 */
            width: 100%;
            height: 100%;
            overflow: hidden;
            position: relative;
        }

        #bg-video {
            position: absolute;
            top: 50%; left: 50%;
            min-width: 100%; min-height: 100%;
            width: auto; height: auto;
            transform: translate(-50%, -50%);
            object-fit: cover;
            z-index: 0;
        }

        .overlay {
            position: absolute; top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1;
        }

        .dt-logo {
            position: absolute; top: 30px; left: 40px; z-index: 10;
        }
        
        /* ★ 수정됨: 모바일과 동일하게 너비 120px로 변경 ★ */
        .dt-logo img { 
            width: 120px; 
            height: auto; 
        }

        .dt-content {
            position: absolute; top: 75%; left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2;
        }

        .btn {
            display: inline-block;
            padding: 15px 30px;
            border: 1px solid white;
            color: white;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
        }
        .btn:hover { background: white; color: black; }


        /* ==============================
           3. 모바일 전용 스타일 (Mobile)
           ============================== */
        #mobile-view {
            display: none; /* PC에서는 숨김 */
            padding: 40px 20px;
            box-sizing: border-box;
            background-color: #111; /* 짙은 검은색 배경 */
            height: auto;
            min-height: 100%;
        }

        .m-logo {
            margin-bottom: 50px;
        }
        
        .m-logo img {
            width: 120px; /* 모바일 로고 크기 */
            height: auto;
            /* 검은색 로고를 흰색으로 반전 */
            filter: invert(1) brightness(100); 
        }

        .m-text-group {
            font-size: 0.9rem;
            line-height: 1.6;
            color: #ccc;
            margin-bottom: 50px;
            font-weight: 300;
        }

        .m-text-en { margin-bottom: 30px; }
        
        .m-divider {
            width: 30px;
            height: 1px;
            background-color: #fff;
            margin-bottom: 30px;
        }

        .m-text-kr { font-size: 0.85rem; word-break: keep-all; }

        /* 모바일 중간 영상 영역 */
        .m-video-container {
            width: 100%;
            margin-bottom: 60px;
            aspect-ratio: 16 / 9; /* 16:9 비율 유지 */
            background: #000;
        }
        .m-video-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .m-footer {
            font-size: 0.75rem;
            color: #888;
            font-family: 'Courier New', monospace; /* 하단 폰트 느낌 */
            border-top: 1px solid #333;
            padding-top: 20px;
        }
        .m-project-date {
            display: block; margin-bottom: 40px; color: #fff;
        }
        .m-copyright { line-height: 1.4; }


        /* ==============================
           4. 반응형 스위치 (핵심 로직)
           ============================== */
        @media (max-width: 768px) {
            body, html { overflow: auto; /* 모바일은 스크롤 허용 */ }
            #desktop-view { display: none !important; }
            #mobile-view { display: block !important; }
        }
    </style>
</head>
<body>

    <div id="desktop-view">
        <video autoplay muted loop playsinline id="bg-video">
            <source src="background.mp4" type="video/mp4">
        </video>
        <div class="overlay"></div>
        
        <div class="dt-logo">
            <a href="#"><img src="logo.png" alt="Lasagna Logo"></a>
        </div>
        
        <div class="dt-content">
            <a href="mailto:info@lasagna.kr" class="btn">Project Request</a>
        </div>
    </div>


    <div id="mobile-view">
        <div class="m-logo">
            <img src="logo.png" alt="LASAGNA">
        </div>

        <div class="m-text-group">
            <div class="m-text-en">
                Lasagna is a design company that uses AI as a tool for thinking and structuring ideas.
                We redesign not only outcomes, but the way problems are approached.
                By layering technology, sensibility, strategy, and execution, we create new rules.
                Through rapid experimentation and deliberate decisions, we deliver meaningful results.
            </div>
            
            <div class="m-divider"></div>
            
            <div class="m-text-kr">
                라자냐는 AI를 사고의 도구로 삼아 디자인의 구조를 설계하는 회사입니다.
                우리는 단순한 결과물이 아니라, 문제를 바라보는 방식부터 다시 디자인합니다.
                기술과 감각, 전략과 실행을 겹겹이 쌓아 새로운 규칙을 만들어갑니다.
                빠른 실험과 정교한 선택을 통해, 의미 있는 결과로 답합니다.
            </div>
        </div>

        <div class="m-video-container">
            <video autoplay muted loop playsinline>
                <source src="background.mp4" type="video/mp4">
            </video>
        </div>

        <div class="m-footer">
            <span class="m-project-date">2026, Jan. 230 project</span>
            <div class="m-divider" style="width:20px; margin: 20px 0;"></div>
            <div class="m-copyright">
                © Lasagna. We use AI as a tool for thinking and design as a language to build meaningful structures and lasting experiences. All rights reserved.
                <br><br>
                <a href="mailto:info@lasagna.kr" style="color:#fff; text-decoration:underline;">Project Request</a>
            </div>
        </div>
    </div>

</body>
</html>