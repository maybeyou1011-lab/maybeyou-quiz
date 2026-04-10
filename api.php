<?php
/**
 * Quiz API Backend
 * POST /api.php?action=submit   - Submit quiz answers
 * GET  /api.php?action=stats    - Get statistics (admin)
 * GET  /api.php?action=results  - Get all results (admin)
 */
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'submit':
        handleSubmit();
        break;
    case 'stats':
        handleStats();
        break;
    case 'results':
        handleResults();
        break;
    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}

// ─── Submit Quiz ────────────────────────────────────────
function handleSubmit() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['error' => 'POST required'], 405);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        jsonResponse(['error' => 'Invalid JSON'], 400);
    }

    $fields = ['gender', 'age', 'goal', 'diet', 'gut', 'sleep', 'skin', 'exercise'];
    $answers = [];
    foreach ($fields as $f) {
        if (empty($input[$f])) {
            jsonResponse(['error' => "Missing field: $f"], 400);
        }
        $answers[$f] = substr(trim($input[$f]), 0, 20);
    }

    // Calculate result on server side
    $result = calculateResult($answers);

    // Store in database
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO quiz_submissions
            (session_id, gender, age, goal, diet, gut, sleep_state, skin, exercise, result_code, result_name, ip_address, user_agent)
            VALUES (:sid, :gender, :age, :goal, :diet, :gut, :sleep_state, :skin, :exercise, :code, :name, :ip, :ua)");

        $stmt->execute([
            ':sid'         => $input['session_id'] ?? bin2hex(random_bytes(16)),
            ':gender'      => $answers['gender'],
            ':age'         => $answers['age'],
            ':goal'        => $answers['goal'],
            ':diet'        => $answers['diet'],
            ':gut'         => $answers['gut'],
            ':sleep_state' => $answers['sleep'],
            ':skin'        => $answers['skin'],
            ':exercise'    => $answers['exercise'],
            ':code'        => $result['code'],
            ':name'        => $result['name'],
            ':ip'          => $_SERVER['REMOTE_ADDR'] ?? '',
            ':ua'          => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
        ]);

        $submissionId = $db->lastInsertId();

    } catch (PDOException $e) {
        jsonResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
    }

    jsonResponse([
        'success'       => true,
        'submission_id' => (int)$submissionId,
        'result'        => $result,
    ]);
}

// ─── Calculate Result (Server-side) ─────────────────────
function calculateResult($a) {
    $results = getResultsMap();

    $gender   = $a['gender'];
    $age      = $a['age'];
    $goal     = $a['goal'];
    $diet     = $a['diet'];
    $gut      = $a['gut'];
    $sleep    = $a['sleep'];
    $skin     = $a['skin'];
    $exercise = $a['exercise'];

    // Age-based shortcuts
    if ($age === 'teen') {
        $code = ($exercise === 'none' || $exercise === 'light') ? 'D11' : 'D12';
        return $results[$code];
    }
    if ($age === 'senior') {
        return $results['D13'];
    }

    // Intimate concern
    if ($gender === 'female' && $skin === 'intimate') {
        return $results['D14'];
    }

    // Male paths
    if ($gender === 'male') {
        if ($goal === 'immunity' || ($goal === 'energy' && ($exercise === 'regular' || $exercise === 'intense'))) {
            return $results['D10'];
        }
        if ($goal === 'slim')   return $results['D2'];
        if ($goal === 'digest') return $results['D1'];
        return $results['D9'];
    }

    // Female paths
    if ($goal === 'slim') {
        return ($diet === 'carbs') ? $results['D2'] : $results['D4'];
    }
    if ($goal === 'digest') {
        return ($gut === 'normal') ? $results['D4'] : $results['D1'];
    }
    if ($goal === 'beauty') {
        if ($skin === 'dull' || $skin === 'unstable') {
            return ($diet === 'balanced' || $exercise === 'regular' || $exercise === 'intense')
                ? $results['D8'] : $results['D6'];
        }
        return $results['D5'];
    }
    if ($goal === 'sleep') {
        return ($skin === 'dull') ? $results['D5'] : $results['D7'];
    }
    if ($goal === 'energy') {
        return ($sleep === 'tired') ? $results['D3'] : $results['D5'];
    }
    if ($goal === 'immunity') {
        if ($exercise === 'regular' || $exercise === 'intense') return $results['D3'];
        if ($gut !== 'normal') return $results['D4'];
        return $results['D7'];
    }

    return $results['D7'];
}

