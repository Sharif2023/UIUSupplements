/**
 * Bargain Manager - Client-side bargain management
 * Handles AJAX operations for bargain submission, responses, and updates
 */

class BargainManager {
    constructor() {
        this.init();
    }

    init() {
        // Bind event listeners
        this.bindEvents();
    }

    bindEvents() {
        // Bargain submission form
        const bargainForm = document.getElementById('bargainForm');
        if (bargainForm) {
            bargainForm.addEventListener('submit', (e) => this.submitBargain(e));
        }

        // Bargain response buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('accept-bargain-btn')) {
                this.respondToBargain(e.target.dataset.bargainId, 'accept');
            }
            if (e.target.classList.contains('reject-bargain-btn')) {
                this.respondToBargain(e.target.dataset.bargainId, 'reject');
            }
            if (e.target.classList.contains('counter-bargain-btn')) {
                this.showCounterOfferModal(e.target.dataset.bargainId);
            }
        });

        // Counter offer form
        const counterForm = document.getElementById('counterOfferForm');
        if (counterForm) {
            counterForm.addEventListener('submit', (e) => this.submitCounterOffer(e));
        }

        // Accept counter offer buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('accept-counter-btn')) {
                this.respondToCounterOffer(e.target.dataset.offerId, 'accept');
            }
            if (e.target.classList.contains('reject-counter-btn')) {
                this.respondToCounterOffer(e.target.dataset.offerId, 'reject');
            }
        });

        // Create deal button
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('create-deal-btn')) {
                this.createDeal(e.target.dataset.bargainId);
            }
        });
    }

    async submitBargain(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        formData.append('action', 'submit');

        try {
            const response = await fetch('api/bargains.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showToast('Bargain submitted successfully!', 'success');
                form.reset();

                // Close modal if exists
                const modal = bootstrap.Modal.getInstance(document.getElementById('bargainModal'));
                if (modal) modal.hide();

                // Reload bargains list if on bargains page
                if (typeof this.loadBargains === 'function') {
                    this.loadBargains();
                }
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (error) {
            console.error('Error submitting bargain:', error);
            this.showToast('An error occurred. Please try again.', 'error');
        }
    }

    async respondToBargain(bargainId, response) {
        if (!confirm(`Are you sure you want to ${response} this bargain?`)) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'respond');
        formData.append('bargain_id', bargainId);
        formData.append('response', response);

        try {
            const res = await fetch('api/bargains.php', {
                method: 'POST',
                body: formData
            });

            const data = await res.json();

            if (data.success) {
                this.showToast(data.message, 'success');

                // Reload page or update UI
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (error) {
            console.error('Error responding to bargain:', error);
            this.showToast('An error occurred. Please try again.', 'error');
        }
    }

    showCounterOfferModal(bargainId) {
        const modal = document.getElementById('counterOfferModal');
        if (modal) {
            document.getElementById('counter_bargain_id').value = bargainId;
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }
    }

    async submitCounterOffer(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        formData.append('action', 'respond');
        formData.append('response', 'counter');

        // Rename fields to match API
        const bargainId = formData.get('counter_bargain_id');
        const counterPrice = formData.get('counter_price');
        const sellerMessage = formData.get('seller_message');

        const apiFormData = new FormData();
        apiFormData.append('action', 'respond');
        apiFormData.append('bargain_id', bargainId);
        apiFormData.append('response', 'counter');
        apiFormData.append('counter_price', counterPrice);
        apiFormData.append('seller_message', sellerMessage);

        try {
            const response = await fetch('api/bargains.php', {
                method: 'POST',
                body: apiFormData
            });

            const data = await response.json();

            if (data.success) {
                this.showToast('Counter offer sent successfully!', 'success');
                form.reset();

                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('counterOfferModal'));
                if (modal) modal.hide();

                // Reload page
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (error) {
            console.error('Error submitting counter offer:', error);
            this.showToast('An error occurred. Please try again.', 'error');
        }
    }

    async respondToCounterOffer(offerId, response) {
        if (!confirm(`Are you sure you want to ${response} this counter offer?`)) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'respond');
        formData.append('offer_id', offerId);
        formData.append('response', response);

        try {
            const res = await fetch('api/offers.php', {
                method: 'POST',
                body: formData
            });

            const data = await res.json();

            if (data.success) {
                this.showToast(data.message, 'success');

                // If accepted, show create deal option
                if (response === 'accept') {
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (error) {
            console.error('Error responding to counter offer:', error);
            this.showToast('An error occurred. Please try again.', 'error');
        }
    }

    async createDeal(bargainId) {
        // Get bargain details first
        try {
            const response = await fetch(`api/bargains.php?action=details&bargain_id=${bargainId}`);
            const data = await response.json();

            if (data.success) {
                const bargain = data.data;

                // Create deal
                const formData = new FormData();
                formData.append('action', 'create');
                formData.append('product_id', bargain.product_id);
                formData.append('buyer_id', bargain.buyer_id);
                formData.append('final_price', bargain.bargain_price);
                formData.append('bargain_id', bargainId);

                const dealResponse = await fetch('api/deals.php', {
                    method: 'POST',
                    body: formData
                });

                const dealData = await dealResponse.json();

                if (dealData.success) {
                    this.showToast('Deal created successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = `mydeals.php?highlight=${dealData.data.deal_id}`;
                    }, 1500);
                } else {
                    this.showToast(dealData.message, 'error');
                }
            }
        } catch (error) {
            console.error('Error creating deal:', error);
            this.showToast('An error occurred. Please try again.', 'error');
        }
    }

    showToast(message, type = 'info') {
        // Create toast element
        const toastContainer = document.getElementById('toastContainer') || this.createToastContainer();

        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        toast.innerHTML = `
            <div class="toast-icon">
                ${type === 'success' ? '<i class="fas fa-check-circle"></i>' :
                type === 'error' ? '<i class="fas fa-exclamation-circle"></i>' :
                    '<i class="fas fa-info-circle"></i>'}
            </div>
            <div class="toast-message">${message}</div>
            <button class="toast-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;

        toastContainer.appendChild(toast);

        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.classList.add('fade-out');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container';
        document.body.appendChild(container);
        return container;
    }

    async loadBargains(type = 'buyer') {
        try {
            const response = await fetch(`api/bargains.php?action=list&type=${type}`);
            const data = await response.json();

            if (data.success) {
                this.renderBargains(data.data, type);
            } else {
                console.error('Error loading bargains:', data.message);
            }
        } catch (error) {
            console.error('Error loading bargains:', error);
        }
    }

    renderBargains(bargains, type) {
        const container = document.getElementById('bargainsContainer');
        if (!container) return;

        if (bargains.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No Bargains Found</h3>
                    <p>You don't have any ${type === 'buyer' ? 'submitted' : 'received'} bargains yet.</p>
                </div>
            `;
            return;
        }

        let html = '';
        bargains.forEach(bargain => {
            html += this.renderBargainCard(bargain, type);
        });

        container.innerHTML = html;
    }

    renderBargainCard(bargain, type) {
        const statusClass = {
            'pending': 'status-pending',
            'accepted': 'status-accepted',
            'rejected': 'status-rejected',
            'countered': 'status-countered',
            'deal_done': 'status-completed'
        }[bargain.status] || '';

        return `
            <div class="bargain-card ${statusClass}">
                <div class="bargain-product">
                    <img src="${bargain.image_path}" alt="${bargain.product_name}">
                    <div class="bargain-info">
                        <h4>${bargain.product_name}</h4>
                        <p class="category">${bargain.category}</p>
                        <p class="price">Original: ৳${bargain.original_price} | Bargain: ৳${bargain.bargain_price}</p>
                    </div>
                </div>
                <div class="bargain-status">
                    <span class="status-badge ${statusClass}">${bargain.status}</span>
                    <small>${new Date(bargain.created_at).toLocaleDateString()}</small>
                </div>
                <div class="bargain-actions">
                    ${this.renderBargainActions(bargain, type)}
                </div>
            </div>
        `;
    }

    renderBargainActions(bargain, type) {
        if (type === 'seller' && bargain.status === 'pending') {
            return `
                <button class="btn btn-sm btn-success accept-bargain-btn" data-bargain-id="${bargain.id}">
                    <i class="fas fa-check"></i> Accept
                </button>
                <button class="btn btn-sm btn-warning counter-bargain-btn" data-bargain-id="${bargain.id}">
                    <i class="fas fa-exchange-alt"></i> Counter
                </button>
                <button class="btn btn-sm btn-danger reject-bargain-btn" data-bargain-id="${bargain.id}">
                    <i class="fas fa-times"></i> Reject
                </button>
            `;
        } else if (type === 'buyer' && bargain.status === 'countered') {
            return `
                <button class="btn btn-sm btn-info" onclick="viewCounterOffer(${bargain.id})">
                    <i class="fas fa-eye"></i> View Counter Offer
                </button>
            `;
        } else if (bargain.status === 'accepted') {
            return `
                <button class="btn btn-sm btn-primary create-deal-btn" data-bargain-id="${bargain.id}">
                    <i class="fas fa-handshake"></i> Create Deal
                </button>
            `;
        }
        return '';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    window.bargainManager = new BargainManager();
});
