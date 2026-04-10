<?php
/**
 * Admin Dashboard - Quiz Results & Statistics
 * Login: ?token=maybeyou2026 (change in config.php)
 */
require_once __DIR__ . '/config.php';

$token = $_GET['token'] ?? ($_POST['token'] ?? '');
if ($token !== ADMIN_PASS) {
    http_response_code(401);
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Login</title>
    <style>
      body{font-family:sans-serif;display:flex;justify-content:center;align-items:center;min-height:100vh;background:#f5f0f8;}
      form{background:white;padding:40px;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.08);text-align:center;}
      input{display:block;margin:16px auto;padding:12px 20px;border:2px solid #eee;border-radius:10px;font-size:16px;width:250px;}
      button{background:linear-gradient(135deg,#e8789a,#c06cd4);color:white;border:none;padding:14px 40px;border-radius:50px;font-size:16px;cursor:pointer;}
    </style></head><body>
    <form method="GET"><h2>Admin Login</h2>
    <input type="password" name="token" placeholder="Enter admin password">
    <button type="submit">Login</button></form></body></html>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MaybeYou Quiz - Admin Dashboard</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@300;400;500;700&display=swap');
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    font-family: 'Noto Sans TC', sans-serif;
    background: #f5f0f8;
    color: #333;
    padding: 20px;
  }
  .container { max-width: 1200px; margin: 0 auto; }

  h1 {
    font-size: 28px;
    margin-bottom: 8px;
    background: linear-gradient(135deg, #e8789a, #c06cd4);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }
  .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
  }
  .header-right { font-size: 14px; color: #999; }

  /* Stat Cards */
  .stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 32px;
  }
  .stat-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.05);
  }
  .stat-card .label { font-size: 13px; color: #999; margin-bottom: 8px; }
  .stat-card .value { font-size: 36px; font-weight: 700; color: #7b2d8e; }
  .stat-card .sub { font-size: 13px; color: #aaa; margin-top: 4px; }

  /* Charts */
  .chart-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 32px;
  }
  @media (max-width: 768px) { .chart-row { grid-template-columns: 1fr; } }

  .chart-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.05);
  }
  .chart-card h3 {
    font-size: 16px;
    margin-bottom: 20px;
    color: #555;
  }

  /* Bar chart */
  .bar-chart { display: flex; flex-direction: column; gap: 10px; }
  .bar-row {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .bar-label {
    width: 100px;
    font-size: 13px;
    color: #666;
    text-align: right;
    flex-shrink: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .bar-track {
    flex: 1;
    height: 24px;
    background: #f5f0f8;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
  }
  .bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #e8789a, #c06cd4);
    border-radius: 12px;
    transition: width 0.6s ease;
    min-width: 2px;
  }
  .bar-count {
    width: 40px;
    font-size: 13px;
    font-weight: 600;
    color: #7b2d8e;
  }

  /* Table */
  .table-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.05);
    overflow-x: auto;
  }
  .table-card h3 { font-size: 16px; margin-bottom: 16px; color: #555; }
  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
  }
  th {
    background: #f5f0f8;
    padding: 10px 12px;
    text-align: left;
    font-weight: 600;
    color: #666;
    position: sticky;
    top: 0;
  }
  td {
    padding: 10px 12px;
    border-bottom: 1px solid #f0f0f0;
  }
  tr:hover td { background: #fdf8ff; }
  .badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
    background: #f3e5f5;
    color: #7b2d8e;
  }

  /* Pagination */
  .pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 20px;
  }
  .pagination button {
    padding: 8px 16px;
    border: 2px solid #eee;
    border-radius: 10px;
    background: white;
    cursor: pointer;
    font-family: inherit;
    transition: all 0.2s;
  }
  .pagination button:hover { border-color: #c06cd4; }
  .pagination button.active {
    background: linear-gradient(135deg, #e8789a, #c06cd4);
    color: white;
    border-color: transparent;
  }
  .pagination button:disabled { opacity: 0.3; pointer-events: none; }

  /* Export */
  .export-btn {
    background: linear-gradient(135deg, #e8789a, #c06cd4);
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 10px;
    cursor: pointer;
    font-family: inherit;
    font-size: 14px;
    font-weight: 500;
  }

  .loading { text-align: center; padding: 40px; color: #aaa; }
</style>
</head>
<body>

<div class="container">
  <div class="header">
    <div>
      <h1>MaybeYou Quiz Dashboard</h1>
      <p style="color:#999;font-size:14px;">問卷數據管理後台</p>
    </div>
    <div class="header-right">
      <button class="export-btn" onclick="exportCSV()">匯出 CSV</button>
    </div>
  </div>

  <!-- Stats -->
  <div class="stats-row" id="stats-row">
    <div class="stat-card"><div class="label">總填寫數</div><div class="value" id="stat-total">-</div></div>
    <div class="stat-card"><div class="label">今日填寫</div><div class="value" id="stat-today">-</div></div>
    <div class="stat-card"><div class="label">近 7 日</div><div class="value" id="stat-week">-</div></div>
    <div class="stat-card"><div class="label">最熱門結果</div><div class="value" id="stat-top" style="font-size:20px;">-</div></div>
  </div>

  <!-- Charts -->
  <div class="chart-row">
    <div class="chart-card">
      <h3>推薦結果分佈</h3>
      <div class="bar-chart" id="chart-results"><div class="loading">載入中...</div></div>
    </div>
    <div class="chart-card">
      <h3>健康目標分佈</h3>
      <div class="bar-chart" id="chart-goals"><div class="loading">載入中...</div></div>
    </div>
  </div>

  <div class="chart-row">
    <div class="chart-card">
      <h3>性別分佈</h3>
      <div class="bar-chart" id="chart-gender"><div class="loading">載入中...</div></div>
    </div>
    <div class="chart-card">
      <h3>年齡層分佈</h3>
      <div class="bar-chart" id="chart-age"><div class="loading">載入中...</div></div>
    </div>
  </div>

  <!-- Table -->
  <div class="table-card">
    <h3>最新填寫紀錄</h3>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>時間</th>
          <th>性別</th>
          <th>年齡</th>
          <th>目標</th>
          <th>飲食</th>
          <th>腸胃</th>
          <th>睡眠</th>
          <th>肌膚</th>
          <th>運動</th>
          <th>推薦結果</th>
        </tr>
      </thead>
      <tbody id="table-body">
        <tr><td colspan="11" class="loading">載入中...</td></tr>
      </tbody>
    </table>
    <div class="pagination" id="pagination"></div>
  </div>
</div>

<script>
const TOKEN = '<?php echo htmlspecialchars($token, ENT_QUOTES); ?>';
const API = 'api.php';
let currentPage = 1;

const labelMap = {
  gender:   { male: '男性', female: '女性' },
  age:      { teen: '18歲以下', young: '19-35歲', middle: '36-55歲', senior: '55歲以上' },
  goal:     { slim: '瘦身', digest: '消化', energy: '精神', sleep: '睡眠', beauty: '美容', immunity: '免疫' },
  diet:     { eating_out: '外食', carbs: '澱粉控', balanced: '均衡', fitness: '健身' },
  gut:      { bloat: '脹氣', constipation: '便秘', sensitive: '敏感', normal: '正常' },
  sleep:    { poor: '品質差', stressed: '壓力大', tired: '易疲倦', good: '正常' },
  skin:     { dull: '暗沉', unstable: '不穩定', intimate: '私密', none: '無' },
  exercise: { none: '不運動', light: '偶爾', regular: '規律', intense: '高強度' }
};

function t(category, value) {
  return (labelMap[category] && labelMap[category][value]) || value;
}

// Load stats
async function loadStats() {
  try {
    const res = await fetch(API + '?action=stats&token=' + TOKEN);
    const data = await res.json();

    document.getElementById('stat-total').textContent = data.total.toLocaleString();
    document.getElementById('stat-today').textContent = data.today.toLocaleString();
    document.getElementById('stat-week').textContent = data.this_week.toLocaleString();

    if (data.results.length > 0) {
      document.getElementById('stat-top').textContent = data.results[0].result_code + ' ' + data.results[0].result_name;
    }

    renderBarChart('chart-results', data.results.map(function(r) {
      return { label: r.result_code + ' ' + r.result_name, count: r.cnt };
    }));

    renderBarChart('chart-goals', data.goal.map(function(r) {
      return { label: t('goal', r.goal), count: r.cnt };
    }));

    renderBarChart('chart-gender', data.gender.map(function(r) {
      return { label: t('gender', r.gender), count: r.cnt };
    }));

    renderBarChart('chart-age', data.age.map(function(r) {
      return { label: t('age', r.age), count: r.cnt };
    }));

  } catch (err) {
    console.error('Stats error:', err);
  }
}

function renderBarChart(containerId, items) {
  var container = document.getElementById(containerId);
  if (!items.length) {
    container.innerHTML = '<div class="loading">尚無資料</div>';
    return;
  }
  var max = Math.max.apply(null, items.map(function(i) { return Number(i.count); }));
  container.innerHTML = items.map(function(item) {
    var pct = max > 0 ? (item.count / max * 100) : 0;
    return '<div class="bar-row">' +
      '<span class="bar-label" title="' + item.label + '">' + item.label + '</span>' +
      '<div class="bar-track"><div class="bar-fill" style="width:' + pct + '%"></div></div>' +
      '<span class="bar-count">' + item.count + '</span>' +
    '</div>';
  }).join('');
}

// Load results table
async function loadResults(page) {
  currentPage = page || 1;
  try {
    const res = await fetch(API + '?action=results&token=' + TOKEN + '&page=' + currentPage);
    const data = await res.json();

    var tbody = document.getElementById('table-body');
    if (!data.data.length) {
      tbody.innerHTML = '<tr><td colspan="11" class="loading">尚無資料</td></tr>';
      return;
    }

    tbody.innerHTML = data.data.map(function(r) {
      return '<tr>' +
        '<td>' + r.id + '</td>' +
        '<td>' + r.created_at + '</td>' +
        '<td>' + t('gender', r.gender) + '</td>' +
        '<td>' + t('age', r.age) + '</td>' +
        '<td>' + t('goal', r.goal) + '</td>' +
        '<td>' + t('diet', r.diet) + '</td>' +
        '<td>' + t('gut', r.gut) + '</td>' +
        '<td>' + t('sleep', r.sleep_state) + '</td>' +
        '<td>' + t('skin', r.skin) + '</td>' +
        '<td>' + t('exercise', r.exercise) + '</td>' +
        '<td><span class="badge">' + r.result_code + ' ' + r.result_name + '</span></td>' +
      '</tr>';
    }).join('');

    // Pagination
    var pag = document.getElementById('pagination');
    var pages = data.pages;
    var html = '<button ' + (currentPage <= 1 ? 'disabled' : '') + ' onclick="loadResults(' + (currentPage - 1) + ')">上一頁</button>';
    for (var i = 1; i <= Math.min(pages, 10); i++) {
      html += '<button class="' + (i === currentPage ? 'active' : '') + '" onclick="loadResults(' + i + ')">' + i + '</button>';
    }
    html += '<button ' + (currentPage >= pages ? 'disabled' : '') + ' onclick="loadResults(' + (currentPage + 1) + ')">下一頁</button>';
    pag.innerHTML = html;

  } catch (err) {
    console.error('Results error:', err);
  }
}

// Export CSV
async function exportCSV() {
  try {
    var allData = [];
    var page = 1;
    var pages = 1;
    while (page <= pages) {
      var res = await fetch(API + '?action=results&token=' + TOKEN + '&page=' + page);
      var data = await res.json();
      allData = allData.concat(data.data);
      pages = data.pages;
      page++;
    }

    var headers = ['ID','時間','性別','年齡','目標','飲食','腸胃','睡眠','肌膚','運動','結果代碼','結果名稱','IP'];
    var csv = '\uFEFF' + headers.join(',') + '\n';
    allData.forEach(function(r) {
      csv += [r.id, r.created_at, t('gender',r.gender), t('age',r.age), t('goal',r.goal),
              t('diet',r.diet), t('gut',r.gut), t('sleep',r.sleep_state), t('skin',r.skin),
              t('exercise',r.exercise), r.result_code, r.result_name, r.ip_address]
              .map(function(v) { return '"' + (v||'').toString().replace(/"/g,'""') + '"'; }).join(',') + '\n';
    });

    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'quiz_results_' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
    URL.revokeObjectURL(url);
  } catch (err) {
    alert('匯出失敗: ' + err.message);
  }
}

// Init
loadStats();
loadResults(1);
</script>

</body>
</html>