function getResultsMap() {
    return [
        'D1'  => ['code'=>'D1',  'name'=>'幫助消化道機能', 'combo'=>'紅1 綠1 橙1 可2',
                   'desc'=>'針對腸胃不適、消化不良，透過紅色燈泡菌控制飲食搭配綠色調理腸胃，橙色幫助放鬆，加上可可馬甲光纖飲促進腸道好菌，全方位守護你的消化系統。',
                   'tags'=>['腸胃保健','消化不良','排便順暢'],
                   'pills'=>[['c'=>'red','n'=>'紅色','q'=>1],['c'=>'green','n'=>'綠色','q'=>1],['c'=>'orange','n'=>'橙色','q'=>1],['c'=>'cocoa','n'=>'可可','q'=>2]]],
        'D2'  => ['code'=>'D2',  'name'=>'閃電輕盈', 'combo'=>'紅2 綠1 米2 咖1',
                   'desc'=>'專為想瘦身的你量身打造！雙倍紅色燈泡菌狙擊多餘熱量，綠色提升代謝力，紫米馬甲光纖飲增加飽足感、減少亂吃，咖啡口味加速燃燒，讓你輕鬆邁向理想體態。',
                   'tags'=>['體態管理','控制食慾','加速代謝'],
                   'pills'=>[['c'=>'red','n'=>'紅色','q'=>2],['c'=>'green','n'=>'綠色','q'=>1],['c'=>'rice','n'=>'紫米','q'=>2],['c'=>'coffee','n'=>'咖啡','q'=>1]]],
        'D3'  => ['code'=>'D3',  'name'=>'促進新陳代謝', 'combo'=>'藍1 靛1 黃1 咖1 可1',
                   'desc'=>'藍色提振精神與專注力，靛色強化防護力，黃色鞏固骨骼基底，搭配咖啡與可可雙口味馬甲光纖飲，全面促進新陳代謝，讓身體循環更順暢。',
                   'tags'=>['新陳代謝','精神提振','循環順暢'],
                   'pills'=>[['c'=>'blue','n'=>'藍色','q'=>1],['c'=>'indigo','n'=>'靛色','q'=>1],['c'=>'yellow','n'=>'黃色','q'=>1],['c'=>'coffee','n'=>'咖啡','q'=>1],['c'=>'cocoa','n'=>'可可','q'=>1]]],
        'D4'  => ['code'=>'D4',  'name'=>'身體循環不卡卡', 'combo'=>'紅1 綠1 橙1 米2',
                   'desc'=>'紅色幫助控制飲食、綠色調理腸胃、橙色舒緩壓力助好眠，加上紫米馬甲光纖飲補充蛋白質維持體力，讓外食族也能維持良好的身體循環。',
                   'tags'=>['外食族','腸胃調理','體態維持'],
                   'pills'=>[['c'=>'red','n'=>'紅色','q'=>1],['c'=>'green','n'=>'綠色','q'=>1],['c'=>'orange','n'=>'橙色','q'=>1],['c'=>'rice','n'=>'紫米','q'=>2]]],
        'D5'  => ['code'=>'D5',  'name'=>'活力好氣色', 'combo'=>'藍1 綠1 橙1 米2',
                   'desc'=>'藍色提振精神、綠色增強抵抗力、橙色提升睡眠品質，搭配紫米馬甲光纖飲維持好氣色與活力，從內而外散發自然光采。',
                   'tags'=>['好氣色','活力充沛','由內而外'],
                   'pills'=>[['c'=>'blue','n'=>'藍色','q'=>1],['c'=>'green','n'=>'綠色','q'=>1],['c'=>'orange','n'=>'橙色','q'=>1],['c'=>'rice','n'=>'紫米','q'=>2]]],
        'D6'  => ['code'=>'D6',  'name'=>'日夜煥亮', 'combo'=>'紅1 靛1 紫1 米2',
                   'desc'=>'紅色控制飲食維持體態、靛色守護女性私密健康、紫色美白提亮氣色，搭配紫米馬甲光纖飲補充營養，白天亮麗、夜晚修護，24 小時持續煥亮。',
                   'tags'=>['美白提亮','私密保養','全天候'],
                   'pills'=>[['c'=>'red','n'=>'紅色','q'=>1],['c'=>'indigo','n'=>'靛色','q'=>1],['c'=>'purple','n'=>'紫色','q'=>1],['c'=>'rice','n'=>'紫米','q'=>2]]],
        'D7'  => ['code'=>'D7',  'name'=>'小資女調理', 'combo'=>'紅1 綠1 橙1 米1',
                   'desc'=>'精打細算的日常保養組合！紅色維持體態、綠色顧好腸胃、橙色幫助舒壓好眠，搭配一杯紫米馬甲光纖飲，簡單四步就能照顧好自己。',
                   'tags'=>['日常保養','高CP值','輕鬆維持'],
                   'pills'=>[['c'=>'red','n'=>'紅色','q'=>1],['c'=>'green','n'=>'綠色','q'=>1],['c'=>'orange','n'=>'橙色','q'=>1],['c'=>'rice','n'=>'紫米','q'=>1]]],
        'D8'  => ['code'=>'D8',  'name'=>'白富美保養', 'combo'=>'藍1 靛1 橙1 紫1 咖1',
                   'desc'=>'頂級全方位保養方案！藍色提振精神、靛色私密防護、橙色舒壓好眠、紫色美白提亮，加上咖啡馬甲光纖飲促進代謝，給你從頭到腳的全面呵護。',
                   'tags'=>['全方位','頂級保養','美白抗老'],
                   'pills'=>[['c'=>'blue','n'=>'藍色','q'=>1],['c'=>'indigo','n'=>'靛色','q'=>1],['c'=>'orange','n'=>'橙色','q'=>1],['c'=>'purple','n'=>'紫色','q'=>1],['c'=>'coffee','n'=>'咖啡','q'=>1]]],
        'D9'  => ['code'=>'D9',  'name'=>'精緻男調理', 'combo'=>'藍1 橙1 咖1',
                   'desc'=>'專為忙碌男性設計的精簡方案。藍色提振精神、增加專注力，橙色幫助舒壓放鬆，搭配咖啡馬甲光纖飲補充能量，三步搞定每日保養。',
                   'tags'=>['男性保養','專注力','精簡有效'],
                   'pills'=>[['c'=>'blue','n'=>'藍色','q'=>1],['c'=>'orange','n'=>'橙色','q'=>1],['c'=>'coffee','n'=>'咖啡','q'=>1]]],
        'D10' => ['code'=>'D10', 'name'=>'高富帥保養', 'combo'=>'藍1 綠1 橙1 黃1 咖1 可1',
                   'desc'=>'全面型男保養方案！藍色提神專注、綠色增強免疫、橙色助眠舒壓、黃色鞏固骨骼，搭配咖啡與可可雙倍馬甲光纖飲，打造由內而外的強健體魄。',
                   'tags'=>['全面保養','運動族','強健體魄'],
                   'pills'=>[['c'=>'blue','n'=>'藍色','q'=>1],['c'=>'green','n'=>'綠色','q'=>1],['c'=>'orange','n'=>'橙色','q'=>1],['c'=>'yellow','n'=>'黃色','q'=>1],['c'=>'coffee','n'=>'咖啡','q'=>1],['c'=>'cocoa','n'=>'可可','q'=>1]]],
        'D11' => ['code'=>'D11', 'name'=>'考生調理', 'combo'=>'黃1 綠1 橙1 米1 可1',
                   'desc'=>'專為考生設計！黃色鞏固骨骼發展、綠色增強抵抗力、橙色舒壓助好眠，搭配紫米和可可馬甲光纖飲補充體力與營養，讓你讀書有精神、考試更專注。',
                   'tags'=>['考生','專注力','抗壓'],
                   'pills'=>[['c'=>'yellow','n'=>'黃色','q'=>1],['c'=>'green','n'=>'綠色','q'=>1],['c'=>'orange','n'=>'橙色','q'=>1],['c'=>'rice','n'=>'紫米','q'=>1],['c'=>'cocoa','n'=>'可可','q'=>1]]],
        'D12' => ['code'=>'D12', 'name'=>'孩童成長', 'combo'=>'黃1 綠1 橙1 可1',
                   'desc'=>'為成長中的孩子打好基礎！黃色幫助骨骼發展、綠色調整腸胃增進食慾、橙色提升睡眠品質，搭配好喝的可可馬甲光纖飲，健康成長每一天。',
                   'tags'=>['孩童','骨骼發展','成長期'],
                   'pills'=>[['c'=>'yellow','n'=>'黃色','q'=>1],['c'=>'green','n'=>'綠色','q'=>1],['c'=>'orange','n'=>'橙色','q'=>1],['c'=>'cocoa','n'=>'可可','q'=>1]]],
        'D13' => ['code'=>'D13', 'name'=>'銀髮族保健', 'combo'=>'黃1 綠1 橙1 米1',
                   'desc'=>'貼心守護長輩健康！黃色加固骨骼、綠色增強免疫抵抗力、橙色幫助舒壓好眠，搭配紫米馬甲光纖飲補充蛋白質維持肌力，讓每一天都充滿活力。',
                   'tags'=>['銀髮族','骨骼保健','免疫力'],
                   'pills'=>[['c'=>'yellow','n'=>'黃色','q'=>1],['c'=>'green','n'=>'綠色','q'=>1],['c'=>'orange','n'=>'橙色','q'=>1],['c'=>'rice','n'=>'紫米','q'=>1]]],
        'D14' => ['code'=>'D14', 'name'=>'夫妻生活調理', 'combo'=>'靛2 藍2 綠2 咖2 可2',
                   'desc'=>'為夫妻雙方設計的共同保養方案。靛色守護私密健康、藍色提振精神活力、綠色增強體力，搭配咖啡與可可雙口味馬甲光纖飲，讓兩人都維持最佳狀態。',
                   'tags'=>['夫妻保養','私密健康','活力充沛'],
                   'pills'=>[['c'=>'indigo','n'=>'靛色','q'=>2],['c'=>'blue','n'=>'藍色','q'=>2],['c'=>'green','n'=>'綠色','q'=>2],['c'=>'coffee','n'=>'咖啡','q'=>2],['c'=>'cocoa','n'=>'可可','q'=>2]]],
    ];
}

