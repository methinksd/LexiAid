<?php
/**
 * Add sample data for insights testing
 * This script adds realistic sample data to demonstrate the insights functionality
 */

require_once __DIR__ . '/config/database.php';

function addSampleData() {
    try {
        $conn = getDbConnection();
        
        echo "🔄 Adding sample data for insights...\n";
        
        // Get or create demo user
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? LIMIT 1");
        $username = 'demo_student';
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $userId = $row['user_id'];
            echo "✅ Using existing demo user (ID: $userId)\n";
        } else {
            // Create demo user
            $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, full_name) VALUES (?, ?, ?, ?)");
            $email = 'demo@lexiaid.com';
            $passwordHash = password_hash('demo123', PASSWORD_DEFAULT);
            $fullName = 'Demo Student';
            $stmt->bind_param('ssss', $username, $email, $passwordHash, $fullName);
            $stmt->execute();
            $userId = $conn->insert_id;
            echo "✅ Created demo user (ID: $userId)\n";
        }
        $stmt->close();
        
        // Clear existing sample data for this user
        $conn->query("DELETE FROM tasks WHERE user_id = $userId AND title LIKE '%Sample%'");
        $conn->query("DELETE FROM quizzes WHERE user_id = $userId AND topic LIKE '%Test%'");
        
        // Add sample tasks with realistic dates and categories
        $sampleTasks = [
            // Recent completed tasks
            ['Sample: Constitutional Law Brief', 'Write analysis of Marbury v. Madison', 'brief', 'high', date('Y-m-d H:i:s', strtotime('-2 days')), 1],
            ['Sample: Contract Law Reading', 'Read chapters 1-3 of Contract Law textbook', 'reading', 'medium', date('Y-m-d H:i:s', strtotime('-3 days')), 1],
            ['Sample: Criminal Procedure Quiz', 'Complete online quiz on Miranda rights', 'quiz', 'medium', date('Y-m-d H:i:s', strtotime('-1 day')), 1],
            ['Sample: Property Law Research', 'Research adverse possession cases', 'research', 'low', date('Y-m-d H:i:s', strtotime('-4 days')), 1],
            ['Sample: Torts Essay', 'Write essay on negligence standards', 'essay', 'high', date('Y-m-d H:i:s', strtotime('-5 days')), 1],
            
            // Pending tasks
            ['Sample: Civil Procedure Study', 'Prepare for midterm exam', 'study', 'high', date('Y-m-d H:i:s', strtotime('+2 days')), 0],
            ['Sample: Evidence Law Review', 'Review hearsay exceptions', 'review', 'medium', date('Y-m-d H:i:s', strtotime('+3 days')), 0],
            ['Sample: Corporate Law Case Brief', 'Brief Delaware corporate law case', 'brief', 'medium', date('Y-m-d H:i:s', strtotime('+5 days')), 0],
        ];
        
        $taskStmt = $conn->prepare("INSERT INTO tasks (user_id, title, description, category, priority, deadline, completed) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($sampleTasks as $task) {
            $taskStmt->bind_param('isssssi', $userId, $task[0], $task[1], $task[2], $task[3], $task[4], $task[5]);
            $taskStmt->execute();
        }
        $taskStmt->close();
        echo "✅ Added " . count($sampleTasks) . " sample tasks\n";
        
        // Add sample quiz results with variety in scores and topics
        $sampleQuizzes = [
            // Constitutional Law quizzes (varying performance)
            ['Constitutional Law Test', 88.5, '{"questions": 20, "correct": 17, "topics": ["Due Process", "Equal Protection"]}', date('Y-m-d H:i:s', strtotime('-3 days'))],
            ['Constitutional Law Quiz', 92.0, '{"questions": 15, "correct": 14, "topics": ["First Amendment", "Commerce Clause"]}', date('Y-m-d H:i:s', strtotime('-1 week'))],
            ['Constitutional Law Final', 85.0, '{"questions": 50, "correct": 42, "topics": ["All Topics"]}', date('Y-m-d H:i:s', strtotime('-2 weeks'))],
            
            // Contract Law quizzes (strong performance)
            ['Contract Law Test', 94.5, '{"questions": 25, "correct": 23, "topics": ["Formation", "Consideration"]}', date('Y-m-d H:i:s', strtotime('-2 days'))],
            ['Contract Law Quiz', 89.0, '{"questions": 15, "correct": 13, "topics": ["Breach", "Remedies"]}', date('Y-m-d H:i:s', strtotime('-1 week'))],
            
            // Criminal Law quizzes (needs improvement)
            ['Criminal Law Test', 76.5, '{"questions": 30, "correct": 23, "topics": ["Mens Rea", "Actus Reus"]}', date('Y-m-d H:i:s', strtotime('-4 days'))],
            ['Criminal Procedure Quiz', 68.0, '{"questions": 20, "correct": 14, "topics": ["Miranda Rights", "Search & Seizure"]}', date('Y-m-d H:i:s', strtotime('-1 week'))],
            
            // Torts quizzes (average performance)
            ['Torts Test', 82.5, '{"questions": 25, "correct": 21, "topics": ["Negligence", "Strict Liability"]}', date('Y-m-d H:i:s', strtotime('-5 days'))],
            ['Torts Quiz', 78.0, '{"questions": 15, "correct": 12, "topics": ["Intentional Torts"]}', date('Y-m-d H:i:s', strtotime('-2 weeks'))],
            
            // Property Law quizzes
            ['Property Law Test', 91.0, '{"questions": 20, "correct": 18, "topics": ["Real Property", "Personal Property"]}', date('Y-m-d H:i:s', strtotime('-6 days'))],
        ];
        
        $quizStmt = $conn->prepare("INSERT INTO quizzes (user_id, topic, score, details, completed_at) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($sampleQuizzes as $quiz) {
            $quizStmt->bind_param('isdss', $userId, $quiz[0], $quiz[1], $quiz[2], $quiz[3]);
            $quizStmt->execute();
        }
        $quizStmt->close();
        echo "✅ Added " . count($sampleQuizzes) . " sample quiz results\n";
        
        // Display summary
        echo "\n📊 Sample Data Summary:\n";
        echo "User ID: $userId\n";
        
        $result = $conn->query("SELECT COUNT(*) as total, SUM(completed) as completed FROM tasks WHERE user_id = $userId");
        $taskSummary = $result->fetch_assoc();
        echo "Tasks: {$taskSummary['completed']} completed, " . ($taskSummary['total'] - $taskSummary['completed']) . " pending\n";
        
        $result = $conn->query("SELECT COUNT(*) as total, AVG(score) as avg_score FROM quizzes WHERE user_id = $userId");
        $quizSummary = $result->fetch_assoc();
        echo "Quizzes: {$quizSummary['total']} completed, " . round($quizSummary['avg_score'], 1) . "% average score\n";
        
        $conn->close();
        echo "\n✅ Sample data added successfully!\n";
        echo "🌐 You can now test the insights at: http://localhost:8080/insights.html\n";
        
    } catch (Exception $e) {
        echo "❌ Error adding sample data: " . $e->getMessage() . "\n";
        return false;
    }
    
    return true;
}

// Run if called directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    addSampleData();
}
?>
