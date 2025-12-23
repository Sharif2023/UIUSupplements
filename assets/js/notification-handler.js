/**
 * Notification Handler - Real-time notification management
 * Polls for new notifications and updates UI
 */

class NotificationHandler {
    constructor() {
        this.pollInterval = 30000; // Poll every 30 seconds
        this.intervalId = null;
        this.init();
    }

    init() {
        this.updateNotificationBadge();
        this.startPolling();
        this.bindEvents();
    }

    bindEvents() {
        // Mark notification as read when clicked
        document.addEventListener('click', (e) => {
            if (e.target.closest('.notification-item')) {
                const notificationId = e.target.closest('.notification-item').dataset.notificationId;
                if (notificationId) {
                    this.markAsRead(notificationId);
                }
            }
        });

        // Mark all as read button
        const markAllBtn = document.getElementById('markAllReadBtn');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', () => this.markAllAsRead());
        }
    }

    startPolling() {
        // Poll immediately
        this.pollNotifications();

        // Then poll at intervals
        this.intervalId = setInterval(() => {
            this.pollNotifications();
        }, this.pollInterval);
    }

    stopPolling() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
    }

    async pollNotifications() {
        try {
            const response = await fetch('api/notifications.php?action=unread-count');
            const data = await response.json();

            if (data.success) {
                this.updateBadge(data.data.count);

                // If there are new notifications, show a toast
                if (data.data.count > 0 && data.data.latest) {
                    this.showNewNotificationToast(data.data.latest);
                }
            }
        } catch (error) {
            console.error('Error polling notifications:', error);
        }
    }

    async updateNotificationBadge() {
        try {
            const response = await fetch('api/notifications.php?action=unread-count');
            const data = await response.json();

            if (data.success) {
                this.updateBadge(data.data.count);
            }
        } catch (error) {
            console.error('Error updating notification badge:', error);
        }
    }

    updateBadge(count) {
        const badges = document.querySelectorAll('.notification-badge, .bargain-notification-badge');
        badges.forEach(badge => {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        });
    }

    async markAsRead(notificationId) {
        try {
            const formData = new FormData();
            formData.append('action', 'mark-read');
            formData.append('notification_id', notificationId);

            const response = await fetch('api/notifications.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Update UI
                const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notificationItem) {
                    notificationItem.classList.remove('unread');
                }

                // Update badge
                this.updateNotificationBadge();
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    async markAllAsRead() {
        try {
            const formData = new FormData();
            formData.append('action', 'mark-all-read');

            const response = await fetch('api/notifications.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Update UI
                document.querySelectorAll('.notification-item.unread').forEach(item => {
                    item.classList.remove('unread');
                });

                // Update badge
                this.updateBadge(0);
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    }

    showNewNotificationToast(notification) {
        // Only show if not already shown
        const lastShownId = localStorage.getItem('lastNotificationId');
        if (lastShownId && parseInt(lastShownId) >= notification.id) {
            return;
        }

        // Create toast
        const toast = document.createElement('div');
        toast.className = 'notification-toast';
        toast.innerHTML = `
            <div class="notification-toast-header">
                <i class="fas fa-bell"></i>
                <strong>${notification.title}</strong>
                <button class="close-toast" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="notification-toast-body">
                ${notification.message}
            </div>
        `;

        // Add to page
        const container = document.getElementById('notificationToastContainer') || this.createToastContainer();
        container.appendChild(toast);

        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.classList.add('fade-out');
            setTimeout(() => toast.remove(), 300);
        }, 5000);

        // Save last shown ID
        localStorage.setItem('lastNotificationId', notification.id);
    }

    createToastContainer() {
        const container = document.createElement('div');
        container.id = 'notificationToastContainer';
        container.className = 'notification-toast-container';
        document.body.appendChild(container);
        return container;
    }

    async loadNotifications() {
        try {
            const response = await fetch('api/notifications.php?action=list&type=bargain');
            const data = await response.json();

            if (data.success) {
                this.renderNotifications(data.data);
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }

    renderNotifications(notifications) {
        const container = document.getElementById('notificationsContainer');
        if (!container) return;

        if (notifications.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <h3>No Notifications</h3>
                    <p>You're all caught up!</p>
                </div>
            `;
            return;
        }

        let html = '';
        notifications.forEach(notification => {
            html += this.renderNotificationItem(notification);
        });

        container.innerHTML = html;
    }

    renderNotificationItem(notification) {
        const unreadClass = notification.is_read == 0 ? 'unread' : '';
        const iconClass = {
            'bargain': 'fa-tag',
            'bargain_accepted': 'fa-check-circle',
            'bargain_rejected': 'fa-times-circle',
            'bargain_countered': 'fa-exchange-alt',
            'counter_offer': 'fa-hand-holding-usd',
            'deal_completed': 'fa-handshake'
        }[notification.type] || 'fa-bell';

        return `
            <div class="notification-item ${unreadClass}" data-notification-id="${notification.id}">
                <div class="notification-icon">
                    <i class="fas ${iconClass}"></i>
                </div>
                <div class="notification-content">
                    <h5>${notification.title}</h5>
                    <p>${notification.message}</p>
                    <small>${this.formatDate(notification.created_at)}</small>
                </div>
                ${notification.link ? `
                    <a href="${notification.link}" class="notification-link">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                ` : ''}
            </div>
        `;
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        const seconds = Math.floor(diff / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);

        if (seconds < 60) return 'Just now';
        if (minutes < 60) return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
        if (hours < 24) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
        if (days < 7) return `${days} day${days > 1 ? 's' : ''} ago`;

        return date.toLocaleDateString();
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    window.notificationHandler = new NotificationHandler();
});

// Clean up on page unload
window.addEventListener('beforeunload', () => {
    if (window.notificationHandler) {
        window.notificationHandler.stopPolling();
    }
});