// ─── Admin: Stats ───────────────────────────────────────
function handleStats() {
    checkAdmin();
    $db = getDB();

    // Total submissions
    $total = $db->query("SELECT COUNT(*) FROM quiz_submissions")->fetchColumn();

    // Today
    $today = $db->query("SELECT COUNT(*) FROM quiz_submissions WHERE DATE(created_at) = CURDATE()")->fetchColumn();

    // This week
    $week = $db->query("SELECT COUNT(*) FROM quiz_submissions WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetchColumn();

    // Result distribution
    $dist = $db->query("SELECT result_code, result_name, COUNT(*) as cnt
        FROM quiz_submissions GROUP BY result_code, result_name ORDER BY cnt DESC")->fetchAll();

    // Gender distribution
    $genderDist = $db->query("SELECT gender, COUNT(*) as cnt
        FROM quiz_submissions GROUP BY gender")->fetchAll();

    // Age distribution
    $ageDist = $db->query("SELECT age, COUNT(*) as cnt
        FROM quiz_submissions GROUP BY age ORDER BY cnt DESC")->fetchAll();

    // Goal distribution
    $goalDist = $db->query("SELECT goal, COUNT(*) as cnt
        FROM quiz_submissions GROUP BY goal ORDER BY cnt DESC")->fetchAll();

    // Daily trend (last 30 days)
    $trend = $db->query("SELECT DATE(created_at) as date, COUNT(*) as cnt
        FROM quiz_submissions WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at) ORDER BY date")->fetchAll();

    jsonResponse([
        'total'       => (int)$total,
        'today'       => (int)$today,
        'this_week'   => (int)$week,
        'results'     => $dist,
        'gender'      => $genderDist,
        'age'         => $ageDist,
        'goal'        => $goalDist,
        'daily_trend' => $trend,
    ]);
}

// ─── Admin: All Results ─────────────────────────────────
function handleResults() {
    checkAdmin();
    $db = getDB();

    $page  = max(1, (int)($_GET['page'] ?? 1));
    $limit = 50;
    $offset = ($page - 1) * $limit;

    $total = $db->query("SELECT COUNT(*) FROM quiz_submissions")->fetchColumn();

    $stmt = $db->prepare("SELECT * FROM quiz_submissions ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    jsonResponse([
        'total'    => (int)$total,
        'page'     => $page,
        'per_page' => $limit,
        'pages'    => ceil($total / $limit),
        'data'     => $rows,
    ]);
}

// ─── Helpers ────────────────────────────────────────────
function checkAdmin() {
    $token = $_GET['token'] ?? ($_SERVER['HTTP_X_ADMIN_TOKEN'] ?? '');
    if ($token !== ADMIN_PASS) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
