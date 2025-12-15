/**
 * Notification System for Admin Dashboard
 * Handles fetching, displaying, and managing notifications
 */

class NotificationSystem {
    constructor() {
        this.apiUrl = 'notifications_api.php';
        this.unreadCount = 0;
        this.notifications = [];
        this.pollInterval = null;
        this.pollIntervalMs = 30000; // Poll every 30 seconds
        
        this.init();
    }
    
    init() {
        // Load notifications on page load
        this.loadNotifications();
        this.loadUnreadCount();
        
        // Set up polling for new notifications
        this.startPolling();
        
        // Set up event listeners
        this.setupEventListeners();
    }
    
    setupEventListeners() {
        // Notification bell click
        const bellIcon = document.getElementById('notificationBell');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const bellContainer = bellIcon?.closest('.notification-bell-container');
        
        if (bellIcon && notificationDropdown) {
            bellIcon.addEventListener('click', (e) => {
                e.stopPropagation();
                notificationDropdown.classList.toggle('show');
                if (notificationDropdown.classList.contains('show')) {
                    this.loadNotifications();
                }
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (bellContainer && !bellContainer.contains(e.target)) {
                    notificationDropdown.classList.remove('show');
                }
            });
        }
        
        // Mark all as read button
        const markAllReadBtn = document.getElementById('markAllReadBtn');
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', () => {
                this.markAllAsRead();
            });
        }
        
        // Clear all notifications button
        const clearAllBtn = document.getElementById('clearAllNotificationsBtn');
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', () => {
                if (confirm('Are you sure you want to delete all notifications?')) {
                    this.deleteAllNotifications();
                }
            });
        }
    }
    
    async loadNotifications() {
        try {
            const response = await fetch(`${this.apiUrl}?action=list&limit=20`);
            const data = await response.json();
            
            if (data.success) {
                this.notifications = data.data;
                this.renderNotifications();
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }
    
    async loadUnreadCount() {
        try {
            const response = await fetch(`${this.apiUrl}?action=unread_count`);
            const data = await response.json();
            
            if (data.success) {
                this.unreadCount = data.count;
                this.updateBadge();
            }
        } catch (error) {
            console.error('Error loading unread count:', error);
        }
    }
    
    updateBadge() {
        const badge = document.getElementById('notificationBadge');
        if (badge) {
            if (this.unreadCount > 0) {
                badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
    }
    
    renderNotifications() {
        const container = document.getElementById('notificationList');
        if (!container) return;
        
        if (this.notifications.length === 0) {
            container.innerHTML = `
                <div class="notification-empty">
                    <i class="bi bi-bell-slash"></i>
                    <p>No notifications</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = this.notifications.map(notif => {
            const iconClass = this.getIconClass(notif.type);
            const readClass = notif.isRead ? '' : 'unread';
            
            return `
                <div class="notification-item ${readClass}" data-id="${notif.id}">
                    <div class="notification-icon ${notif.type}">
                        <i class="bi ${iconClass}"></i>
                    </div>
                    <div class="notification-content">
                        <h6 class="notification-title">${this.escapeHtml(notif.title)}</h6>
                        <p class="notification-message">${this.escapeHtml(notif.message)}</p>
                        <small class="notification-time">${notif.timeAgo}</small>
                    </div>
                    <div class="notification-actions">
                        ${!notif.isRead ? `
                            <button class="btn-notification-action" onclick="notificationSystem.markAsRead(${notif.id})" title="Mark as read">
                                <i class="bi bi-check-circle"></i>
                            </button>
                        ` : ''}
                        <button class="btn-notification-action" onclick="notificationSystem.deleteNotification(${notif.id})" title="Delete">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </div>
            `;
        }).join('');
    }
    
    getIconClass(type) {
        const icons = {
            'info': 'bi-info-circle',
            'success': 'bi-check-circle',
            'warning': 'bi-exclamation-triangle',
            'error': 'bi-x-circle',
            'system': 'bi-gear'
        };
        return icons[type] || icons.info;
    }
    
    async markAsRead(id) {
        try {
            const formData = new FormData();
            formData.append('action', 'mark_read');
            formData.append('id', id);
            
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Update local state
                const notif = this.notifications.find(n => n.id === id);
                if (notif) {
                    notif.isRead = true;
                }
                this.renderNotifications();
                this.loadUnreadCount();
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }
    
    async markAllAsRead() {
        try {
            const formData = new FormData();
            formData.append('action', 'mark_all_read');
            
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Update local state
                this.notifications.forEach(notif => {
                    notif.isRead = true;
                });
                this.renderNotifications();
                this.loadUnreadCount();
            }
        } catch (error) {
            console.error('Error marking all as read:', error);
        }
    }
    
    async deleteNotification(id) {
        try {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Remove from local state
                this.notifications = this.notifications.filter(n => n.id !== id);
                this.renderNotifications();
                this.loadUnreadCount();
            }
        } catch (error) {
            console.error('Error deleting notification:', error);
        }
    }
    
    async deleteAllNotifications() {
        try {
            const formData = new FormData();
            formData.append('action', 'delete_all');
            
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.notifications = [];
                this.renderNotifications();
                this.loadUnreadCount();
            }
        } catch (error) {
            console.error('Error deleting all notifications:', error);
        }
    }
    
    startPolling() {
        // Poll for new notifications every 30 seconds
        this.pollInterval = setInterval(() => {
            this.loadUnreadCount();
        }, this.pollIntervalMs);
    }
    
    stopPolling() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize notification system when DOM is ready
let notificationSystem;
document.addEventListener('DOMContentLoaded', () => {
    notificationSystem = new NotificationSystem();
});

// Clean up polling when page is hidden
document.addEventListener('visibilitychange', () => {
    if (notificationSystem) {
        if (document.hidden) {
            notificationSystem.stopPolling();
        } else {
            notificationSystem.startPolling();
            notificationSystem.loadUnreadCount();
        }
    }
});
