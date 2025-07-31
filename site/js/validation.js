/**
 * LexiAid Phase 7 - Client-Side Validation and UX Enhancements
 * Enhanced form validation, loading states, and user feedback
 */

// Global validation rules and helper functions
const LexiAidValidation = {
    // Validation patterns
    patterns: {
        email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        phone: /^[\+]?[1-9][\d]{0,15}$/,
        url: /^https?:\/\/[^\s/$.?#].[^\s]*$/i,
        alphanumeric: /^[a-zA-Z0-9]+$/,
        alphaNumericSpaces: /^[a-zA-Z0-9\s]+$/
    },

    // Show validation error
    showError: function(element, message) {
        this.clearError(element);
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback d-block fade-in';
        errorDiv.textContent = message;
        
        element.classList.add('is-invalid');
        element.parentNode.appendChild(errorDiv);
        
        // Add red border animation
        element.style.animation = 'shake 0.5s ease-in-out';
        setTimeout(() => {
            element.style.animation = '';
        }, 500);
    },

    // Clear validation error
    clearError: function(element) {
        element.classList.remove('is-invalid');
        const errorDiv = element.parentNode.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.remove();
        }
    },

    // Show success
    showSuccess: function(element) {
        this.clearError(element);
        element.classList.add('is-valid');
        
        // Remove success styling after 3 seconds
        setTimeout(() => {
            element.classList.remove('is-valid');
        }, 3000);
    },

    // Validate single field
    validateField: function(element, rules) {
        const value = element.value.trim();
        
        // Required check
        if (rules.required && !value) {
            this.showError(element, rules.requiredMessage || 'This field is required');
            return false;
        }
        
        // Skip other validations if field is empty and not required
        if (!value && !rules.required) {
            this.clearError(element);
            return true;
        }
        
        // Length validation
        if (rules.minLength && value.length < rules.minLength) {
            this.showError(element, `Must be at least ${rules.minLength} characters long`);
            return false;
        }
        
        if (rules.maxLength && value.length > rules.maxLength) {
            this.showError(element, `Must be no more than ${rules.maxLength} characters long`);
            return false;
        }
        
        // Pattern validation
        if (rules.pattern && !this.patterns[rules.pattern].test(value)) {
            this.showError(element, rules.patternMessage || 'Invalid format');
            return false;
        }
        
        // Custom validation function
        if (rules.custom && typeof rules.custom === 'function') {
            const customResult = rules.custom(value);
            if (customResult !== true) {
                this.showError(element, customResult);
                return false;
            }
        }
        
        this.showSuccess(element);
        return true;
    },

    // Validate entire form
    validateForm: function(formElement, rules) {
        let isValid = true;
        
        for (const fieldName in rules) {
            const field = formElement.querySelector(`[name="${fieldName}"]`);
            if (field) {
                const fieldValid = this.validateField(field, rules[fieldName]);
                if (!fieldValid) {
                    isValid = false;
                }
            }
        }
        
        return isValid;
    },

    // Setup real-time validation
    setupRealTimeValidation: function(formElement, rules) {
        for (const fieldName in rules) {
            const field = formElement.querySelector(`[name="${fieldName}"]`);
            if (field) {
                // Validate on blur
                field.addEventListener('blur', () => {
                    this.validateField(field, rules[fieldName]);
                });
                
                // Clear errors on input
                field.addEventListener('input', () => {
                    if (field.classList.contains('is-invalid')) {
                        this.clearError(field);
                    }
                });
            }
        }
    }
};

