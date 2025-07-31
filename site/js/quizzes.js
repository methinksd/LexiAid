// LexiAid Quizzes Frontend Script

document.addEventListener('DOMContentLoaded', function() {
    let currentQuizData = {};
    let activeQuiz = null;
    let currentQuestionIndex = 0;
    let userAnswers = [];
    let quizTimer = null;
    let timeRemaining = 0;
    const DEMO_USER_ID = 1; // Using demo user

    // Quiz questions data
    const quizQuestions = {
        'constitutional-law': {
            title: 'Constitutional Law Fundamentals',
            category: 'Constitutional Law',
            timeLimit: 15 * 60, // 15 minutes in seconds
            questions: [
                {
                    question: "What principle was established in Marbury v. Madison (1803)?",
                    options: [
                        "Separation of powers",
                        "Judicial review", 
                        "Due process",
                        "Equal protection"
                    ],
                    correct: 1,
                    explanation: "Marbury v. Madison established the principle of judicial review, giving courts the power to declare laws unconstitutional."
                },
                {
                    question: "Which amendment guarantees freedom of speech?",
                    options: [
                        "First Amendment",
                        "Fourth Amendment", 
                        "Fifth Amendment",
                        "Fourteenth Amendment"
                    ],
                    correct: 0,
                    explanation: "The First Amendment protects freedom of speech, religion, press, assembly, and petition."
                },
                {
                    question: "What does the Equal Protection Clause require?",
                    options: [
                        "Equal voting rights for all citizens",
                        "Equal treatment under the law", 
                        "Equal economic opportunities",
                        "Equal educational funding"
                    ],
                    correct: 1,
                    explanation: "The Equal Protection Clause requires that similarly situated people be treated equally under the law."
                },
                {
                    question: "Which court case established the 'clear and present danger' test?",
                    options: [
                        "Gitlow v. New York",
                        "Schenck v. United States", 
                        "Brandenburg v. Ohio",
                        "Dennis v. United States"
                    ],
                    correct: 1,
                    explanation: "Schenck v. United States (1919) established the 'clear and present danger' test for speech restrictions."
                },
                {
                    question: "What is the highest level of constitutional scrutiny?",
                    options: [
                        "Rational basis review",
                        "Intermediate scrutiny", 
                        "Strict scrutiny",
                        "Compelling interest test"
                    ],
                    correct: 2,
                    explanation: "Strict scrutiny is the highest level of judicial review, requiring a compelling government interest and narrow tailoring."
                }
            ]
        },
        'contract-law': {
            title: 'Contract Formation & Terms',
            category: 'Contract Law',
            timeLimit: 12 * 60, // 12 minutes in seconds
            questions: [
                {
                    question: "What are the essential elements of a valid contract?",
                    options: [
                        "Offer, acceptance, consideration, capacity",
                        "Offer, acceptance, writing, signatures", 
                        "Agreement, money, witnesses, notarization",
                        "Intent, performance, damages, remedies"
                    ],
                    correct: 0,
                    explanation: "A valid contract requires offer, acceptance, consideration, and capacity of the parties."
                },
                {
                    question: "What is consideration in contract law?",
                    options: [
                        "Careful thought about the agreement",
                        "Something of value exchanged between parties", 
                        "Kindness and respect in negotiations",
                        "Time spent drafting the contract"
                    ],
                    correct: 1,
                    explanation: "Consideration is something of value (money, goods, services, or promises) exchanged between contracting parties."
                },
                {
                    question: "Which contracts must be in writing under the Statute of Frauds?",
                    options: [
                        "All contracts over $100",
                        "Contracts for services only", 
                        "Contracts for sale of land and goods over $500",
                        "Only employment contracts"
                    ],
                    correct: 2,
                    explanation: "The Statute of Frauds requires written contracts for land sales, goods over $500, and certain other specified agreements."
                }
            ]
        },
        'criminal-law': {
            title: 'Criminal Law Principles',
            category: 'Criminal Law',
            timeLimit: 18 * 60, // 18 minutes in seconds
            questions: [
                {
                    question: "What are the elements of a crime?",
                    options: [
                        "Intent and action only",
                        "Actus reus and mens rea", 
                        "Motive and opportunity",
                        "Evidence and witnesses"
                    ],
                    correct: 1,
                    explanation: "A crime requires both actus reus (guilty act) and mens rea (guilty mind/intent)."
                },
                {
                    question: "What was established in Miranda v. Arizona?",
                    options: [
                        "Right to remain silent during interrogation",
                        "Right to a speedy trial", 
                        "Protection against unreasonable search",
                        "Right to confront witnesses"
                    ],
                    correct: 0,
                    explanation: "Miranda v. Arizona established that suspects must be informed of their rights before custodial interrogation."
                }
            ]
        },
        'torts': {
            title: 'Torts & Liability',
            category: 'Torts',
            timeLimit: 14 * 60, // 14 minutes in seconds
            questions: [
                {
                    question: "What are the elements of negligence?",
                    options: [
                        "Duty, breach, causation, damages",
                        "Intent, action, harm, liability", 
                        "Care, mistake, injury, payment",
                        "Standard, violation, result, responsibility"
                    ],
                    correct: 0,
                    explanation: "Negligence requires proving duty of care, breach of that duty, causation, and damages."
                },
                {
                    question: "What is strict liability?",
                    options: [
                        "Liability requiring proof of negligence",
                        "Liability without proof of fault", 
                        "Liability only for intentional acts",
                        "Liability with maximum damage limits"
                    ],
                    correct: 1,
                    explanation: "Strict liability imposes responsibility regardless of fault, often applied to abnormally dangerous activities."
                }
            ]
        }
    };

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
            const passedQuizzesEl = document.querySelector('[data-stat="quizzes-passed"]');

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
            const historyTable = document.getElementById('quizHistoryTable');
            if (!historyTable) return;

            if (!history || history.length === 0) {
                historyTable.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            <i class="fa fa-info-circle"></i> No quiz history found. Take your first quiz to get started!
                        </td>
                    </tr>
                `;
                return;
            }

            const historyHTML = history.map(quiz => createQuizHistoryRow(quiz)).join('');
            historyTable.innerHTML = historyHTML;

            console.log('Quiz history updated:', history);
        } catch (error) {
            console.error('Error displaying quiz history:', error);
        }
    }

    // Create HTML for a quiz history row
    function createQuizHistoryRow(quiz) {
        const performanceBadge = getPerformanceBadge(quiz.performance, quiz.score);
        const date = formatDate(quiz.completed_at);

        return `
            <tr>
                <td><strong>${quiz.topic}</strong></td>
                <td><span class="badge badge-lg badge-primary">${Math.round(quiz.score)}%</span></td>
                <td><span class="badge ${performanceBadge.class} performance-badge">${performanceBadge.status}</span></td>
                <td><small class="text-muted">${date}</small></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="retakeQuiz('${quiz.topic}')">
                        <i class="fa fa-redo"></i> Retake
                    </button>
                </td>
            </tr>
        `;
    }

    // Start a quiz
    function startQuiz(quizType) {
        if (!quizQuestions[quizType]) {
            showQuizError('Quiz not found!');
            return;
        }

        activeQuiz = quizQuestions[quizType];
        currentQuestionIndex = 0;
        userAnswers = [];
        timeRemaining = activeQuiz.timeLimit;

        // Hide available quizzes and show quiz interface
        document.getElementById('availableQuizzes').style.display = 'none';
        document.getElementById('quizInterface').style.display = 'block';
        document.getElementById('quizResults').style.display = 'none';

        // Setup quiz interface
        document.getElementById('quizTitle').textContent = activeQuiz.title;
        document.getElementById('quizCategory').textContent = activeQuiz.category;

        // Start timer
        startQuizTimer();

        // Show first question
        showQuestion(0);
    }

    // Start quiz timer
    function startQuizTimer() {
        quizTimer = setInterval(() => {
            timeRemaining--;
            updateTimerDisplay();

            if (timeRemaining <= 0) {
                clearInterval(quizTimer);
                finishQuiz();
            }
        }, 1000);
    }

    // Update timer display
    function updateTimerDisplay() {
        const minutes = Math.floor(timeRemaining / 60);
        const seconds = timeRemaining % 60;
        document.getElementById('quizTimer').textContent = 
            `${minutes}:${seconds.toString().padStart(2, '0')}`;
        
        // Change color when time is running low
        const timerEl = document.getElementById('quizTimer');
        if (timeRemaining <= 60) {
            timerEl.style.color = '#dc3545'; // Red
        } else if (timeRemaining <= 300) {
            timerEl.style.color = '#ffc107'; // Yellow
        }
    }

    // Show a specific question
    function showQuestion(index) {
        if (!activeQuiz || index >= activeQuiz.questions.length) return;

        const question = activeQuiz.questions[index];
        currentQuestionIndex = index;

        // Update progress
        const progress = ((index + 1) / activeQuiz.questions.length) * 100;
        document.getElementById('quizProgress').style.width = progress + '%';
        document.getElementById('quizProgress').textContent = 
            `Question ${index + 1} of ${activeQuiz.questions.length}`;

        // Show question
        document.getElementById('questionText').textContent = question.question;

        // Create answer options
        const optionsContainer = document.getElementById('answerOptions');
        optionsContainer.innerHTML = question.options.map((option, i) => `
            <div class="answer-option" data-option="${i}">
                <input type="radio" name="answer" value="${i}" id="option${i}" style="margin-right: 10px;">
                <label for="option${i}" style="cursor: pointer; margin: 0; width: 100%;">${option}</label>
            </div>
        `).join('');

        // Add click handlers for answer options
        document.querySelectorAll('.answer-option').forEach(option => {
            option.addEventListener('click', function() {
                const optionIndex = this.getAttribute('data-option');
                selectAnswer(optionIndex);
            });
        });

        // Update navigation buttons
        document.getElementById('prevQuestionBtn').disabled = index === 0;
        document.getElementById('nextQuestionBtn').style.display = 
            index === activeQuiz.questions.length - 1 ? 'none' : 'inline-block';
        document.getElementById('submitQuizBtn').style.display = 
            index === activeQuiz.questions.length - 1 ? 'inline-block' : 'none';

        // Restore previous answer if any
        if (userAnswers[index] !== undefined) {
            selectAnswer(userAnswers[index]);
        }
    }

    // Select an answer
    function selectAnswer(optionIndex) {
        // Remove previous selections
        document.querySelectorAll('.answer-option').forEach(opt => {
            opt.classList.remove('selected');
        });

        // Mark new selection
        const selectedOption = document.querySelector(`[data-option="${optionIndex}"]`);
        selectedOption.classList.add('selected');
        
        // Check the radio button
        document.getElementById(`option${optionIndex}`).checked = true;

        // Store answer
        userAnswers[currentQuestionIndex] = parseInt(optionIndex);
    }

    // Navigate to next question
    function nextQuestion() {
        if (currentQuestionIndex < activeQuiz.questions.length - 1) {
            showQuestion(currentQuestionIndex + 1);
        }
    }

    // Navigate to previous question
    function prevQuestion() {
        if (currentQuestionIndex > 0) {
            showQuestion(currentQuestionIndex - 1);
        }
    }

    // Finish the quiz
    function finishQuiz() {
        if (quizTimer) {
            clearInterval(quizTimer);
        }

        // Calculate score
        let correctAnswers = 0;
        activeQuiz.questions.forEach((question, index) => {
            if (userAnswers[index] === question.correct) {
                correctAnswers++;
            }
        });

        const score = (correctAnswers / activeQuiz.questions.length) * 100;
        const timeTaken = activeQuiz.timeLimit - timeRemaining;

        // Show results
        showQuizResults(score, correctAnswers, timeTaken);

        // Submit to backend
        const quizData = {
            topic: activeQuiz.title,
            score: score,
            details: {
                questions: activeQuiz.questions.length,
                correct: correctAnswers,
                time_taken: timeTaken,
                answers: userAnswers
            }
        };

        submitQuizResult(quizData);
    }

    // Show quiz results
    function showQuizResults(score, correctAnswers, timeTaken) {
        // Hide quiz interface and show results
        document.getElementById('quizInterface').style.display = 'none';
        document.getElementById('quizResults').style.display = 'block';

        // Update results display
        document.getElementById('finalScore').textContent = Math.round(score) + '%';
        document.getElementById('correctCount').textContent = correctAnswers;
        document.getElementById('totalQuestions').textContent = activeQuiz.questions.length;
        
        const minutes = Math.floor(timeTaken / 60);
        const seconds = timeTaken % 60;
        document.getElementById('timeTaken').textContent = 
            `${minutes}:${seconds.toString().padStart(2, '0')}`;

        // Score message
        let message = '';
        if (score >= 90) {
            message = 'Excellent! Outstanding performance!';
        } else if (score >= 70) {
            message = 'Great job! You passed the quiz.';
        } else {
            message = 'Keep studying! You can do better next time.';
        }
        document.getElementById('scoreMessage').textContent = message;
    }

    // Return to quiz selection
    function backToQuizzes() {
        document.getElementById('quizInterface').style.display = 'none';
        document.getElementById('quizResults').style.display = 'none';
        document.getElementById('availableQuizzes').style.display = 'block';
        
        // Reset quiz state
        activeQuiz = null;
        currentQuestionIndex = 0;
        userAnswers = [];
        if (quizTimer) {
            clearInterval(quizTimer);
        }
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
        // Quiz start buttons
        document.querySelectorAll('.start-quiz').forEach(button => {
            button.addEventListener('click', function() {
                const quizType = this.getAttribute('data-quiz');
                startQuiz(quizType);
            });
        });

        // Take new quiz button
        const takeQuizBtn = document.getElementById('takeQuizBtn');
        if (takeQuizBtn) {
            takeQuizBtn.addEventListener('click', function() {
                // Scroll to available quizzes
                document.getElementById('availableQuizzes').scrollIntoView({ behavior: 'smooth' });
            });
        }

        // Quiz navigation buttons
        const nextBtn = document.getElementById('nextQuestionBtn');
        const prevBtn = document.getElementById('prevQuestionBtn');
        const submitBtn = document.getElementById('submitQuizBtn');

        if (nextBtn) {
            nextBtn.addEventListener('click', nextQuestion);
        }
        if (prevBtn) {
            prevBtn.addEventListener('click', prevQuestion);
        }
        if (submitBtn) {
            submitBtn.addEventListener('click', finishQuiz);
        }

        // Quiz results buttons
        const retakeBtn = document.getElementById('retakeQuizBtn');
        const backBtn = document.getElementById('backToQuizzesBtn');

        if (retakeBtn) {
            retakeBtn.addEventListener('click', function() {
                if (activeQuiz) {
                    startQuiz(getQuizTypeFromTitle(activeQuiz.title));
                }
            });
        }
        if (backBtn) {
            backBtn.addEventListener('click', backToQuizzes);
        }

        // Refresh data button (if exists)
        const refreshButton = document.getElementById('refresh-quiz-data');
        if (refreshButton) {
            refreshButton.addEventListener('click', function() {
                loadQuizData();
            });
        }
    }

    // Helper function to get quiz type from title
    function getQuizTypeFromTitle(title) {
        const mapping = {
            'Constitutional Law Fundamentals': 'constitutional-law',
            'Contract Formation & Terms': 'contract-law',
            'Criminal Law Principles': 'criminal-law',
            'Torts & Liability': 'torts'
        };
        return mapping[title] || 'constitutional-law';
    }

    // Retake quiz function (called from history table)
    window.retakeQuiz = function(topic) {
        // Find matching quiz type
        for (const [key, quiz] of Object.entries(quizQuestions)) {
            if (quiz.title === topic || quiz.category.includes(topic)) {
                startQuiz(key);
                return;
            }
        }
        showQuizError('Quiz not found for retake');
    };

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
                    status: 'Excellent'
                };
            case 'pass':
                return {
                    class: 'badge-primary',
                    status: 'Passed'
                };
            default:
                return {
                    class: 'badge-warning',
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
        // Create a simple toast notification
        const toast = document.createElement('div');
        toast.className = 'alert alert-success position-fixed';
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `<i class="fa fa-check-circle"></i> ${message}`;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    function showQuizError(message) {
        console.error('Error:', message);
        // Create a simple toast notification
        const toast = document.createElement('div');
        toast.className = 'alert alert-danger position-fixed';
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `<i class="fa fa-exclamation-circle"></i> ${message}`;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }

    // Simulate quiz completion for demo purposes (kept for testing)
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

    // Public functions for testing and global access
    window.quizManager = {
        loadQuizData,
        submitQuizResult,
        simulateQuizCompletion,
        startQuiz,
        backToQuizzes,
        currentData: () => currentQuizData,
        activeQuiz: () => activeQuiz
    };

    // Initialize the quizzes module
    initQuizzes();
    console.log('LexiAid Quizzes module loaded successfully');
    console.log('Available functions:', Object.keys(window.quizManager));
});
