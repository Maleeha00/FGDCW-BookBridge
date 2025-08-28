// Main JavaScript file for the FGDCW Bookbridge system.
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips if any
    const tooltips = document.querySelectorAll('[data-tooltip]');
    if (tooltips.length > 0) {
        tooltips.forEach(tooltip => {
            tooltip.addEventListener('mouseenter', function() {
                const tooltipText = this.getAttribute('data-tooltip');
                const tooltipEl = document.createElement('div');
                tooltipEl.className = 'tooltip';
                tooltipEl.textContent = tooltipText;
                document.body.appendChild(tooltipEl);
                
                const rect = this.getBoundingClientRect();
                tooltipEl.style.left = rect.left + (rect.width / 2) - (tooltipEl.offsetWidth / 2) + 'px';
                tooltipEl.style.top = rect.top - tooltipEl.offsetHeight - 10 + 'px';
                
                setTimeout(() => {
                    tooltipEl.classList.add('show');
                }, 10);
                
                this.addEventListener('mouseleave', function handler() {
                    tooltipEl.classList.remove('show');
                    setTimeout(() => {
                        document.body.removeChild(tooltipEl);
                    }, 300);
                    this.removeEventListener('mouseleave', handler);
                });
            });
        });
    }
    
    // Form validation
    const forms = document.querySelectorAll('form');
    if (forms.length > 0) {
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('[required]');
                let valid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        valid = false;
                        field.classList.add('is-invalid');
                        
                        // Create error message if it doesn't exist
                        let errorMsg = field.nextElementSibling;
                        if (!errorMsg || !errorMsg.classList.contains('error-message')) {
                            errorMsg = document.createElement('div');
                            errorMsg.className = 'error-message';
                            errorMsg.textContent = 'This field is required';
                            field.parentNode.insertBefore(errorMsg, field.nextSibling);
                        }
                    } else {
                        field.classList.remove('is-invalid');
                        
                        // Remove error message if it exists
                        const errorMsg = field.nextElementSibling;
                        if (errorMsg && errorMsg.classList.contains('error-message')) {
                            errorMsg.remove();
                        }
                    }
                });
                
                if (!valid) {
                    e.preventDefault();
                }
            });
            
            // Live validation on input
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    if (this.hasAttribute('required') && this.value.trim()) {
                        this.classList.remove('is-invalid');
                        
                        // Remove error message if it exists
                        const errorMsg = this.nextElementSibling;
                        if (errorMsg && errorMsg.classList.contains('error-message')) {
                            errorMsg.remove();
                        }
                    }
                });
            });
        });
    }
    
    // Alert auto-close
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
        alerts.forEach(alert => {
            if (!alert.classList.contains('alert-persistent')) {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 5000);
            }
            
            // Add close button (X)
            const closeBtn = document.createElement('button');
            closeBtn.className = 'alert-close';
            closeBtn.innerHTML = '&times;';
            closeBtn.setAttribute('aria-label', 'Close');
            closeBtn.style.position = 'absolute';
            closeBtn.style.top = '8px';
            closeBtn.style.right = '12px';
            closeBtn.style.background = 'transparent';
            closeBtn.style.border = 'none';
            closeBtn.style.fontSize = '1.5rem';
            closeBtn.style.cursor = 'pointer';
            closeBtn.style.color = '#333';
            closeBtn.style.lineHeight = '1';
            closeBtn.style.padding = '0';
            closeBtn.style.zIndex = '10';
            closeBtn.addEventListener('mouseenter', function() {
                closeBtn.style.color = '#c00';
            });
            closeBtn.addEventListener('mouseleave', function() {
                closeBtn.style.color = '#333';
            });
            closeBtn.addEventListener('click', function() {
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            });
            alert.style.position = 'relative';
            alert.appendChild(closeBtn);
        });
    }
    
    // Password visibility toggle
    const passwordToggles = document.querySelectorAll('.password-toggle');
    if (passwordToggles.length > 0) {
        passwordToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const passwordField = document.querySelector(this.getAttribute('data-target'));
                if (passwordField) {
                    if (passwordField.type === 'password') {
                        passwordField.type = 'text';
                        this.innerHTML = '<i class="fas fa-eye-slash"></i>';
                    } else {
                        passwordField.type = 'password';
                        this.innerHTML = '<i class="fas fa-eye"></i>';
                    }
                }
            });
        });
    }
});

// Show confirmation dialog for delete actions
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

// Format date for display
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

// Format currency for display
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Truncate text with ellipsis
function truncateText(text, maxLength = 100) {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}