
document.addEventListener('DOMContentLoaded', function() {
    
    const viewOptions = document.querySelectorAll('.view-option');
    const booksContainer = document.querySelector('.books-container');
    
    if (viewOptions.length > 0 && booksContainer) {
        viewOptions.forEach(option => {
            option.addEventListener('click', function() {
                viewOptions.forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');
                const viewMode = this.getAttribute('data-view');
                booksContainer.className = 'books-container ' + viewMode;
                
                localStorage.setItem('booksViewMode', viewMode);
            });
        });
        
       
        const savedViewMode = localStorage.getItem('booksViewMode');
        if (savedViewMode) {
            
            viewOptions.forEach(option => {
                if (option.getAttribute('data-view') === savedViewMode) {
                    option.classList.add('active');
                } else {
                    option.classList.remove('active');
                }
            });
            
            
            booksContainer.className = 'books-container ' + savedViewMode;
        }
    }
    
    
    const modalTriggers = document.querySelectorAll('[data-modal-target]');
    const modalCloseButtons = document.querySelectorAll('.modal-close, .modal-cancel');
    const modalOverlays = document.querySelectorAll('.modal-overlay');
    
    
    if (modalTriggers.length > 0) {
        modalTriggers.forEach(trigger => {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                const modalId = this.getAttribute('data-modal-target');
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.add('active');
                    document.body.style.overflow = 'hidden'; 
                    
                   
                    const firstInput = modal.querySelector('input, textarea, select');
                    if (firstInput) {
                        setTimeout(() => firstInput.focus(), 100);
                    }
                }
            });
        });
    }
    
   
    if (modalCloseButtons.length > 0) {
        modalCloseButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const modal = this.closest('.modal-overlay');
                if (modal) {
                    modal.classList.remove('active');
                    document.body.style.overflow = ''; 
                }
            });
        });
    }
    if (modalOverlays.length > 0) {
        modalOverlays.forEach(overlay => {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                    document.body.style.overflow = ''; 
                }
            });
        });
    }
    
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('.modal-overlay.active');
            if (activeModal) {
                activeModal.classList.remove('active');
                document.body.style.overflow = ''; 
            }
        }
    });
    
    
    const searchInput = document.getElementById('book-search');
    const bookItems = document.querySelectorAll('.book-card, .book-item');
    
    if (searchInput && bookItems.length > 0) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            
            if (query === '') {
                
                bookItems.forEach(item => {
                    item.style.display = '';
                });
                return;
            }
            
            
            bookItems.forEach(item => {
                const title = item.querySelector('.book-title').textContent.toLowerCase();
                const author = item.querySelector('.book-author').textContent.toLowerCase();
                
                if (title.includes(query) || author.includes(query)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
    
    
    const fileInputs = document.querySelectorAll('.custom-file-input');
    
    if (fileInputs.length > 0) {
        fileInputs.forEach(input => {
            input.addEventListener('change', function() {
                const fileLabel = this.nextElementSibling;
                if (fileLabel && fileLabel.classList.contains('custom-file-label')) {
                    if (this.files.length > 0) {
                        fileLabel.textContent = this.files[0].name;
                    } else {
                        fileLabel.textContent = 'Choose file';
                    }
                }
                
                
                const previewContainer = document.querySelector(this.getAttribute('data-preview'));
                if (previewContainer && this.files.length > 0) {
                    const file = this.files[0];
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewContainer.innerHTML = `<img src="${e.target.result}" class="img-preview">`;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        previewContainer.innerHTML = `<div class="file-icon"><i class="fas fa-file"></i> ${file.name}</div>`;
                    }
                }
            });
        });
    }
    
    
    const filterSelect = document.getElementById('book-filter');
    
    if (filterSelect && bookItems.length > 0) {
        filterSelect.addEventListener('change', function() {
            const filterValue = this.value;
            
            if (filterValue === 'all') {
                
                bookItems.forEach(item => {
                    item.style.display = '';
                });
                return;
            }
            
            
            bookItems.forEach(item => {
                const category = item.getAttribute('data-category').toLowerCase();
                
                if (category === filterValue) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
    
    const notificationItems = document.querySelectorAll('.notification-item');
    
    if (notificationItems.length > 0) {
        notificationItems.forEach(item => {
            item.addEventListener('click', function() {
                
                if (!this.classList.contains('unread')) return;
                
                const notificationId = this.getAttribute('data-id');
                
                
                fetch('mark_notification_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `notification_id=${notificationId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        
                        this.classList.remove('unread');
                        
                        
                        const countEl = document.querySelector('.notification-count');
                        if (countEl) {
                            let count = parseInt(countEl.textContent) - 1;
                            if (count <= 0) {
                                countEl.style.display = 'none';
                            } else {
                                countEl.textContent = count;
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Error marking notification as read:', error);
                });
            });
        });
    }
    
    
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.tab-content');
    
    if (tabLinks.length > 0 && tabContents.length > 0) {
        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                
                tabLinks.forEach(l => l.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                
                this.classList.add('active');
                
                
                const target = this.getAttribute('data-tab');
                const content = document.getElementById(target);
                if (content) {
                    content.classList.add('active');
                }
            });
        });
    }

    
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    const alerts = document.querySelectorAll('.alert:not(.alert-persistent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
    const notificationDropdown = document.querySelector('.notification-dropdown');
    const notificationMenu = document.querySelector('.notification-menu');
    
    if (notificationDropdown && notificationMenu) {
        let hideTimeout;
        
        notificationDropdown.addEventListener('mouseenter', function() {
            clearTimeout(hideTimeout);
            notificationMenu.style.display = 'block';
        });
        
        notificationDropdown.addEventListener('mouseleave', function() {
            hideTimeout = setTimeout(() => {
                notificationMenu.style.display = 'none';
            }, 300);
        });
        
        notificationMenu.addEventListener('mouseenter', function() {
            clearTimeout(hideTimeout);
        });
        
        notificationMenu.addEventListener('mouseleave', function() {
            hideTimeout = setTimeout(() => {
                notificationMenu.style.display = 'none';
            }, 300);
        });
    }
});

function loadDashboardData() {
    fetch('get_dashboard_data.php')
        .then(response => response.json())
        .then(data => {
           
            if (data.stats) {
                Object.keys(data.stats).forEach(key => {
                    const element = document.getElementById(`stat-${key}`);
                    if (element) {
                        element.textContent = data.stats[key];
                    }
                });
            }
            
            
            if (data.activity && data.activity.length > 0) {
                const activityList = document.querySelector('.activity-list');
                if (activityList) {
                    activityList.innerHTML = '';
                    
                    data.activity.forEach(item => {
                        const li = document.createElement('li');
                        li.className = 'activity-item';
                        
                        li.innerHTML = `
                            <div class="activity-icon">
                                <i class="${getActivityIcon(item.type)}"></i>
                            </div>
                            <div class="activity-info">
                                <h4 class="activity-title">${item.title}</h4>
                                <div class="activity-meta">
                                    <span class="activity-time">${formatTimeAgo(item.timestamp)}</span>
                                    <span class="activity-user">${item.user}</span>
                                </div>
                            </div>
                        `;
                        
                        activityList.appendChild(li);
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error loading dashboard data:', error);
        });
}

function getActivityIcon(type) {
    switch (type) {
        case 'book_added':
            return 'fas fa-book';
        case 'book_issued':
            return 'fas fa-hand-holding';
        case 'book_returned':
            return 'fas fa-undo';
        case 'fine_paid':
            return 'fas fa-money-bill-wave';
        case 'user_added':
            return 'fas fa-user-plus';
        default:
            return 'fas fa-info-circle';
    }
}


function formatTimeAgo(timestamp) {
    const now = new Date();
    const time = new Date(timestamp);
    const diff = Math.floor((now - time) / 1000); 
    
    if (diff < 60) {
        return 'just now';
    } else if (diff < 3600) {
        const minutes = Math.floor(diff / 60);
        return `${minutes} ${minutes === 1 ? 'minute' : 'minutes'} ago`;
    } else if (diff < 86400) {
        const hours = Math.floor(diff / 3600);
        return `${hours} ${hours === 1 ? 'hour' : 'hours'} ago`;
    } else if (diff < 604800) {
        const days = Math.floor(diff / 86400);
        return `${days} ${days === 1 ? 'day' : 'days'} ago`;
    } else {
        // Format date
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return time.toLocaleDateString(undefined, options);
    }
}

function showLoading(element) {
    if (element) {
        element.innerHTML = '<div class="loader"></div>';
    }
}
function hideLoading(element, originalContent) {
    if (element) {
        element.innerHTML = originalContent;
    }
}

function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        const value = field.value.trim();
        const errorElement = field.parentNode.querySelector('.field-error');
        
        if (!value) {
            isValid = false;
            field.classList.add('is-invalid');
            
            if (!errorElement) {
                const error = document.createElement('div');
                error.className = 'field-error';
                error.textContent = 'This field is required';
                field.parentNode.appendChild(error);
            }
        } else {
            field.classList.remove('is-invalid');
            if (errorElement) {
                errorElement.remove();
            }
        }
    });
    
    return isValid;
}

function autoSaveForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const inputs = form.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
        const savedValue = localStorage.getItem(`${formId}_${input.name}`);
        if (savedValue && input.type !== 'password') {
            input.value = savedValue;
        }
    });
    
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.type !== 'password') {
                localStorage.setItem(`${formId}_${this.name}`, this.value);
            }
        });
    });
    form.addEventListener('submit', function() {
        inputs.forEach(input => {
            localStorage.removeItem(`${formId}_${input.name}`);
        });
    });
}