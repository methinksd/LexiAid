// LexiAid Quizzes Frontend Script

document.addEventListener('DOMContentLoaded', function() {
    let currentQuizData = {};
    const DEMO_USER_ID = 1; // Using demo user

    // Initialize the quizzes page
    function initQuizzes() {
        loadQuizData();
        setupEventListeners();
    }

    // Load quiz performance data from the backend
    async function loadQuizData() {
        try {
            console.log('Loading quiz data...');
            
            const response = await fetch(`quizzes.php?user_id=${DEMO_USER_ID}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('Quiz data response:', data);

            if (data.status === 'success') {
                currentQuizData = data;
                displayQuizStatistics(data.statistics);
                displayQuizHistory(data.recent_history);
            } else {
                throw new Error(data.message || 'Failed to load quiz data');
            }
        } catch (error) {
            console.error('Error loading quiz data:', error);
            showQuizError('Failed to load quiz data. Please refresh the page.');
        }
    }

    // Display quiz statistics
    function displayQuizStatistics(stats) {
        try {
            // Update statistics cards if they exist
            const totalQuizzesEl = document.querySelector('[data-stat="total-quizzes"]');
            const avgScoreEl = document.querySelector('[data-stat="avg-score"]');
            const highestScoreEl = document.querySelector('[data-stat="highest-score"]');
            const passedQuizzesEl = document.querySelector('[data-stat="passed-quizzes"]');

            if (totalQuizzesEl) totalQuizzesEl.textContent = stats.total_quizzes || 0;
            if (avgScoreEl) avgScoreEl.textContent = stats.average_score ? Math.round(stats.average_score) + '%' : '0%';
            if (highestScoreEl) highestScoreEl.textContent = stats.highest_score ? Math.round(stats.highest_score) + '%' : '0%';
            if (passedQuizzesEl) passedQuizzesEl.textContent = stats.quizzes_passed || 0;

            console.log('Statistics updated:', stats);
        } catch (error) {
            console.error('Error displaying statistics:', error);
        }
    }

    // Display quiz history
    function displayQuizHistory(history) {
        try {
            const historyContainer = document.getElementById('quiz-history-container');
            if (!historyContainer) return;

            if (!history || history.length === 0) {
                historyContainer.innerHTML = '<div class="alert alert-info">No quiz history found.</div>';
                return;
            }

            const historyHTML = history.map(quiz => createQuizHistoryItem(quiz)).join('');
            historyContainer.innerHTML = historyHTML;

            console.log('Quiz history updated:', history);
        } catch (error) {
            console.error('Error displaying quiz history:', error);
        }
    }

    // Create HTML for a quiz history item
    function createQuizHistoryItem(quiz) {
        const performanceBadge = getPerformanceBadge(quiz.performance, quiz.score);
        const date = formatDate(quiz.completed_at);

        return `
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">${quiz.topic}</h6>
                            <p class="card-text text-muted mb-0">Completed on ${date}</p>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge badge-lg ${performanceBadge.class} mr-2">${Math.round(quiz.score)}%</span>
                            <span class="badge ${performanceBadge.statusClass}">${performanceBadge.status}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Submit a new quiz result
    async function submitQuizResult(quizData) {
        try {
            console.log('Submitting quiz result:', quizData);

            const response = await fetch('quizzes.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    user_id: DEMO_USER_ID,
                    topic: quizData.topic,
                    score: quizData.score,
                    details: quizData.details || null,
                    task_id: quizData.task_id || null
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('Submit quiz response:', data);

            if (data.status === 'success') {
                showQuizSuccess('Quiz result recorded successfully!');
                loadQuizData(); // Reload quiz data
                return data;
            } else {
                throw new Error(data.message || 'Failed to submit quiz result');
            }
        } catch (error) {
            console.error('Error submitting quiz result:', error);
            showQuizError('Failed to submit quiz result: ' + error.message);
            throw error;
        }
    }

    // Setup event listeners
    function setupEventListeners() {
        // Example: Quiz completion simulation
        const simulateQuizButtons = document.querySelectorAll('[data-action="simulate-quiz"]');
        simulateQuizButtons.forEach(button => {
            button.addEventListener('click', function() {
                const topic = this.getAttribute('data-topic') || 'Sample Quiz';
                simulateQuizCompletion(topic);
            });
        });

        // Refresh data button
        const refreshButton = document.getElementById('refresh-quiz-data');
        if (refreshButton) {
            refreshButton.addEventListener('click', function() {
                loadQuizData();
            });
        }
    }

    // Simulate quiz completion for demo purposes
    function simulateQuizCompletion(topic) {
        const score = Math.floor(Math.random() * 40) + 60; // Random score between 60-100
        const details = {
            questions: 20,
            correct: Math.floor((score / 100) * 20),
            time_taken: Math.floor(Math.random() * 20) + 10, // 10-30 minutes
            topics: [topic, 'General Knowledge']
        };

        const quizData = {
            topic: topic,
            score: score,
            details: details
        };

        submitQuizResult(quizData);
    }

    // Helper functions
    function getPerformanceBadge(performance, score) {
        switch (performance) {
            case 'excellent':
                return {
                    class: 'badge-success',
                    statusClass: 'badge-success',
                    status: 'Excellent'
                };
            case 'pass':
                return {
                    class: 'badge-primary',
                    statusClass: 'badge-primary', 
                    status: 'Passed'
                };
            default:
                return {
                    class: 'badge-warning',
                    statusClass: 'badge-warning',
                    status: 'Needs Improvement'
                };
        }
    }

    function formatDate(dateString) {
        if (!dateString) return 'Unknown date';
        
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function showQuizSuccess(message) {
        console.log('Success:', message);
        // You can implement a toast notification here
        alert(message); // Temporary alert
    }

    function showQuizError(message) {
        console.error('Error:', message);
        // You can implement a toast notification here  
        alert('Error: ' + message); // Temporary alert
    }

    // Public functions for testing
    window.quizManager = {
        loadQuizData,
        submitQuizResult,
        simulateQuizCompletion,
        currentData: () => currentQuizData
    };

    // Initialize the quizzes module
    initQuizzes();
    console.log('LexiAid Quizzes module loaded successfully');
    console.log('Use quizManager.simulateQuizCompletion("Topic Name") to test quiz submission');
});
