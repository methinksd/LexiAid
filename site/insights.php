<?php
// insights.php
// Returns JSON analytics for LexiAid Study Insights dashboard
// Compatible with demo mode for testing

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// --- CONFIG ---
require_once __DIR__ . '/config/database.php';

// Use demo user if no user_id provided (for testing purposes)
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 1;

// --- PERIOD FILTER ---
$period = isset($_GET['period']) ? $_GET['period'] : 'weekly';
$period_sql = [
    'weekly' => 'INTERVAL 7 DAY',
    'monthly' => 'INTERVAL 1 MONTH', 
    'semester' => 'INTERVAL 6 MONTH',
];
$interval = isset($period_sql[$period]) ? $period_sql[$period] : $period_sql['weekly'];

try {
    $conn = getDbConnection();
} catch (Exception $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// --- STUDY TIME DISTRIBUTION (by day) ---
$studyTime = [
    'labels' => ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'],
    'values' => array_fill(0, 7, 0)
];
// Calculate study time based on task completion dates and durations
$sql = "SELECT DAYOFWEEK(updated_at) as dow, COUNT(*) * 2 as hours 
        FROM tasks 
        WHERE user_id=? AND completed=1 AND updated_at >= NOW() - $interval 
        GROUP BY dow";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $i = ((int)$row['dow'] + 5) % 7; // MySQL: 1=Sunday, 7=Saturday; shift to 0=Monday
        $studyTime['values'][$i] = round((float)$row['hours'], 1);
    }
    $stmt->close();
}

// --- TIME BY SUBJECT (pie) ---
$subjectPie = [
    'labels' => [],
    'values' => []
];
$sql = "SELECT category, COUNT(*) as cnt 
        FROM tasks 
        WHERE user_id=? AND completed=1 AND updated_at >= NOW() - $interval 
        GROUP BY category";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $subjectPie['labels'][] = ucfirst($row['category']);
        $subjectPie['values'][] = (int)$row['cnt'];
    }
    $stmt->close();
}

// --- QUIZ PERFORMANCE (line, per topic) ---
$quizPerformance = [
    'labels' => [],
    'datasets' => []
];
$sql = "SELECT topic, DATE_FORMAT(completed_at, '%Y-%u') as week, AVG(score) as avg_score 
        FROM quizzes 
        WHERE user_id=? AND completed_at >= NOW() - $interval 
        GROUP BY topic, week 
        ORDER BY week";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $topicData = [];
    $weeks = [];
    while ($row = $res->fetch_assoc()) {
        $weeks[$row['week']] = true;
        $topicData[$row['topic']][$row['week']] = round((float)$row['avg_score'], 1);
    }
    $quizPerformance['labels'] = array_keys($weeks);
    $colors = ['rgba(54,162,235,1)','rgba(255,99,132,1)','rgba(255,206,86,1)','rgba(75,192,192,1)','rgba(153,102,255,1)'];
    $i=0;
    foreach ($topicData as $topic => $weekScores) {
        $data = [];
        foreach ($quizPerformance['labels'] as $w) {
            $data[] = isset($weekScores[$w]) ? $weekScores[$w] : null;
        }
        $quizPerformance['datasets'][] = [
            'label' => $topic,
            'data' => $data,
            'borderColor' => $colors[$i%count($colors)],
            'backgroundColor' => $colors[$i%count($colors)].'33',
            'tension' => 0.3,
            'fill' => true
        ];
        $i++;
    }
    $stmt->close();
}

// --- TOPIC PERFORMANCE (radar) ---
$topicPerformance = [
    'labels' => [],
    'datasets' => []
];
$sql = "SELECT topic, AVG(score) as avg_score 
        FROM quizzes 
        WHERE user_id=? AND completed_at >= NOW() - $interval 
        GROUP BY topic";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $labels = [];
    $data = [];
    while ($row = $res->fetch_assoc()) {
        $labels[] = $row['topic'];
        $data[] = round((float)$row['avg_score'], 1);
    }
    $topicPerformance['labels'] = $labels;
    $topicPerformance['datasets'][] = [
        'label' => 'Your Performance',
        'data' => $data,
        'backgroundColor' => 'rgba(54,162,235,0.2)',
        'borderColor' => 'rgba(54,162,235,1)'
    ];
    $stmt->close();
}

