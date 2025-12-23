/**
 * Deal Chat Manager
 * Handles chat functionality for deal negotiations
 */

class DealChatManager {
    constructor() {
        this.currentChatId = null;
        this.currentChatData = null;
        this.lastMessageId = 0;
        this.pollingInterval = null;
        this.isPolling = false;
    }

    /**
     * Open chat for a bargain
     */
    async openChat(bargainId) {
        try {
            // Fetch or create chat session
            const response = await fetch(`api/deal_chat.php?action=get_or_create&bargain_id=${bargainId}`);
            const data = await response.json();

            if (!data.success) {
                this.showToast(data.message || 'Failed to open chat', 'error');
                return;
            }

            this.currentChatData = data.chat;
            this.currentChatId = data.chat.id;

            // Show chat modal
            this.showChatModal();

            // Load messages
            await this.loadMessages();

            // Mark messages as read
            await this.markAsRead();

            // Start polling for new messages
            this.startPolling();

        } catch (error) {
            console.error('Error opening chat:', error);
            this.showToast('An error occurred while opening the chat', 'error');
        }
    }

    /**
     * Show chat modal
     */
    showChatModal() {
        const chat = this.currentChatData;

        // Create modal HTML
        const modalHTML = `
            <div class="modal fade show" id="chatModal" tabindex="-1" style="display: block;">
                <div class="modal-dialog modal-dialog-scrollable modal-lg">
                    <div class="modal-content">
                        <div class="modal-header" style="background: linear-gradient(135deg, #FF3300 0%, #FF6B35 100%); color: white;">
                            <div class="d-flex align-items-center w-100">
                                <div class="flex-grow-1">
                                    <h5 class="modal-title mb-0">
                                        <i class="fas fa-comments"></i> Chat with ${chat.other_party}
                                    </h5>
                                    <small style="opacity: 0.9;">${chat.product_name} - ৳${parseFloat(chat.bargain_price).toLocaleString()}</small>
                                </div>
                                <button type="button" class="btn-close btn-close-white" onclick="dealChat.closeChat()"></button>
                            </div>
                        </div>
                        <div class="modal-body" id="chatMessagesContainer" style="height: 400px; overflow-y: auto; background: #f5f5f5;">
                            <div id="chatMessages" class="chat-messages-list"></div>
                        </div>
                        <div class="modal-footer" style="background: #fff;">
                            <div class="chat-input-container w-100">
                                <div class="input-group">
                                    <input type="text" id="chatMessageInput" class="form-control" placeholder="Type your message..." autocomplete="off">
                                    <button class="btn btn-primary" onclick="dealChat.sendMessage()" style="background: linear-gradient(135deg, #FF3300 0%, #FF6B35 100%); border: none;">
                                        <i class="fas fa-paper-plane"></i> Send
                                    </button>
                                </div>
                                <div class="chat-suggestions mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-lightbulb"></i> Quick topics: 
                                        <button class="btn btn-sm btn-outline-secondary btn-suggestion" onclick="dealChat.insertSuggestion('What payment method works for you?')">Payment</button>
                                        <button class="btn btn-sm btn-outline-secondary btn-suggestion" onclick="dealChat.insertSuggestion('Where should we meet?')">Location</button>
                                        <button class="btn btn-sm btn-outline-secondary btn-suggestion" onclick="dealChat.insertSuggestion('When are you available?')">Time</button>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-backdrop fade show"></div>
        `;

        // Remove existing modal if any
        const existing = document.getElementById('chatModal');
        if (existing) {
            existing.remove();
            document.querySelector('.modal-backdrop')?.remove();
        }

        // Add to DOM
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Add enter key listener
        document.getElementById('chatMessageInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.sendMessage();
            }
        });

        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }

    /**
     * Load messages
     */
    async loadMessages(lastId = 0) {
        try {
            const url = lastId > 0
                ? `api/deal_chat.php?action=messages&chat_id=${this.currentChatId}&last_message_id=${lastId}`
                : `api/deal_chat.php?action=messages&chat_id=${this.currentChatId}`;

            const response = await fetch(url);
            const data = await response.json();

            if (!data.success) {
                console.error('Failed to load messages:', data.message);
                return;
            }

            if (data.messages.length > 0) {
                this.renderMessages(data.messages, lastId > 0);
                this.lastMessageId = data.messages[data.messages.length - 1].id;

                // Mark as read if new messages
                if (lastId > 0) {
                    await this.markAsRead();
                }
            }

        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }

    /**
     * Render messages
     */
    renderMessages(messages, append = false) {
        const container = document.getElementById('chatMessages');

        if (!append) {
            container.innerHTML = '';
        }

        messages.forEach(msg => {
            const messageHTML = this.createMessageElement(msg);
            container.insertAdjacentHTML('beforeend', messageHTML);
        });

        // Scroll to bottom
        const scrollContainer = document.getElementById('chatMessagesContainer');
        scrollContainer.scrollTop = scrollContainer.scrollHeight;
    }

    /**
     * Create message element
     */
    createMessageElement(msg) {
        const isSystem = msg.message_type === 'system';
        // Explicitly convert to boolean - handle various falsy values
        const isMine = msg.is_mine === true || msg.is_mine === 1 || msg.is_mine === '1';

        // Debug logging - check browser console
        console.log('Creating message:', {
            id: msg.id,
            sender_id: msg.sender_id,
            is_mine_value: msg.is_mine,
            is_mine_type: typeof msg.is_mine,
            is_mine_converted: isMine,
            message_preview: msg.message.substring(0, 20)
        });

        if (isSystem) {
            return `
                <div class="chat-message system-message text-center my-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> ${this.escapeHtml(msg.message)}
                    </small>
                </div>
            `;
        }

        const alignment = isMine ? 'flex-end' : 'flex-start';
        const bgColor = isMine ? '#FF3300' : '#e9ecef';
        const textColor = isMine ? 'white' : '#333';
        const timeAgo = this.getTimeAgo(msg.created_at);

        return `
            <div class="chat-message mb-3" style="display: flex; justify-content: ${alignment};">
                <div style="max-width: 70%;">
                    ${!isMine ? `<small class="text-muted d-block mb-1">${this.escapeHtml(msg.sender_name)}</small>` : ''}
                    <div class="message-bubble" style="background: ${bgColor}; color: ${textColor}; padding: 10px 15px; border-radius: 15px; word-wrap: break-word;">
                        ${this.escapeHtml(msg.message)}
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 11px;">${timeAgo}</small>
                </div>
            </div>
        `;
    }

    /**
     * Send message
     */
    async sendMessage() {
        const input = document.getElementById('chatMessageInput');
        const message = input.value.trim();

        if (!message) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'send');
            formData.append('chat_id', this.currentChatId);
            formData.append('message', message);

            const response = await fetch('api/deal_chat.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (!data.success) {
                this.showToast(data.message || 'Failed to send message', 'error');
                return;
            }

            // Clear input
            input.value = '';

            // Render the message
            this.renderMessages([data.message], true);
            this.lastMessageId = data.message.id;

        } catch (error) {
            console.error('Error sending message:', error);
            this.showToast('An error occurred while sending the message', 'error');
        }
    }

    /**
     * Insert suggestion into input
     */
    insertSuggestion(text) {
        const input = document.getElementById('chatMessageInput');
        input.value = text;
        input.focus();
    }

    /**
     * Mark messages as read
     */
    async markAsRead() {
        try {
            const formData = new FormData();
            formData.append('action', 'mark_read');
            formData.append('chat_id', this.currentChatId);

            await fetch('api/deal_chat.php', {
                method: 'POST',
                body: formData
            });

            // Update badge if exists
            this.updateBadge(0);

        } catch (error) {
            console.error('Error marking as read:', error);
        }
    }

    /**
     * Start polling for new messages
     */
    startPolling() {
        if (this.isPolling) {
            return;
        }

        this.isPolling = true;
        this.pollingInterval = setInterval(async () => {
            await this.loadMessages(this.lastMessageId);
        }, 3000); // Poll every 3 seconds
    }

    /**
     * Stop polling
     */
    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
            this.isPolling = false;
        }
    }

    /**
     * Close chat
     */
    closeChat() {
        this.stopPolling();

        // Remove modal
        document.getElementById('chatModal')?.remove();
        document.querySelector('.modal-backdrop')?.remove();

        // Restore body scroll
        document.body.style.overflow = 'auto';

        // Reset state
        this.currentChatId = null;
        this.currentChatData = null;
        this.lastMessageId = 0;
    }

    /**
     * Update unread badge
     */
    updateBadge(count) {
        const badges = document.querySelectorAll(`.chat-badge[data-bargain-id="${this.currentChatData?.bargain_id}"]`);
        badges.forEach(badge => {
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        });
    }

    /**
     * Utility: Get time ago
     */
    getTimeAgo(timestamp) {
        const now = new Date();
        const time = new Date(timestamp);
        const diffMs = now - time;
        const diffMins = Math.floor(diffMs / 60000);

        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins} min ago`;

        const diffHours = Math.floor(diffMins / 60);
        if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;

        const diffDays = Math.floor(diffHours / 24);
        if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;

        return time.toLocaleDateString();
    }

    /**
     * Utility: Escape HTML
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Show toast notification
     */
    showToast(message, type = 'info') {
        // Use existing toast system if available
        if (window.bargainManager && window.bargainManager.showToast) {
            window.bargainManager.showToast(message, type);
        } else {
            alert(message);
        }
    }
}

// Initialize global instance
const dealChat = new DealChatManager();
window.dealChat = dealChat;
