<?php
/* ---------------------------------------------------------------------------
   HTTP → HTTPS 리다이렉트 (서버 측)

   이 서버는 .htaccess 를 읽지 않아 PHP 로 처리한다. 자바스크립트 방식과 달리
   진짜 리다이렉트라 검색엔진이 주소 이전으로 인정한다.

   [중요] 이 블록은 파일 맨 처음이어야 한다. 앞에 공백/빈 줄도 있으면 안 된다.
          출력이 한 글자라도 먼저 나가면 header() 가 동작하지 않는다.

   [테스트 중] 지금은 302(임시). 며칠 정상 동작하면 302 를 301 로 바꾼다.
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lasagna Empire</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700&family=Noto+Sans+KR:wght@300;400;700&display=swap" rel="stylesheet">

    <style>
        /* =========================================
           1. 공통 설정
           ========================================= */
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            background-color: #000;
            color: #fff;
            font-family: 'Inter', 'Noto Sans KR', sans-serif;
        }
        a { text-decoration: none; color: inherit; }

        /* 로고 공통 (PC/모바일 둘 다 사용) */
        .logo-img {
            width: 120px;
            height: auto;
            /* 검은 로고를 흰색으로 반전 */
            filter: invert(1) brightness(100);
        }

        /* =========================================
           2. 데스크탑 레이아웃 (PC)
           구조: [섹션1: 풀스크린 비디오] -> [섹션2: 텍스트 콘텐츠]
           ========================================= */

        /* 1) 히어로 섹션 (첫 화면 영상) */
        .desktop-hero {
            display: block; /* PC에서만 보임 */
            position: relative;
            width: 100%;
            height: 100vh; /* 화면 높이 100% */
            overflow: hidden;
        }

        .hero-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-overlay {
            position: absolute; top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.3);
        }

        /* PC 로고 (영상 위에 고정) */
        .pc-logo {
            position: absolute;
            top: 40px; left: 50px;
            z-index: 10;
        }

        /* PC 버튼 (영상 중앙 하단) */
        .pc-btn-area {
            position: absolute;
            bottom: 80px; left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            text-align: center;
        }

        /* 스크롤 유도 화살표 (선택사항) */
        .scroll-indicator {
            margin-bottom: 20px;
            font-size: 0.8rem;
            opacity: 0.7;
            animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-10px);}
            60% {transform: translateY(-5px);}
        }

        .btn {
            display: inline-block;
            padding: 15px 40px;
            border: 1px solid #fff;
            color: #fff;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
        }
        .btn:hover { background: #fff; color: #000; }

        /* 메일 주소 표기 — mailto 가 열리지 않는 환경에서도 연락처가 남도록 */
        .mail-line {
            margin-top: 16px;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            color: rgba(255, 255, 255, 0.7);
        }
        .mail-line a {
            border-bottom: 1px solid rgba(255, 255, 255, 0.35);
            padding-bottom: 2px;
            transition: 0.3s;
        }
        .mail-line a:hover { color: #fff; border-bottom-color: #fff; }
        .mail-line .copied {
            display: inline-block;
            margin-left: 8px;
            opacity: 0;
            transition: opacity 0.25s;
            color: #fff;
        }
        .mail-line .copied.show { opacity: 1; }


        /* 2) 콘텐츠 섹션 (스크롤 내리면 나오는 곳) */
        .desktop-content {
            display: block; /* PC에서만 보임 */
            background-color: #111; /* 배경색 */
            padding: 100px 0;
            text-align: center; /* 텍스트 중앙 정렬 */
        }

        .content-inner {
            max-width: 800px; /* 글자가 너무 퍼지지 않게 폭 제한 */
            margin: 0 auto;
            padding: 0 40px;
            text-align: left; /* 본문은 왼쪽 정렬 */
        }

        .dt-text-en {
            font-size: 1.5rem; /* PC라 글씨 좀 더 크게 */
            line-height: 1.6;
            font-weight: 300;
            margin-bottom: 60px;
            color: #ddd;
        }

        .dt-divider {
            width: 40px; height: 2px;
            background: #fff;
            margin-bottom: 60px;
        }

        .dt-text-kr {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #bbb;
            margin-bottom: 100px;
            word-break: keep-all;
        }

        .dt-footer {
            border-top: 1px solid #333;
            padding-top: 40px;
            font-size: 0.9rem;
            color: #666;
            font-family: 'Courier New', monospace;
        }


        /* =========================================
           3. 모바일 레이아웃 (Mobile)
           기존 디자인 유지 (로고 -> 텍스트 -> 영상)
           ========================================= */
        .mobile-wrapper {
            display: none; /* PC에서는 숨김 */
            padding: 40px 20px;
            background: #111;
        }

        .m-logo { margin-bottom: 50px; }

        .m-text-group {
            font-size: 0.95rem;
            line-height: 1.6;
            color: #ccc;
            margin-bottom: 50px;
        }
        .m-text-en { margin-bottom: 30px; }
        .m-divider { width: 30px; height: 1px; background: #fff; margin-bottom: 30px; }
        .m-text-kr { font-size: 0.9rem; word-break: keep-all; }

        .m-video-box {
            width: 100%;
            margin-bottom: 60px;
            aspect-ratio: 16 / 9;
            background: #000;
        }
        .m-video-box video {
            width: 100%; height: 100%; object-fit: cover;
        }

        .m-footer {
            font-size: 0.75rem; color: #777;
            font-family: 'Courier New', monospace;
            border-top: 1px solid #333;
            padding-top: 20px;
        }


        /* =========================================
           4. 반응형 스위치 (미디어 쿼리)
           ========================================= */
        @media (max-width: 768px) {
            /* 모바일 환경 */
            body, html { height: auto; overflow-x: hidden; }

            .desktop-hero { display: none !important; }
            .desktop-content { display: none !important; }

            .mobile-wrapper { display: block !important; }
        }

    </style>
</head>
<body>

    <section class="desktop-hero">
        <video autoplay muted loop playsinline class="hero-video">
            <source src="background.mp4" type="video/mp4">
        </video>
        <div class="hero-overlay"></div>

        <div class="pc-logo">
            <a href="#"><img src="logo.png" alt="LASAGNA" class="logo-img"></a>
        </div>

        <div class="pc-btn-area">
            <div class="scroll-indicator">Scroll Down</div>
            <a href="mailto:info@lasagna.kr" class="btn">Project Request</a>
            <div class="mail-line">
                <a href="mailto:info@lasagna.kr" data-mail>info@lasagna.kr</a><span class="copied">복사됨</span>
            </div>
        </div>
    </section>

    <section class="desktop-content">
        <div class="content-inner">
            <div class="dt-text-en">
                Lasagna is a design company that uses AI as a tool for thinking and structuring ideas.
                We redesign not only outcomes, but the way problems are approached.
                By layering technology, sensibility, strategy, and execution, we create new rules.
            </div>

            <div class="dt-divider"></div>

            <div class="dt-text-kr">
                라자냐는 AI를 사고의 도구로 삼아 디자인의 구조를 설계하는 회사입니다.<br>
                우리는 단순한 결과물이 아니라, 문제를 바라보는 방식부터 다시 디자인합니다.<br>
                기술과 감각, 전략과 실행을 겹겹이 쌓아 새로운 규칙을 만들어갑니다.<br>
                빠른 실험과 정교한 선택을 통해, 의미 있는 결과로 답합니다.
            </div>

            <div class="dt-footer">
                2026, Jan. 230 project<br><br>
                © Lasagna. We use AI as a tool for thinking and design as a language. All rights reserved.
                <div class="mail-line">
                    <a href="mailto:info@lasagna.kr" data-mail>info@lasagna.kr</a><span class="copied">복사됨</span>
                </div>
            </div>
        </div>
    </section>


    <div class="mobile-wrapper">
        <div class="m-logo">
            <img src="logo.png" alt="LASAGNA" class="logo-img">
        </div>

        <div class="m-text-group">
            <div class="m-text-en">
                Lasagna is a design company that uses AI as a tool for thinking and structuring ideas.
                We redesign not only outcomes, but the way problems are approached.
            </div>
            <div class="m-divider"></div>
            <div class="m-text-kr">
                라자냐는 AI를 사고의 도구로 삼아 디자인의 구조를 설계하는 회사입니다.
                기술과 감각, 전략과 실행을 겹겹이 쌓아 새로운 규칙을 만들어갑니다.
            </div>
        </div>

        <div class="m-video-box">
            <video autoplay muted loop playsinline>
                <source src="background.mp4" type="video/mp4">
            </video>
        </div>

        <div class="m-footer">
            2026, Jan. 230 project<br><br>
            © Lasagna. All rights reserved.<br><br>
            <a href="mailto:info@lasagna.kr" style="color:#fff; text-decoration:underline;">Project Request</a>
            <div class="mail-line">
                <a href="mailto:info@lasagna.kr" data-mail>info@lasagna.kr</a><span class="copied">복사됨</span>
            </div>
        </div>
    </div>


<script>
/* 메일 주소를 누르면 클립보드에도 복사한다.
   기본 메일 앱이 없는 환경에서는 mailto 가 아무 반응이 없는데,
   그때도 주소가 손에 남도록 하는 장치다. mailto 동작은 막지 않는다. */
(function () {
  document.querySelectorAll('a[data-mail]').forEach(function (a) {
    a.addEventListener('click', function () {
      var addr = a.textContent.trim();
      var note = a.parentNode.querySelector('.copied');
      var done = function () {
        if (!note) return;
        note.classList.add('show');
        setTimeout(function () { note.classList.remove('show'); }, 2000);
      };
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(addr).then(done, function () {});
      } else {
        try {
          var t = document.createElement('textarea');
          t.value = addr; t.style.position = 'fixed'; t.style.opacity = '0';
          document.body.appendChild(t); t.select();
          document.execCommand('copy'); document.body.removeChild(t);
          done();
        } catch (e) {}
      }
    });
  });
})();
</script>
</body>
</html>