// Loading state management
const LoadingManager = {
    // Show loading spinner on element
    showLoading: function(element, message = 'Loading...') {
        element.disabled = true;
        element.dataset.originalContent = element.innerHTML;
        element.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>${message}`;
        element.classList.add('loading');
    },

    // Hide loading spinner
    hideLoading: function(element) {
        element.disabled = false;
        if (element.dataset.originalContent) {
            element.innerHTML = element.dataset.originalContent;
            delete element.dataset.originalContent;
        }
        element.classList.remove('loading');
    },

    // Show page loading overlay
    showPageLoading: function(message = 'Loading...') {
        const overlay = document.createElement('div');
        overlay.id = 'loading-overlay';
        overlay.className = 'loading-overlay fade-in';
        overlay.innerHTML = `
            <div class="loading-container">
                <div class="loading-spinner"></div>
                <div class="loading-text">${message}</div>
            </div>
        `;
        document.body.appendChild(overlay);
    },

    // Hide page loading overlay
    hidePageLoading: function() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.remove();
        }
    }
};

// Toast notification system
const ToastManager = {
    // Show toast notification
    show: function(message, type = 'info', duration = 5000) {
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type} fade-in`;
        
        const icon = this.getIcon(type);
        toast.innerHTML = `
            <div class="toast-content">
                <i class="${icon} mr-2"></i>
                <span>${message}</span>
                <button class="toast-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        // Add to page
        const container = this.getContainer();
        container.appendChild(toast);
        
        // Auto-remove after duration
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, duration);
    },

    // Get icon for toast type
    getIcon: function(type) {
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };
        return icons[type] || icons.info;
    },

    // Get or create toast container
    getContainer: function() {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        return container;
    },

    // Convenience methods
    success: function(message, duration) { this.show(message, 'success', duration); },
    error: function(message, duration) { this.show(message, 'error', duration); },
    warning: function(message, duration) { this.show(message, 'warning', duration); },
    info: function(message, duration) { this.show(message, 'info', duration); }
};

// AJAX helper with enhanced error handling
const AjaxHelper = {
    // Enhanced fetch wrapper
    request: async function(url, options = {}) {
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        };
        
        const mergedOptions = { ...defaultOptions, ...options };
        
        try {
            const response = await fetch(url, mergedOptions);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return await response.json();
            } else {
                return await response.text();
            }
            
        } catch (error) {
            console.error('AJAX request failed:', error);
            throw error;
        }
    },

    // GET request
    get: function(url, options = {}) {
        return this.request(url, { ...options, method: 'GET' });
    },

    // POST request
    post: function(url, data, options = {}) {
        return this.request(url, {
            ...options,
            method: 'POST',
            body: JSON.stringify(data)
        });
    },

    // PUT request
    put: function(url, data, options = {}) {
        return this.request(url, {
            ...options,
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },

    // DELETE request
    delete: function(url, options = {}) {
        return this.request(url, { ...options, method: 'DELETE' });
    }
};

// Form submission helper
const FormHelper = {
    // Submit form with validation and loading state
    submitForm: async function(formElement, validationRules, submitCallback) {
        // Prevent default submission
        formElement.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Validate form
            if (!LexiAidValidation.validateForm(formElement, validationRules)) {
                ToastManager.error('Please correct the errors in the form');
                return;
            }
            
            // Get form data
            const formData = new FormData(formElement);
            const data = Object.fromEntries(formData.entries());
            
            // Find submit button
            const submitBtn = formElement.querySelector('button[type="submit"]') || 
                             formElement.querySelector('input[type="submit"]');
            
            try {
                // Show loading state
                if (submitBtn) {
                    LoadingManager.showLoading(submitBtn, 'Submitting...');
                }
                
                // Call submit callback
                const result = await submitCallback(data, formElement);
                
                // Handle success
                if (result.success !== false) {
                    ToastManager.success(result.message || 'Form submitted successfully');
                    
                    // Reset form if requested
                    if (result.resetForm !== false) {
                        formElement.reset();
                        // Clear all validation states
                        formElement.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
                            el.classList.remove('is-valid', 'is-invalid');
                        });
                        formElement.querySelectorAll('.invalid-feedback').forEach(el => {
                            el.remove();
                        });
                    }
                }
                
            } catch (error) {
                console.error('Form submission error:', error);
                ToastManager.error(error.message || 'An error occurred while submitting the form');
                
            } finally {
                // Hide loading state
                if (submitBtn) {
                    LoadingManager.hideLoading(submitBtn);
                }
            }
        });
    }
};

// Utility functions
const Utils = {
    // Debounce function
    debounce: function(func, delay) {
        let timeoutId;
        return function (...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    },

    // Throttle function
    throttle: function(func, limit) {
        let inThrottle;
        return function (...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },

    // Format file size
    formatFileSize: function(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },

    // Escape HTML
    escapeHtml: function(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

// Add CSS for enhanced components if not already present
if (!document.querySelector('#lexiaid-validation-css')) {
    const style = document.createElement('style');
    style.id = 'lexiaid-validation-css';
    style.textContent = `
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        }
        
        .toast-notification {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            margin-bottom: 10px;
            border-left: 4px solid #007bff;
            animation: slideInRight 0.3s ease;
        }
        
        .toast-success { border-left-color: #28a745; }
        .toast-error { border-left-color: #dc3545; }
        .toast-warning { border-left-color: #ffc107; }
        .toast-info { border-left-color: #007bff; }
        
        .toast-content {
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .toast-close {
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            padding: 0;
            margin-left: 10px;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }
        
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .loading {
            cursor: not-allowed;
            opacity: 0.7;
        }
    `;
    document.head.appendChild(style);
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', function() {
    // Add focus outlines for accessibility
    document.querySelectorAll('input, select, textarea, button').forEach(element => {
        element.classList.add('focus-outline');
    });
    
    // Set current year in copyright
    document.querySelectorAll('#copyright-year, .copyright-year').forEach(element => {
        element.textContent = new Date().getFullYear();
    });
    
    // Set current date
    document.querySelectorAll('#current-date').forEach(element => {
        element.textContent = new Date().toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    });
});