// --- PRODUCTIVITY BY TIME OF DAY (demo) ---
$productivity = [
    'labels' => ['6-8 AM','8-11 AM','11-2 PM','2-5 PM','5-8 PM','8-11 PM','After 11 PM'],
    'values' => [72,95,83,78,88,75,62] // Placeholder, real data would require time tracking
];

// --- CONSISTENCY (demo) ---
$consistency = [
    'values' => [85, 15] // Placeholder: 85% consistent, 15% inconsistent
];

// --- RESOURCE UTILIZATION (demo) ---
$resourceUtilization = [
    'labels' => ['Casebooks','Practice Questions','Study Guides','Lecture Notes','Supplements','Online Resources'],
    'values' => [16,12,8,6,7,5] // Placeholder
];

// --- SUMMARY CARDS ---
$summary = [
    'totalStudyTime' => array_sum($studyTime['values']),
    'casesReviewed' => isset($subjectPie['values'][0]) ? array_sum($subjectPie['values']) : 0,
    'tasksCompleted' => 0,
    'tasksAssigned' => 0,
    'quizAverage' => 0
];

// Tasks completed/assigned
$sql = "SELECT COUNT(*) as total, SUM(completed=1) as completed 
        FROM tasks 
        WHERE user_id=? AND deadline >= NOW() - $interval";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $summary['tasksAssigned'] = (int)$row['total'];
        $summary['tasksCompleted'] = (int)$row['completed'];
    }
    $stmt->close();
}

// Quiz average
$sql = "SELECT AVG(score) as avg_score 
        FROM quizzes 
        WHERE user_id=? AND completed_at >= NOW() - $interval";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $summary['quizAverage'] = round((float)$row['avg_score']);
    }
    $stmt->close();
}

// --- WEAK AREAS ---
$weakAreas = [];
$sql = "SELECT topic, AVG(score) as avg_score 
        FROM quizzes 
        WHERE user_id=? AND completed_at >= NOW() - $interval 
        GROUP BY topic 
        ORDER BY avg_score ASC 
        LIMIT 3";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $weakAreas[] = [
            'topic' => $row['topic'],
            'score' => round((float)$row['avg_score'])
        ];
    }
    $stmt->close();
}

// --- RECOMMENDATIONS ---
$recommendations = [];
foreach ($weakAreas as $area) {
    $recommendations[] = [
        'title' => 'Focus on ' . $area['topic'],
        'text' => 'Schedule extra study time for this topic.',
        'action' => 'Add to Tasks'
    ];
}
if (count($weakAreas) === 0) {
    $recommendations[] = [
        'title' => 'Great job!',
        'text' => 'No weak topics detected. Keep up the good work!',
        'action' => null
    ];
}

// --- MOST USED RESOURCES (demo) ---
$resources = [
    ['name' => 'Constitutional Law Casebook', 'hours' => 12],
    ['name' => 'Torts Practice Questions', 'hours' => 8],
    ['name' => 'Criminal Law Study Guide', 'hours' => 6],
    ['name' => 'Property Supplements', 'hours' => 5]
];

// --- OUTPUT ---
echo json_encode([
    'summary' => $summary,
    'studyTime' => $studyTime,
    'subjectPie' => $subjectPie,
    'quizPerformance' => $quizPerformance,
    'topicPerformance' => $topicPerformance,
    'productivity' => $productivity,
    'consistency' => $consistency,
    'resourceUtilization' => $resourceUtilization,
    'weakAreas' => $weakAreas,
    'recommendations' => $recommendations,
    'resources' => $resources
]);
$conn->close();
