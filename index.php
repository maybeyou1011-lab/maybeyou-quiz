<!DOCTYPE html>
<html lang="zh-Hant">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MaybeYou 每日營養搭配測驗</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@300;400;500;700&display=swap');

  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    font-family: 'Noto Sans TC', sans-serif;
    background: linear-gradient(135deg, #fdf6f0 0%, #fce4ec 50%, #f3e5f5 100%);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 20px;
  }

  .quiz-container {
    max-width: 480px;
    width: 100%;
    margin: 20px auto;
  }

  /* ═══════════════════════════════════════════
     LANDING PAGE
     ═══════════════════════════════════════════ */
  .landing {
    text-align: center;
    background: white;
    border-radius: 24px;
    padding: 40px 32px 48px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.08);
  }

  /* ─── Gear Animation ─── */
  .gear-section {
    position: relative;
    width: 200px;
    height: 160px;
    margin: 0 auto 24px;
  }

  .gear {
    position: absolute;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .gear::before {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: inherit;
  }

  /* Gear teeth using box-shadows */
  .gear-lg {
    width: 100px;
    height: 100px;
    top: 10px;
    left: 16px;
    animation: spinCW 6s linear infinite;
  }
  .gear-lg .gear-body {
    width: 100px;
    height: 100px;
    position: relative;
  }
  .gear-md {
    width: 70px;
    height: 70px;
    top: 50px;
    right: 20px;
    animation: spinCCW 4.2s linear infinite;
  }
  .gear-md .gear-body {
    width: 70px;
    height: 70px;
    position: relative;
  }
  .gear-sm {
    width: 50px;
    height: 50px;
    top: 0;
    right: 40px;
    animation: spinCW 3s linear infinite;
  }
  .gear-sm .gear-body {
    width: 50px;
    height: 50px;
    position: relative;
  }

  /* SVG Gear rendering */
  .gear-svg {
    width: 100%;
    height: 100%;
  }

  @keyframes spinCW  { from { transform: rotate(0deg); }   to { transform: rotate(360deg); } }
  @keyframes spinCCW { from { transform: rotate(0deg); }   to { transform: rotate(-360deg); } }

  /* Gear accelerate on hover */
  .landing:hover .gear-lg { animation-duration: 2s; }
  .landing:hover .gear-md { animation-duration: 1.4s; }
  .landing:hover .gear-sm { animation-duration: 1s; }

  /* Processing state */
  .gear-section.processing .gear-lg { animation-duration: 0.8s; }
  .gear-section.processing .gear-md { animation-duration: 0.56s; }
  .gear-section.processing .gear-sm { animation-duration: 0.4s; }

  .gear-label {
    position: absolute;
    bottom: -4px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 12px;
    color: #bbb;
    white-space: nowrap;
    letter-spacing: 2px;
  }

  .landing h1 {
    font-size: 26px;
    font-weight: 700;
    color: #333;
    margin-bottom: 8px;
  }
  .landing .subtitle {
    font-size: 15px;
    color: #888;
    margin-bottom: 28px;
    line-height: 1.6;
  }
  .landing .tag-row {
    display: flex;
    justify-content: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 32px;
  }
  .landing .tag {
    background: #fef0f5;
    color: #d4618c;
    font-size: 13px;
    padding: 6px 14px;
    border-radius: 20px;
    font-weight: 500;
  }

  .btn-start {
    background: linear-gradient(135deg, #e8789a, #c06cd4);
    color: white;
    border: none;
    padding: 16px 48px;
    font-size: 18px;
    font-weight: 600;
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.3s;
    font-family: inherit;
    letter-spacing: 1px;
  }
  .btn-start:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(192,108,212,0.4);
  }
  .landing .note {
    margin-top: 20px;
    font-size: 13px;
    color: #aaa;
  }

  /* ═══════════════════════════════════════════
     PROGRESS BAR
     ═══════════════════════════════════════════ */
  .progress-wrapper { margin-bottom: 20px; }
  .progress-bar-bg {
    height: 6px;
    background: #e8e0f0;
    border-radius: 3px;
    overflow: hidden;
  }
  .progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #e8789a, #c06cd4);
    border-radius: 3px;
    transition: width 0.4s ease;
  }
  .progress-text {
    text-align: right;
    font-size: 13px;
    color: #999;
    margin-top: 6px;
  }

  /* ═══════════════════════════════════════════
     QUESTION CARD
     ═══════════════════════════════════════════ */
  .question-card {
    background: white;
    border-radius: 24px;
    padding: 36px 28px 28px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.08);
    animation: fadeIn 0.4s ease;
  }
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .q-number {
    display: inline-block;
    background: linear-gradient(135deg, #e8789a, #c06cd4);
    color: white;
    font-size: 13px;
    font-weight: 700;
    padding: 4px 14px;
    border-radius: 20px;
    margin-bottom: 16px;
  }
  .q-title {
    font-size: 20px;
    font-weight: 700;
    color: #333;
    margin-bottom: 24px;
    line-height: 1.5;
  }

  .options {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }
  .option-btn {
    display: flex;
    align-items: center;
    gap: 14px;
    background: #fafafa;
    border: 2px solid #eee;
    border-radius: 16px;
    padding: 16px 20px;
    font-size: 15px;
    color: #444;
    cursor: pointer;
    transition: all 0.25s;
    text-align: left;
    font-family: inherit;
    line-height: 1.5;
  }
  .option-btn:hover {
    border-color: #d4a0e8;
    background: #fdf5ff;
  }
  .option-btn.selected {
    border-color: #c06cd4;
    background: linear-gradient(135deg, #fdf5ff, #fef0f5);
    color: #7b2d8e;
    font-weight: 500;
  }
  .option-icon {
    font-size: 24px;
    flex-shrink: 0;
    width: 36px;
    text-align: center;
  }

  /* Navigation */
  .nav-row {
    display: flex;
    justify-content: space-between;
    margin-top: 24px;
    gap: 12px;
  }
  .btn-back {
    flex: 1;
    background: #f5f0f8;
    color: #999;
    border: none;
    padding: 14px;
    font-size: 15px;
    font-weight: 500;
    border-radius: 14px;
    cursor: pointer;
    font-family: inherit;
    transition: all 0.2s;
  }
  .btn-back:hover { background: #ece4f2; color: #666; }
  .btn-next {
    flex: 2;
    background: linear-gradient(135deg, #e8789a, #c06cd4);
    color: white;
    border: none;
    padding: 14px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 14px;
    cursor: pointer;
    font-family: inherit;
    transition: all 0.3s;
    opacity: 0.4;
    pointer-events: none;
  }
  .btn-next.active {
    opacity: 1;
    pointer-events: auto;
  }
  .btn-next.active:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 16px rgba(192,108,212,0.3);
  }

  /* ═══════════════════════════════════════════
     LOADING / PROCESSING PAGE
     ═══════════════════════════════════════════ */
  .loading-card {
    background: white;
    border-radius: 24px;
    padding: 60px 32px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.08);
    text-align: center;
    animation: fadeIn 0.4s ease;
  }
  .loading-gears {
    position: relative;
    width: 160px;
    height: 130px;
    margin: 0 auto 32px;
  }
  .loading-gears .gear-lg {
    animation-duration: 0.8s;
    top: 8px;
    left: 8px;
  }
  .loading-gears .gear-md {
    animation-duration: 0.56s;
    top: 40px;
    right: 12px;
  }
  .loading-gears .gear-sm {
    animation-duration: 0.4s;
    top: -2px;
    right: 30px;
  }
  .loading-text {
    font-size: 18px;
    font-weight: 600;
    color: #7b2d8e;
    margin-bottom: 8px;
  }
  .loading-sub {
    font-size: 14px;
    color: #aaa;
  }
  .loading-dots::after {
    content: '';
    animation: dots 1.5s steps(4,end) infinite;
  }
  @keyframes dots {
    0%   { content: ''; }
    25%  { content: '.'; }
    50%  { content: '..'; }
    75%  { content: '...'; }
    100% { content: ''; }
  }

  /* ═══════════════════════════════════════════
     RESULT PAGE
     ═══════════════════════════════════════════ */
  .result-card {
    background: white;
    border-radius: 24px;
    padding: 40px 28px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.08);
    text-align: center;
    animation: fadeIn 0.5s ease;
  }
  .result-badge {
    display: inline-block;
    background: linear-gradient(135deg, #e8789a, #c06cd4);
    color: white;
    font-size: 14px;
    font-weight: 600;
    padding: 6px 20px;
    border-radius: 20px;
    margin-bottom: 20px;
  }
  .result-title {
    font-size: 28px;
    font-weight: 700;
    color: #333;
    margin-bottom: 8px;
  }
  .result-code {
    font-size: 15px;
    color: #aaa;
    margin-bottom: 28px;
  }
  .result-desc {
    font-size: 15px;
    color: #666;
    line-height: 1.8;
    margin-bottom: 28px;
    text-align: left;
    background: #fdf8ff;
    padding: 20px;
    border-radius: 16px;
    border-left: 4px solid #c06cd4;
  }

  /* Combo Pills */
  .combo-section { margin-bottom: 28px; }
  .combo-label {
    font-size: 14px;
    color: #999;
    margin-bottom: 12px;
    font-weight: 500;
  }
  .combo-pills {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 8px;
  }
  .pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 24px;
    font-size: 14px;
    font-weight: 600;
    color: white;
  }
  .pill-red    { background: #e74c3c; }
  .pill-orange { background: #f39c12; }
  .pill-yellow { background: #f1c40f; color: #333; }
  .pill-green  { background: #27ae60; }
  .pill-blue   { background: #3498db; }
  .pill-indigo { background: #6c5ce7; }
  .pill-purple { background: #8e44ad; }
  .pill-coffee { background: #795548; }
  .pill-rice   { background: #7e57c2; }
  .pill-cocoa  { background: #5d4037; }

  .pill .pill-count {
    background: rgba(255,255,255,0.3);
    width: 22px; height: 22px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px;
  }

  .suitable-tags {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px;
    margin-bottom: 28px;
  }
  .suitable-tag {
    background: #fef0f5;
    color: #d4618c;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
  }

  .btn-restart {
    background: #f5f0f8;
    color: #7b2d8e;
    border: none;
    padding: 14px 40px;
    font-size: 15px;
    font-weight: 600;
    border-radius: 50px;
    cursor: pointer;
    font-family: inherit;
    transition: all 0.3s;
    margin-top: 8px;
  }
  .btn-restart:hover { background: #ece4f2; }
  .btn-cta {
    background: linear-gradient(135deg, #e8789a, #c06cd4);
    color: white;
    border: none;
    padding: 16px 40px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 50px;
    cursor: pointer;
    font-family: inherit;
    transition: all 0.3s;
    margin-bottom: 12px;
    display: inline-block;
  }
  .btn-cta:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(192,108,212,0.4);
  }

  /* Error toast */
  .toast {
    position: fixed;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%) translateY(100px);
    background: #e74c3c;
    color: white;
    padding: 14px 28px;
    border-radius: 12px;
    font-size: 14px;
    transition: transform 0.3s ease;
    z-index: 999;
  }
  .toast.show { transform: translateX(-50%) translateY(0); }

  .hidden { display: none !important; }
</style>
</head>
<body>

<div class="quiz-container" id="app">

  <!-- ═══ Landing Page ═══ -->
  <div class="landing" id="page-landing">

    <!-- Gear Animation -->
    <div class="gear-section" id="gear-section">
      <!-- Large Gear -->
      <div class="gear gear-lg">
        <svg class="gear-svg" viewBox="0 0 100 100">
          <path d="M50 10 L54 10 L56 2 L60 2 L62 10 L66 12 L72 6 L76 8 L74 16 L78 20 L86 18 L88 22 L80 26 L82 32 L90 34 L90 38 L82 40 L82 46 L90 50 L88 54 L80 52 L78 58 L84 64 L80 68 L74 62 L68 66 L72 74 L68 76 L62 70 L58 72 L58 80 L54 80 L52 72 L46 72 L44 80 L40 80 L40 72 L34 70 L30 76 L26 74 L30 66 L24 62 L18 68 L16 64 L22 58 L18 52 L10 54 L10 50 L18 46 L18 40 L10 38 L10 34 L18 32 L20 26 L12 22 L14 18 L22 20 L26 16 L24 8 L28 6 L34 12 L38 10 L38 2 L42 2 L44 10 Z"
                fill="#c06cd4" opacity="0.9"/>
          <circle cx="50" cy="50" r="18" fill="white"/>
          <circle cx="50" cy="50" r="12" fill="#f3e5f5"/>
          <circle cx="50" cy="50" r="5"  fill="#c06cd4" opacity="0.3"/>
        </svg>
      </div>

      <!-- Medium Gear -->
      <div class="gear gear-md">
        <svg class="gear-svg" viewBox="0 0 100 100">
          <path d="M50 12 L55 12 L57 4 L63 4 L65 12 L70 15 L77 8 L82 12 L76 20 L80 26 L88 26 L89 32 L81 34 L82 40 L90 44 L87 50 L80 48 L77 54 L82 62 L78 66 L72 60 L66 64 L68 72 L62 74 L58 66 L52 68 L52 76 L46 76 L44 68 L38 66 L34 74 L28 72 L32 64 L26 60 L20 66 L16 62 L22 54 L18 48 L10 50 L8 44 L16 40 L16 34 L8 32 L10 26 L18 26 L22 20 L16 12 L22 8 L28 15 L34 12 L34 4 L40 4 L42 12 Z"
                fill="#e8789a" opacity="0.9"/>
          <circle cx="50" cy="50" r="16" fill="white"/>
          <circle cx="50" cy="50" r="10" fill="#fce4ec"/>
          <circle cx="50" cy="50" r="4"  fill="#e8789a" opacity="0.3"/>
        </svg>
      </div>

      <!-- Small Gear -->
      <div class="gear gear-sm">
        <svg class="gear-svg" viewBox="0 0 100 100">
          <path d="M50 14 L56 14 L59 4 L66 6 L65 16 L72 20 L80 14 L84 20 L76 28 L78 36 L88 38 L88 46 L78 46 L76 54 L84 62 L78 68 L72 60 L64 64 L66 74 L58 76 L56 66 L48 66 L44 76 L36 74 L40 64 L32 60 L24 68 L18 62 L28 54 L24 46 L14 46 L14 38 L24 36 L26 28 L18 20 L24 14 L32 20 L38 16 L36 6 L44 4 L44 14 Z"
                fill="#f39c12" opacity="0.9"/>
          <circle cx="50" cy="50" r="14" fill="white"/>
          <circle cx="50" cy="50" r="8"  fill="#fff8e1"/>
          <circle cx="50" cy="50" r="3"  fill="#f39c12" opacity="0.3"/>
        </svg>
      </div>

      <span class="gear-label">ANALYZING ENGINE</span>
    </div>

    <h1>找到專屬你的每日營養搭配</h1>
    <p class="subtitle">回答 8 個簡單問題<br>為你量身推薦最適合的燈泡菌 + 馬甲光纖飲組合</p>
    <div class="tag-row">
      <span class="tag">體態管理</span>
      <span class="tag">腸道保健</span>
      <span class="tag">美容養顏</span>
      <span class="tag">精神專注</span>
      <span class="tag">舒壓好眠</span>
    </div>
    <button class="btn-start" onclick="startQuiz()">開始測驗</button>
    <p class="note">約 1 分鐘即可完成</p>
  </div>

  <!-- ═══ Quiz Page ═══ -->
  <div class="hidden" id="page-quiz">
    <div class="progress-wrapper">
      <div class="progress-bar-bg">
        <div class="progress-bar-fill" id="progress-fill"></div>
      </div>
      <div class="progress-text" id="progress-text"></div>
    </div>
    <div class="question-card" id="question-card"></div>
  </div>

  <!-- ═══ Loading Page ═══ -->
  <div class="hidden" id="page-loading">
    <div class="loading-card">
      <div class="loading-gears">
        <div class="gear gear-lg">
          <svg class="gear-svg" viewBox="0 0 100 100">
            <path d="M50 10 L54 10 L56 2 L60 2 L62 10 L66 12 L72 6 L76 8 L74 16 L78 20 L86 18 L88 22 L80 26 L82 32 L90 34 L90 38 L82 40 L82 46 L90 50 L88 54 L80 52 L78 58 L84 64 L80 68 L74 62 L68 66 L72 74 L68 76 L62 70 L58 72 L58 80 L54 80 L52 72 L46 72 L44 80 L40 80 L40 72 L34 70 L30 76 L26 74 L30 66 L24 62 L18 68 L16 64 L22 58 L18 52 L10 54 L10 50 L18 46 L18 40 L10 38 L10 34 L18 32 L20 26 L12 22 L14 18 L22 20 L26 16 L24 8 L28 6 L34 12 L38 10 L38 2 L42 2 L44 10 Z"
                  fill="#c06cd4" opacity="0.9"/>
            <circle cx="50" cy="50" r="18" fill="white"/>
            <circle cx="50" cy="50" r="12" fill="#f3e5f5"/>
          </svg>
        </div>
        <div class="gear gear-md">
          <svg class="gear-svg" viewBox="0 0 100 100">
            <path d="M50 12 L55 12 L57 4 L63 4 L65 12 L70 15 L77 8 L82 12 L76 20 L80 26 L88 26 L89 32 L81 34 L82 40 L90 44 L87 50 L80 48 L77 54 L82 62 L78 66 L72 60 L66 64 L68 72 L62 74 L58 66 L52 68 L52 76 L46 76 L44 68 L38 66 L34 74 L28 72 L32 64 L26 60 L20 66 L16 62 L22 54 L18 48 L10 50 L8 44 L16 40 L16 34 L8 32 L10 26 L18 26 L22 20 L16 12 L22 8 L28 15 L34 12 L34 4 L40 4 L42 12 Z"
                  fill="#e8789a" opacity="0.9"/>
            <circle cx="50" cy="50" r="16" fill="white"/>
            <circle cx="50" cy="50" r="10" fill="#fce4ec"/>
          </svg>
        </div>
        <div class="gear gear-sm">
          <svg class="gear-svg" viewBox="0 0 100 100">
            <path d="M50 14 L56 14 L59 4 L66 6 L65 16 L72 20 L80 14 L84 20 L76 28 L78 36 L88 38 L88 46 L78 46 L76 54 L84 62 L78 68 L72 60 L64 64 L66 74 L58 76 L56 66 L48 66 L44 76 L36 74 L40 64 L32 60 L24 68 L18 62 L28 54 L24 46 L14 46 L14 38 L24 36 L26 28 L18 20 L24 14 L32 20 L38 16 L36 6 L44 4 L44 14 Z"
                  fill="#f39c12" opacity="0.9"/>
            <circle cx="50" cy="50" r="14" fill="white"/>
            <circle cx="50" cy="50" r="8"  fill="#fff8e1"/>
          </svg>
        </div>
      </div>
      <p class="loading-text">正在為你分析最佳搭配<span class="loading-dots"></span></p>
      <p class="loading-sub">齒輪運轉中，請稍候</p>
    </div>
  </div>

  <!-- ═══ Result Page ═══ -->
  <div class="hidden" id="page-result"></div>

</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script>
const API_URL = 'api.php';
const sessionId = crypto.randomUUID ? crypto.randomUUID() : Date.now().toString(36) + Math.random().toString(36).slice(2);

const questions = [
  {
    id: 'gender',
    title: '你的性別是？',
    options: [
      { icon: '\u{1F468}', text: '男性', value: 'male' },
      { icon: '\u{1F469}', text: '女性', value: 'female' }
    ]
  },
  {
    id: 'age',
    title: '你的年齡層？',
    options: [
      { icon: '\u{1F4DA}', text: '18 歲以下（學生／考生）', value: 'teen' },
      { icon: '\u{1F4BC}', text: '19 – 35 歲（青壯年）', value: 'young' },
      { icon: '\u{1F3E0}', text: '36 – 55 歲（熟齡）', value: 'middle' },
      { icon: '\u{1F33F}', text: '55 歲以上（銀髮族）', value: 'senior' }
    ]
  },
  {
    id: 'goal',
    title: '你最在意的健康目標是？',
    options: [
      { icon: '\u{1F525}', text: '想瘦身／控制體態', value: 'slim' },
      { icon: '\u{1FAE7}', text: '改善消化、排便順暢', value: 'digest' },
      { icon: '\u{26A1}', text: '提升精神與專注力', value: 'energy' },
      { icon: '\u{1F319}', text: '改善睡眠品質、舒壓', value: 'sleep' },
      { icon: '\u{2728}', text: '養顏美容、好氣色', value: 'beauty' },
      { icon: '\u{1F4AA}', text: '增強體力與免疫力', value: 'immunity' }
    ]
  },
  {
    id: 'diet',
    title: '你的飲食習慣偏向？',
    options: [
      { icon: '\u{1F371}', text: '經常外食，飲食不規律', value: 'eating_out' },
      { icon: '\u{1F35E}', text: '愛吃澱粉、甜食，難以控制', value: 'carbs' },
      { icon: '\u{1F957}', text: '飲食均衡，想加強營養補充', value: 'balanced' },
      { icon: '\u{1F3CB}\u{FE0F}', text: '有在健身，注重蛋白質攝取', value: 'fitness' }
    ]
  },
  {
    id: 'gut',
    title: '你的腸胃狀況如何？',
    options: [
      { icon: '\u{1F623}', text: '經常脹氣、消化不良', value: 'bloat' },
      { icon: '\u{1F624}', text: '排便不順暢，容易便秘', value: 'constipation' },
      { icon: '\u{1F630}', text: '腸胃敏感，容易拉肚子', value: 'sensitive' },
      { icon: '\u{1F60A}', text: '還算正常，沒什麼大問題', value: 'normal' }
    ]
  },
  {
    id: 'sleep',
    title: '你的睡眠與壓力狀態？',
    options: [
      { icon: '\u{1F989}', text: '經常熬夜，睡眠品質差', value: 'poor' },
      { icon: '\u{1F629}', text: '壓力大，難以放鬆入睡', value: 'stressed' },
      { icon: '\u{1F971}', text: '睡眠還可以，但白天容易疲倦', value: 'tired' },
      { icon: '\u{1F634}', text: '睡眠正常，精神不錯', value: 'good' }
    ]
  },
  {
    id: 'skin',
    title: '你的外在／肌膚困擾？',
    options: [
      { icon: '\u{1F311}', text: '膚色暗沉、氣色差', value: 'dull' },
      { icon: '\u{1F486}', text: '保養品吸收差、膚況不穩定', value: 'unstable' },
      { icon: '\u{1FA7A}', text: '女性私密處容易不舒服', value: 'intimate' },
      { icon: '\u{1F44C}', text: '沒有特別困擾', value: 'none' }
    ]
  },
  {
    id: 'exercise',
    title: '你有運動習慣嗎？',
    options: [
      { icon: '\u{1F6CB}\u{FE0F}', text: '幾乎不運動', value: 'none' },
      { icon: '\u{1F6B6}', text: '偶爾運動（每週 1-2 次）', value: 'light' },
      { icon: '\u{1F3C3}', text: '規律運動／健身（每週 3 次以上）', value: 'regular' },
      { icon: '\u{1F525}', text: '高強度運動或體力勞動者', value: 'intense' }
    ]
  }
];

let currentQ = 0;
let answers = {};

function startQuiz() {
  document.getElementById('page-landing').classList.add('hidden');
  document.getElementById('page-quiz').classList.remove('hidden');
  renderQuestion();
}

function renderQuestion() {
  const q = questions[currentQ];
  const total = questions.length;
  const pct = ((currentQ) / total) * 100;
  document.getElementById('progress-fill').style.width = pct + '%';
  document.getElementById('progress-text').textContent = (currentQ + 1) + ' / ' + total;

  const card = document.getElementById('question-card');
  const selected = answers[q.id] || null;

  let optionsHtml = q.options.map(function(o) {
    return '<button class="option-btn ' + (selected === o.value ? 'selected' : '') + '" data-qid="' + q.id + '" data-val="' + o.value + '">' +
      '<span class="option-icon">' + o.icon + '</span>' +
      '<span>' + o.text + '</span>' +
    '</button>';
  }).join('');

  card.innerHTML =
    '<span class="q-number">Q' + (currentQ + 1) + '</span>' +
    '<h2 class="q-title">' + q.title + '</h2>' +
    '<div class="options">' + optionsHtml + '</div>' +
    '<div class="nav-row">' +
      '<button class="btn-back ' + (currentQ === 0 ? 'hidden' : '') + '" id="btn-back">上一題</button>' +
      '<button class="btn-next ' + (selected ? 'active' : '') + '" id="btn-next">' +
        (currentQ === total - 1 ? '查看結果' : '下一題') +
      '</button>' +
    '</div>';

  // Event delegation
  card.querySelectorAll('.option-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var qid = this.dataset.qid;
      var val = this.dataset.val;
      answers[qid] = val;
      card.querySelectorAll('.option-btn').forEach(function(b) { b.classList.remove('selected'); });
      this.classList.add('selected');
      document.getElementById('btn-next').classList.add('active');
    });
  });

  document.getElementById('btn-next').addEventListener('click', nextQuestion);
  var backBtn = document.getElementById('btn-back');
  if (backBtn) backBtn.addEventListener('click', prevQuestion);

  card.style.animation = 'none';
  card.offsetHeight;
  card.style.animation = 'fadeIn 0.4s ease';
}

function prevQuestion() {
  if (currentQ > 0) { currentQ--; renderQuestion(); }
}

function nextQuestion() {
  if (!answers[questions[currentQ].id]) return;
  if (currentQ < questions.length - 1) {
    currentQ++;
    renderQuestion();
  } else {
    submitQuiz();
  }
}

// ─── Submit to Backend ──────────────────────────────────
async function submitQuiz() {
  // Show loading with gears
  document.getElementById('page-quiz').classList.add('hidden');
  document.getElementById('page-loading').classList.remove('hidden');

  const payload = {
    session_id: sessionId,
    gender:   answers.gender,
    age:      answers.age,
    goal:     answers.goal,
    diet:     answers.diet,
    gut:      answers.gut,
    sleep:    answers.sleep,
    skin:     answers.skin,
    exercise: answers.exercise
  };

  try {
    const res = await fetch(API_URL + '?action=submit', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const data = await res.json();

    if (!res.ok || !data.success) {
      throw new Error(data.error || 'Server error');
    }

    // Minimum 1.5s loading for effect
    await new Promise(function(r) { setTimeout(r, 1500); });

    showResult(data.result, data.submission_id);

  } catch (err) {
    console.error('Submit error:', err);
    showToast('連線失敗，請檢查網路後再試');
    // Fall back to quiz page
    document.getElementById('page-loading').classList.add('hidden');
    document.getElementById('page-quiz').classList.remove('hidden');
  }
}

function showResult(r, submissionId) {
  document.getElementById('page-loading').classList.add('hidden');
  var page = document.getElementById('page-result');
  page.classList.remove('hidden');

  var pillsHtml = r.pills.map(function(p) {
    return '<span class="pill pill-' + p.c + '"><span>' + p.n + '</span><span class="pill-count">' + p.q + '</span></span>';
  }).join('');

  var tagsHtml = r.tags.map(function(t) {
    return '<span class="suitable-tag">#' + t + '</span>';
  }).join('');

  page.innerHTML =
    '<div class="result-card">' +
      '<span class="result-badge">你的專屬推薦</span>' +
      '<h2 class="result-title">' + r.name + '</h2>' +
      '<p class="result-code">' + r.code + ' ｜ 每日組合：' + r.combo + '</p>' +
      '<div class="combo-section">' +
        '<p class="combo-label">每日搭配內容</p>' +
        '<div class="combo-pills">' + pillsHtml + '</div>' +
      '</div>' +
      '<div class="result-desc">' + r.desc + '</div>' +
      '<div class="suitable-tags">' + tagsHtml + '</div>' +
      '<button class="btn-cta" id="btn-cta">立即選購組合</button><br>' +
      '<button class="btn-restart" id="btn-restart">重新測驗</button>' +
      '<p style="margin-top:16px;font-size:12px;color:#ccc;">測驗編號 #' + submissionId + '</p>' +
    '</div>';

  document.getElementById('btn-cta').addEventListener('click', function() {
    alert('即將為您導向商品頁面！');
  });
  document.getElementById('btn-restart').addEventListener('click', restart);
}

function restart() {
  currentQ = 0;
  answers = {};
  document.getElementById('page-result').classList.add('hidden');
  document.getElementById('page-landing').classList.remove('hidden');
}

function showToast(msg) {
  var t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(function() { t.classList.remove('show'); }, 3000);
}
</script>

</body>
</html>
