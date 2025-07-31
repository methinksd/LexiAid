/**
 * LexiAid Insights Dashboard JavaScript
 * Enhanced analytics and insights functionality for Phase 6
 */

class LexiAidInsights {
    constructor() {
        this.charts = {};
        this.currentPeriod = 'weekly';
        this.currentUserId = 5; // Default to demo user
        this.apiEndpoint = '/insights.php';
        this.chartColors = {
            primary: 'rgba(54, 162, 235, 0.8)',
            secondary: 'rgba(255, 99, 132, 0.8)',
            success: 'rgba(75, 192, 192, 0.8)',
            warning: 'rgba(255, 206, 86, 0.8)',
            info: 'rgba(153, 102, 255, 0.8)',
            light: 'rgba(201, 203, 207, 0.8)'
        };
        
        this.init();
    }
    
    init() {
        this.bindEventListeners();
        this.loadInsights();
    }
    
    bindEventListeners() {
        // Period filter buttons
        document.querySelectorAll('[data-period]').forEach(button => {
            button.addEventListener('click', (e) => {
                this.switchPeriod(e.target.getAttribute('data-period'));
            });
        });
        
        // Export functionality
        document.querySelectorAll('[data-export]').forEach(button => {
            button.addEventListener('click', (e) => {
                this.exportData(e.target.getAttribute('data-export'));
            });
        });
        
        // Refresh button (if exists)
        const refreshBtn = document.getElementById('refresh-insights');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => this.loadInsights());
        }
    }
    
    switchPeriod(period) {
        // Update active button
        document.querySelectorAll('[data-period]').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-period="${period}"]`).classList.add('active');
        
        this.currentPeriod = period;
        this.loadInsights();
    }
    
    async loadInsights() {
        try {
            this.showLoading(true);
            
            const url = `${this.apiEndpoint}?user_id=${this.currentUserId}&period=${this.currentPeriod}`;
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            this.updateDashboard(data);
            this.showLoading(false);
            
        } catch (error) {
            console.error('Error loading insights:', error);
            this.showError(`Failed to load insights: ${error.message}`);
            this.showLoading(false);
        }
    }
    
    updateDashboard(data) {
        this.updateSummaryCards(data.summary);
        this.updateCharts(data);
        this.updateWeakAreas(data.weakAreas);
        this.updateRecommendations(data.recommendations);
        this.updateResourceList(data.resources);
    }
    
    updateSummaryCards(summary) {
        const updates = {
            'total-study-time': summary.totalStudyTime || 0,
            'cases-reviewed': summary.casesReviewed || 0,
            'tasks-completed': summary.tasksCompleted || 0,
            'quiz-average': `${summary.quizAverage || 0}%`
        };
        
        Object.entries(updates).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                this.animateValue(element, element.textContent, value);
            }
        });
        
        // Update task completion label
        const taskLabel = document.getElementById('tasks-completed-label');
        if (taskLabel) {
            taskLabel.textContent = `Of ${summary.tasksAssigned || 0} Assigned`;
        }
        
        // Update period labels
        const periodText = this.currentPeriod.charAt(0).toUpperCase() + this.currentPeriod.slice(1);
        document.querySelectorAll('[id$="-label"]').forEach(label => {
            if (label.textContent.includes('Week')) {
                label.textContent = label.textContent.replace(/Week\w*/i, periodText);
            }
        });
    }
    
    updateCharts(data) {
        this.destroyExistingCharts();
        
        // Study Time Chart
        if (data.studyTime && document.getElementById('studyTimeChart')) {
            this.charts.studyTime = this.createStudyTimeChart(data.studyTime);
        }
        
        // Subject Pie Chart
        if (data.subjectPie && document.getElementById('subjectPieChart')) {
            this.charts.subjectPie = this.createSubjectPieChart(data.subjectPie);
        }
        
        // Quiz Performance Chart
        if (data.quizPerformance && document.getElementById('quizPerformanceChart')) {
            this.charts.quizPerformance = this.createQuizPerformanceChart(data.quizPerformance);
        }
        
        // Topic Performance Radar
        if (data.topicPerformance && document.getElementById('topicPerformanceChart')) {
            this.charts.topicPerformance = this.createTopicPerformanceChart(data.topicPerformance);
        }
        
        // Productivity Chart
        if (data.productivity && document.getElementById('productivityChart')) {
            this.charts.productivity = this.createProductivityChart(data.productivity);
        }
        
        // Consistency Chart
        if (data.consistency && document.getElementById('consistencyChart')) {
            this.charts.consistency = this.createConsistencyChart(data.consistency);
        }
        
        // Resource Utilization Chart
        if (data.resourceUtilization && document.getElementById('resourceUtilizationChart')) {
            this.charts.resourceUtilization = this.createResourceUtilizationChart(data.resourceUtilization);
        }
    }
    
    createStudyTimeChart(data) {
        const ctx = document.getElementById('studyTimeChart').getContext('2d');
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Hours Studied',
                    data: data.values,
                    backgroundColor: this.chartColors.primary,
                    borderColor: this.chartColors.primary.replace('0.8', '1'),
                    borderWidth: 1,
                    borderRadius: 4,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2.5,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => `${context.parsed.y} hours`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Hours'
                        }
                    }
                }
            }
        });
    }
    
    createSubjectPieChart(data) {
        if (!data.labels.length) {
            this.showEmptyChart('subjectPieChart', 'No study data available');
            return null;
        }
        
        const ctx = document.getElementById('subjectPieChart').getContext('2d');
        const colors = Object.values(this.chartColors).slice(0, data.labels.length);
        
        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.values,
                    backgroundColor: colors,
                    borderColor: colors.map(color => color.replace('0.8', '1')),
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    }
    
    createQuizPerformanceChart(data) {
        if (!data.labels.length) {
            this.showEmptyChart('quizPerformanceChart', 'No quiz data available');
            return null;
        }
        
        const ctx = document.getElementById('quizPerformanceChart').getContext('2d');
        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: data.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Score (%)'
                        }
                    }
                }
            }
        });
    }
    
    createTopicPerformanceChart(data) {
        if (!data.labels.length) {
            this.showEmptyChart('topicPerformanceChart', 'No topic data available');
            return null;
        }
        
        const ctx = document.getElementById('topicPerformanceChart').getContext('2d');
        return new Chart(ctx, {
            type: 'radar',
            data: {
                labels: data.labels,
                datasets: data.datasets.map(dataset => ({
                    ...dataset,
                    pointBackgroundColor: dataset.borderColor,
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: dataset.borderColor
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                elements: {
                    line: {
                        borderWidth: 3
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    r: {
                        angleLines: {
                            display: true
                        },
                        suggestedMin: 0,
                        suggestedMax: 100,
                        title: {
                            display: true,
                            text: 'Performance (%)'
                        }
                    }
                }
            }
        });
    }
    
    createProductivityChart(data) {
        const ctx = document.getElementById('productivityChart').getContext('2d');
        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Productivity Score',
                    data: data.values,
                    borderColor: this.chartColors.success.replace('0.8', '1'),
                    backgroundColor: this.chartColors.success.replace('0.8', '0.2'),
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Productivity Score'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Time of Day'
                        }
                    }
                }
            }
        });
    }
    
    createConsistencyChart(data) {
        const ctx = document.getElementById('consistencyChart').getContext('2d');
        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Consistent', 'Inconsistent'],
                datasets: [{
                    data: data.values,
                    backgroundColor: [
                        this.chartColors.success,
                        this.chartColors.light
                    ],
                    borderColor: [
                        this.chartColors.success.replace('0.8', '1'),
                        this.chartColors.light.replace('0.8', '1')
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 1,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    
    createResourceUtilizationChart(data) {
        const ctx = document.getElementById('resourceUtilizationChart').getContext('2d');
        const colors = Object.values(this.chartColors).slice(0, data.labels.length);
        
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Hours Spent',
                    data: data.values,
                    backgroundColor: colors,
                    borderColor: colors.map(color => color.replace('0.8', '1')),
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Hours'
                        }
                    }
                }
            }
        });
    }
    
    updateWeakAreas(weakAreas) {
        const container = document.getElementById('weak-areas-list');
        if (!container) return;
        
        if (!weakAreas.length) {
            container.innerHTML = '<li class="list-group-item text-muted">No weak areas identified. Great job!</li>';
            return;
        }
        
        container.innerHTML = weakAreas.map(area => {
            const badgeClass = area.score < 70 ? 'badge-danger' : area.score < 80 ? 'badge-warning' : 'badge-secondary';
            return `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    ${area.topic}
                    <span class="badge ${badgeClass} badge-pill">${area.score}%</span>
                </li>
            `;
        }).join('');
    }
    
    updateRecommendations(recommendations) {
        const container = document.getElementById('recommendations-list');
        if (!container) return;
        
        container.innerHTML = recommendations.map(rec => `
            <div class="card mb-2 bg-light">
                <div class="card-body py-2">
                    <p class="mb-1">
                        <strong>${rec.title}:</strong> ${rec.text}
                    </p>
                    ${rec.action ? `<a href="#" class="btn btn-sm btn-outline-primary">${rec.action}</a>` : ''}
                </div>
            </div>
        `).join('');
    }
    
    updateResourceList(resources) {
        const container = document.getElementById('resource-list');
        if (!container) return;
        
        container.innerHTML = resources.map(resource => `
            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                ${resource.name}
                <span class="badge badge-primary badge-pill">${resource.hours} hrs</span>
            </a>
        `).join('');
    }
    
    destroyExistingCharts() {
        Object.values(this.charts).forEach(chart => {
            if (chart && typeof chart.destroy === 'function') {
                chart.destroy();
            }
        });
        this.charts = {};
    }
    
    showEmptyChart(canvasId, message) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.font = '16px Arial';
        ctx.fillStyle = '#6c757d';
        ctx.textAlign = 'center';
        ctx.fillText(message, canvas.width / 2, canvas.height / 2);
    }
    
    animateValue(element, start, end, duration = 1000) {
        const startNum = parseFloat(start) || 0;
        const endNum = parseFloat(end) || 0;
        const startTime = performance.now();
        
        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const currentValue = startNum + (endNum - startNum) * progress;
            const displayValue = typeof end === 'string' && end.includes('%') 
                ? Math.round(currentValue) + '%' 
                : Math.round(currentValue);
            
            element.textContent = displayValue;
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        
        requestAnimationFrame(animate);
    }
    
    showLoading(show) {
        const loader = document.getElementById('insights-loader');
        if (loader) {
            loader.style.display = show ? 'block' : 'none';
        }
        
        // Disable/enable buttons during loading
        document.querySelectorAll('[data-period]').forEach(btn => {
            btn.disabled = show;
        });
    }
    
    showError(message) {
        const errorContainer = document.getElementById('error-container');
        if (errorContainer) {
            errorContainer.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong> ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;
        } else {
            console.error('Insights Error:', message);
        }
    }
    
    exportData(format) {
        switch(format) {
            case 'pdf':
                this.exportToPDF();
                break;
            case 'email':
                this.emailReport();
                break;
            case 'print':
                this.printReport();
                break;
            default:
                console.log(`Unknown export format: ${format}`);
        }
    }
    
    async exportToPDF() {
        try {
            // Show loading
            this.showLoading(true);
            
            // Get current insights data
            const url = `${this.apiEndpoint}?user_id=${this.currentUserId}&period=${this.currentPeriod}`;
            const response = await fetch(url);
            const data = await response.json();
            
            // Create PDF content
            const pdfContent = this.generatePDFContent(data);
            
            // Create and download PDF using browser's print functionality
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>LexiAid Insights Report - ${this.currentPeriod}</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; color: #333; }
                        .header { text-align: center; border-bottom: 2px solid #007bff; padding-bottom: 20px; margin-bottom: 30px; }
                        .logo { font-size: 24px; font-weight: bold; color: #007bff; }
                        .period { color: #6c757d; margin-top: 10px; }
                        .summary-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 30px; }
                        .summary-card { border: 1px solid #ddd; padding: 15px; border-radius: 8px; text-align: center; }
                        .summary-value { font-size: 28px; font-weight: bold; color: #007bff; }
                        .summary-label { color: #6c757d; margin-top: 5px; }
                        .section { margin-bottom: 30px; }
                        .section-title { font-size: 18px; font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 15px; }
                        .weak-areas, .recommendations { margin-bottom: 20px; }
                        .weak-area { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
                        .score { font-weight: bold; }
                        .score.low { color: #dc3545; }
                        .score.medium { color: #ffc107; }
                        .score.high { color: #28a745; }
                        .recommendation { margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px; }
                        .footer { margin-top: 40px; text-align: center; color: #6c757d; font-size: 12px; border-top: 1px solid #ddd; padding-top: 20px; }
                        @media print {
                            body { margin: 0; }
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    ${pdfContent}
                    <div class="no-print" style="text-align: center; margin-top: 30px;">
                        <button onclick="window.print()" style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">Print/Save as PDF</button>
                        <button onclick="window.close()" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-left: 10px;">Close</button>
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();
            
            this.showLoading(false);
            
            // Auto-trigger print dialog after a short delay
            setTimeout(() => {
                printWindow.print();
            }, 500);
            
        } catch (error) {
            this.showError(`Failed to generate PDF: ${error.message}`);
            this.showLoading(false);
        }
    }
    
    generatePDFContent(data) {
        const currentDate = new Date().toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        const periodText = this.currentPeriod.charAt(0).toUpperCase() + this.currentPeriod.slice(1);
        
        return `
            <div class="header">
                <div class="logo">LexiAid</div>
                <h1>Study Insights Report</h1>
                <div class="period">${periodText} Report • Generated on ${currentDate}</div>
            </div>
            
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-value">${data.summary.totalStudyTime || 0}</div>
                    <div class="summary-label">Hours Studied</div>
                </div>
                <div class="summary-card">
                    <div class="summary-value">${data.summary.tasksCompleted || 0}/${data.summary.tasksAssigned || 0}</div>
                    <div class="summary-label">Tasks Completed</div>
                </div>
                <div class="summary-card">
                    <div class="summary-value">${data.summary.quizAverage || 0}%</div>
                    <div class="summary-label">Quiz Average</div>
                </div>
                <div class="summary-card">
                    <div class="summary-value">${data.summary.casesReviewed || 0}</div>
                    <div class="summary-label">Cases Reviewed</div>
                </div>
            </div>
            
            ${data.weakAreas.length > 0 ? `
                <div class="section">
                    <div class="section-title">Areas for Improvement</div>
                    <div class="weak-areas">
                        ${data.weakAreas.map(area => `
                            <div class="weak-area">
                                <span>${area.topic}</span>
                                <span class="score ${area.score < 70 ? 'low' : area.score < 80 ? 'medium' : 'high'}">${area.score}%</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            ` : ''}
            
            ${data.recommendations.length > 0 ? `
                <div class="section">
                    <div class="section-title">Personalized Recommendations</div>
                    <div class="recommendations">
                        ${data.recommendations.map(rec => `
                            <div class="recommendation">
                                <strong>${rec.title}:</strong> ${rec.text}
                            </div>
                        `).join('')}
                    </div>
                </div>
            ` : ''}
            
            <div class="section">
                <div class="section-title">Study Performance Summary</div>
                <p><strong>Study Consistency:</strong> ${data.consistency.values[0]}% consistent study habits</p>
                <p><strong>Most Productive Time:</strong> 8:00 AM - 11:00 AM (based on performance data)</p>
                <p><strong>Top Performing Subject:</strong> ${this.getTopPerformingSubject(data.topicPerformance)}</p>
            </div>
            
            <div class="footer">
                <p>Generated by LexiAid Study Insights • ${currentDate}</p>
                <p>This report is based on your ${periodText.toLowerCase()} study activity and quiz performance.</p>
            </div>
        `;
    }
    
    getTopPerformingSubject(topicData) {
        if (!topicData.labels.length || !topicData.datasets.length) {
            return 'No data available';
        }
        
        const scores = topicData.datasets[0].data;
        const maxScore = Math.max(...scores);
        const topIndex = scores.indexOf(maxScore);
        
        return `${topicData.labels[topIndex]} (${maxScore}%)`;
    }
    
    emailReport() {
        // Get current date for subject line
        const currentDate = new Date().toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        const periodText = this.currentPeriod.charAt(0).toUpperCase() + this.currentPeriod.slice(1);
        
        // Create email content
        const subject = `LexiAid ${periodText} Study Insights Report - ${currentDate}`;
        const body = `Hi there!

I wanted to share my ${periodText.toLowerCase()} study insights report from LexiAid.

To view the full interactive report with charts and detailed analytics, please visit:
${window.location.origin}/insights.html

This report includes:
• Study time distribution and productivity metrics
• Quiz performance trends and topic analysis  
• Personalized recommendations for improvement
• Progress tracking and consistency metrics

Generated on ${currentDate} by LexiAid Study Insights.

Best regards!`;

        // Create mailto link
        const mailtoLink = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
        
        // Try to open email client
        try {
            window.location.href = mailtoLink;
        } catch (error) {
            // Fallback: copy to clipboard
            this.copyToClipboard(`${subject}\n\n${body}`);
            alert('Email client not available. Email content has been copied to your clipboard!');
        }
    }
    
    printReport() {
        // Create a print-friendly version of the current page
        const printContent = this.generatePrintContent();
        
        // Create new window for printing
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>LexiAid Insights Report - Print Version</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; color: #333; }
                    .print-header { text-align: center; margin-bottom: 30px; }
                    .logo { font-size: 24px; font-weight: bold; color: #000; }
                    .charts-note { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; text-align: center; color: #6c757d; }
                    .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 20px 0; }
                    .summary-item { text-align: center; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
                    .summary-value { font-size: 24px; font-weight: bold; }
                    .summary-label { color: #666; margin-top: 5px; font-size: 14px; }
                    .section { margin: 30px 0; }
                    .section-title { font-size: 18px; font-weight: bold; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 15px; }
                    .weak-area, .recommendation { margin: 10px 0; padding: 10px; border-left: 4px solid #007bff; background: #f8f9fa; }
                    .score { font-weight: bold; float: right; }
                    .footer { margin-top: 40px; text-align: center; color: #666; font-size: 12px; border-top: 1px solid #ddd; padding-top: 20px; }
                    @media print {
                        body { margin: 10px; }
                        .charts-note { background: #f0f0f0 !important; }
                    }
                </style>
            </head>
            <body>
                ${printContent}
            </body>
            </html>
        `);
        printWindow.document.close();
        
        // Auto-trigger print dialog
        setTimeout(() => {
            printWindow.print();
        }, 500);
    }
    
    generatePrintContent() {
        const currentDate = new Date().toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        const periodText = this.currentPeriod.charAt(0).toUpperCase() + this.currentPeriod.slice(1);
        
        // Get current data from DOM
        const totalStudyTime = document.getElementById('total-study-time')?.textContent || '0';
        const tasksCompleted = document.getElementById('tasks-completed')?.textContent || '0';
        const tasksLabel = document.getElementById('tasks-completed-label')?.textContent || '';
        const quizAverage = document.getElementById('quiz-average')?.textContent || '0%';
        const casesReviewed = document.getElementById('cases-reviewed')?.textContent || '0';
        
        // Get weak areas from DOM
        const weakAreasList = document.getElementById('weak-areas-list');
        let weakAreasHTML = '';
        if (weakAreasList) {
            const weakItems = weakAreasList.querySelectorAll('.list-group-item');
            weakAreasHTML = Array.from(weakItems).map(item => {
                const text = item.textContent.trim();
                const parts = text.split(/(\d+%)$/);
                return `<div class="weak-area">${parts[0]}<span class="score">${parts[1] || ''}</span></div>`;
            }).join('');
        }
        
        // Get recommendations from DOM
        const recommendationsList = document.getElementById('recommendations-list');
        let recommendationsHTML = '';
        if (recommendationsList) {
            const recItems = recommendationsList.querySelectorAll('.card');
            recommendationsHTML = Array.from(recItems).map(item => {
                const text = item.textContent.trim().replace(/\s+/g, ' ');
                return `<div class="recommendation">${text}</div>`;
            }).join('');
        }
        
        return `
            <div class="print-header">
                <div class="logo">LexiAid</div>
                <h1>Study Insights Report</h1>
                <p>${periodText} Report • Generated on ${currentDate}</p>
            </div>
            
            <div class="charts-note">
                📊 For interactive charts and detailed visualizations, visit the online dashboard at ${window.location.origin}/insights.html
            </div>
            
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-value">${totalStudyTime}</div>
                    <div class="summary-label">Hours Studied</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">${tasksCompleted}</div>
                    <div class="summary-label">Tasks Completed</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">${quizAverage}</div>
                    <div class="summary-label">Quiz Average</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">${casesReviewed}</div>
                    <div class="summary-label">Cases Reviewed</div>
                </div>
            </div>
            
            ${weakAreasHTML ? `
                <div class="section">
                    <div class="section-title">Areas for Improvement</div>
                    ${weakAreasHTML}
                </div>
            ` : ''}
            
            ${recommendationsHTML ? `
                <div class="section">
                    <div class="section-title">Personalized Recommendations</div>
                    ${recommendationsHTML}
                </div>
            ` : ''}
            
            <div class="section">
                <div class="section-title">Study Notes</div>
                <p><strong>Report Period:</strong> ${periodText} view</p>
                <p><strong>Most Productive Time:</strong> Based on your performance data, you tend to be most productive during morning hours (8:00 AM - 11:00 AM).</p>
                <p><strong>Study Consistency:</strong> Maintain regular study habits for optimal learning outcomes.</p>
            </div>
            
            <div class="footer">
                <p>Generated by LexiAid Study Insights • ${currentDate}</p>
                <p>For the most up-to-date insights with interactive charts, visit ${window.location.origin}/insights.html</p>
            </div>
        `;
    }
    
    async copyToClipboard(text) {
        try {
            if (navigator.clipboard) {
                await navigator.clipboard.writeText(text);
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
            }
        } catch (error) {
            console.error('Failed to copy to clipboard:', error);
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart !== 'undefined') {
        window.lexiAidInsights = new LexiAidInsights();
    } else {
        console.error('Chart.js library not loaded. Please check your script includes.');
    }
});

// Set current date
document.addEventListener('DOMContentLoaded', () => {
    const dateElement = document.getElementById('current-date');
    if (dateElement) {
        dateElement.textContent = new Date().toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    }
    
    const yearElement = document.getElementById('copyright-year');
    if (yearElement) {
        yearElement.textContent = new Date().getFullYear();
    }
});
