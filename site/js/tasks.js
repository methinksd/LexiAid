// LexiAid Tasks Frontend Script

document.addEventListener('DOMContentLoaded', function() {
    let currentTasks = [];
    const DEMO_USER_ID = 1; // Using demo user

    // DOM Elements
    const taskForm = document.getElementById('addTaskForm');
    const taskTitle = document.getElementById('taskTitle');
    const taskDescription = document.getElementById('taskDescription');
    const taskCategory = document.getElementById('taskCategory');
    const taskPriority = document.getElementById('taskPriority');
    const taskDueDate = document.getElementById('taskDueDate');
    const taskDueTime = document.getElementById('taskDueTime');

    // Initialize the tasks page
    function initTasks() {
        loadTasks();
        setupEventListeners();
        setMinDate();
    }

    // Set minimum date to today
    function setMinDate() {
        if (taskDueDate) {
            const today = new Date().toISOString().split('T')[0];
            taskDueDate.min = today;
        }
    }

    // Load tasks from the backend
    async function loadTasks() {
        try {
            console.log('Loading tasks...');
            
            const response = await fetch(`tasks.php?user_id=${DEMO_USER_ID}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('Tasks response:', data);

            if (data.status === 'success') {
                currentTasks = data.tasks || [];
                displayTasks(currentTasks);
                updateTaskStats();
            } else {
                throw new Error(data.message || 'Failed to load tasks');
            }
        } catch (error) {
            console.error('Error loading tasks:', error);
            showTaskError('Failed to load tasks. Please refresh the page.');
        }
    }

    // Display tasks in the UI
    function displayTasks(tasks) {
        // Clear existing task cards (except the first static demo cards)
        const taskContainers = document.querySelectorAll('.task-card[data-dynamic="true"]');
        taskContainers.forEach(card => card.remove());

        if (tasks.length === 0) {
            console.log('No tasks found');
            return;
        }

        // Find the container to add tasks to
        const taskContainer = document.querySelector('.row');
        if (!taskContainer) {
            console.error('Task container not found');
            return;
        }

        // Add new dynamic tasks
        tasks.forEach(task => {
            const taskCard = createTaskCard(task);
            taskContainer.insertAdjacentHTML('beforeend', taskCard);
        });

        // Setup event listeners for the new task cards
        setupTaskCardListeners();
    }

    // Create HTML for a task card
    function createTaskCard(task) {
        const priorityClass = getPriorityClass(task.priority);
        const statusBadge = getStatusBadge(task);
        const deadline = formatDeadline(task.deadline);
        const completed = task.completed === '1' || task.completed === 1;

        return `
            <div class="col-12 mb-4">
                <div class="card task-card ${priorityClass} ${completed ? 'task-complete' : ''}" data-task-id="${task.task_id}" data-dynamic="true">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-1">
                                <span class="drag-handle mr-2"><i class="fas fa-grip-vertical"></i></span>
                                ${task.title}
                            </h5>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input task-checkbox" 
                                       id="taskCheck${task.task_id}" 
                                       ${completed ? 'checked' : ''} 
                                       data-task-id="${task.task_id}">
                                <label class="custom-control-label" for="taskCheck${task.task_id}"></label>
                            </div>
                        </div>
                        <p class="card-text text-muted mb-2">${task.description || 'No description'}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge badge-${getPriorityBadgeClass(task.priority)} mr-1">${task.priority} Priority</span>
                                <span class="badge badge-secondary">${task.category}</span>
                                ${statusBadge}
                            </div>
                            <div class="d-flex align-items-center">
                                <small class="text-muted mr-2">${deadline}</small>
                                <button class="btn btn-sm btn-outline-danger delete-task" data-task-id="${task.task_id}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Helper functions
    function getPriorityClass(priority) {
        switch(priority) {
            case 'high': return 'task-priority-high';
            case 'medium': return 'task-priority-medium';
            case 'low': return 'task-priority-low';
            default: return 'task-priority-medium';
        }
    }

    function getPriorityBadgeClass(priority) {
        switch(priority) {
            case 'high': return 'danger';
            case 'medium': return 'warning';
            case 'low': return 'success';
            default: return 'warning';
        }
    }

    function getStatusBadge(task) {
        if (task.completed === '1' || task.completed === 1) {
            return '<span class="badge badge-success ml-1">Completed</span>';
        }
        
        switch(task.status) {
            case 'overdue':
                return '<span class="badge badge-danger ml-1">Overdue</span>';
            case 'due-soon':
                return '<span class="badge badge-warning ml-1">Due Soon</span>';
            default:
                return '<span class="badge badge-info ml-1">Upcoming</span>';
        }
    }

    function formatDeadline(deadline) {
        if (!deadline) return 'No deadline';
        
        const date = new Date(deadline);
        const now = new Date();
        const diffTime = date - now;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        if (diffDays < 0) {
            return `<i class="fa fa-clock text-danger"></i> Overdue`;
        } else if (diffDays === 0) {
            return `<i class="fa fa-clock text-warning"></i> Due Today`;
        } else if (diffDays === 1) {
            return `<i class="fa fa-clock text-warning"></i> Due Tomorrow`;
        } else {
            return `<i class="fa fa-clock"></i> Due in ${diffDays} days`;
        }
    }

    // Create a new task
    async function createTask(taskData) {
        try {
            console.log('Creating task:', taskData);

            const response = await fetch('tasks.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(taskData)
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('Create task response:', data);

            if (data.status === 'success') {
                showTaskSuccess('Task created successfully!');
                loadTasks(); // Reload tasks
                resetTaskForm();
                
                // Close the modal if it exists
                const modal = document.getElementById('addTaskModal');
                if (modal && window.$ && window.$().modal) {
                    window.$('#addTaskModal').modal('hide');
                }
            } else {
                throw new Error(data.message || 'Failed to create task');
            }
        } catch (error) {
            console.error('Error creating task:', error);
            showTaskError('Failed to create task: ' + error.message);
        }
    }

    // Update task completion status
    async function updateTaskCompletion(taskId, completed) {
        try {
            const response = await fetch('tasks.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    task_id: parseInt(taskId),
                    user_id: DEMO_USER_ID,
                    completed: completed
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.status === 'success') {
                console.log('Task updated:', data);
                loadTasks(); // Reload tasks to reflect changes
            } else {
                throw new Error(data.message || 'Failed to update task');
            }
        } catch (error) {
            console.error('Error updating task:', error);
            showTaskError('Failed to update task');
        }
    }

    // Delete a task
    async function deleteTask(taskId) {
        if (!confirm('Are you sure you want to delete this task?')) {
            return;
        }

        try {
            const response = await fetch('tasks.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    task_id: parseInt(taskId),
                    user_id: DEMO_USER_ID
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.status === 'success') {
                showTaskSuccess('Task deleted successfully');
                loadTasks(); // Reload tasks
            } else {
                throw new Error(data.message || 'Failed to delete task');
            }
        } catch (error) {
            console.error('Error deleting task:', error);
            showTaskError('Failed to delete task');
        }
    }

    // Setup event listeners
    function setupEventListeners() {
        // Task form submission
        if (taskForm) {
            taskForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const deadline = taskDueDate.value && taskDueTime.value 
                    ? `${taskDueDate.value} ${taskDueTime.value}:00`
                    : (taskDueDate.value ? `${taskDueDate.value} 23:59:59` : null);

                const taskData = {
                    user_id: DEMO_USER_ID,
                    title: taskTitle.value.trim(),
                    description: taskDescription.value.trim(),
                    category: taskCategory.value,
                    priority: taskPriority.value,
                    deadline: deadline
                };

                createTask(taskData);
            });
        }
    }

    // Setup listeners for task cards (checkboxes and delete buttons)
    function setupTaskCardListeners() {
        // Task completion checkboxes
        document.querySelectorAll('.task-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const taskId = this.getAttribute('data-task-id');
                const completed = this.checked;
                updateTaskCompletion(taskId, completed);
            });
        });

        // Delete task buttons
        document.querySelectorAll('.delete-task').forEach(button => {
            button.addEventListener('click', function() {
                const taskId = this.getAttribute('data-task-id');
                deleteTask(taskId);
            });
        });
    }

    // Utility functions
    function resetTaskForm() {
        if (taskForm) {
            taskForm.reset();
        }
    }

    function updateTaskStats() {
        const totalTasks = currentTasks.length;
        const completedTasks = currentTasks.filter(task => task.completed === '1' || task.completed === 1).length;
        const overdueTasks = currentTasks.filter(task => task.status === 'overdue').length;

        console.log(`Task Stats - Total: ${totalTasks}, Completed: ${completedTasks}, Overdue: ${overdueTasks}`);
    }

    function showTaskSuccess(message) {
        console.log('Success:', message);
        // You can implement a toast notification here
        alert(message); // Temporary alert
    }

    function showTaskError(message) {
        console.error('Error:', message);
        // You can implement a toast notification here
        alert('Error: ' + message); // Temporary alert
    }

    // Public functions for testing
    window.taskManager = {
        loadTasks,
        createTask,
        deleteTask,
        currentTasks: () => currentTasks
    };

    // Initialize the tasks module
    initTasks();
    console.log('LexiAid Tasks module loaded successfully');
});
