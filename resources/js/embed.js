import '../../css/embed.css';

class OrderFormEmbed {
    constructor(formId, options = {}) {
        this.formId = formId;
        this.options = options;
        this.containerId = options.containerId || 'order-form-container';
        this.currentStep = 1;
        this.selectedProducts = {};
        this.csrfToken = this.getCSRFToken();
        this.init().catch(err => {
            console.error('OrderFormEmbed initialization failed:', err);
            this.showError('Failed to load order form. Please refresh the page.');
        });
    }

    // Add CSRF token handling to prevent 419 errors
    getCSRFToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : '';
    }

    async init() {
        try {
            this.showLoading();
            const response = await fetch(`${this.options.apiBase || ''}/embeddable-forms/${this.formId}`);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            this.config = await response.json();
            this.renderProductSelection();
        } catch (error) {
            console.error('Error initializing form:', error);
            this.showError('Failed to load order form. Please try again later.');
            throw error;
        }
    }

    async init() {
        try {
            this.showLoading();
            const response = await fetch(`${this.options.apiBase || ''}/embeddable-forms/${this.formId}`);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            this.config = await response.json();
            this.renderProductSelection();
        } catch (error) {
            console.error('Error initializing form:', error);
            this.showError('Failed to load order form. Please try again later.');
            throw error;
        }
    }

    showLoading() {
        const container = document.getElementById(this.containerId);
        if (container) {
            container.innerHTML = `
                <div class="of-loading-container">
                    <div class="of-loading-spinner"></div>
                    <p class="of-loading-text">Loading order form...</p>
                </div>
            `;
        }
    }

    showError(message) {
        const container = document.getElementById(this.containerId);
        if (container) {
            container.innerHTML = `
                <div class="of-error-message">
                    <svg class="of-error-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <p>${message}</p>
                </div>
            `;
        }
    }

    renderProductSelection() {
        const container = document.getElementById(this.containerId);
        if (!container) return;

        if (this.currentStep === 1) {
            // Only update the selected products list if we're on step 1
            this.updateSelectedProductsList();
            this.toggleContinueButton();
        }

        // Use admin-configured colors or defaults
        const primaryColor = this.config.primary_color || '#3b82f6';
        const cardBgColor = this.config.card_bg_color || '#ffffff';
        const cardTextColor = this.config.card_text_color || '#1f2937';
        const cardBorderColor = this.config.card_border_color || '#e5e7eb';
        const buttonTextColor = this.config.button_text_color || '#ffffff';

        container.innerHTML = `
            <div class="of-container" style="--primary-color: ${primaryColor}; 
                --card-bg: ${cardBgColor}; --card-text: ${cardTextColor}; 
                --card-border: ${cardBorderColor}; --button-text: ${buttonTextColor}">
                
                <div class="of-header">
                    <h2 class="of-title">
                        ${this.config.title || 'Select Products'}
                    </h2>
                    <div class="of-step-indicator">
                        <span class="of-step ${this.currentStep >= 1 ? 'active' : ''}">1</span>
                        <span class="of-step-divider"></span>
                        <span class="of-step ${this.currentStep >= 2 ? 'active' : ''}">2</span>
                    </div>
                </div>
                
                <div class="of-content">
                    <div class="of-products-grid">
                        ${this.config.products.map(product => `
                            <div class="of-product-card">
                                ${this.config.show_product_images && product.image ? `
                                    <div class="of-product-image-container">
                                        <div class="of-product-badge">${product.badge_text || ''}</div>
                                        <img src="${product.image}" alt="${product.title}" class="of-product-image">
                                        <div class="of-product-overlay">
                                            <button class="of-product-quickview" data-product-id="${product.id}">
                                                <svg viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                                </svg>
                                                Quick View
                                            </button>
                                        </div>
                                    </div>
                                ` : ''}
                                
                                <div class="of-product-details">
                                    <div class="of-product-meta">
                                        ${product.category ? `<span class="of-product-category">${product.category}</span>` : ''}
                                        <div class="of-product-rating">
                                            ${this.renderRatingStars(product.rating || 0)}
                                        </div>
                                    </div>
                                    <h3 class="of-product-title">${product.title}</h3>
                                    ${product.description ? `<p class="of-product-description">${product.description}</p>` : ''}
                                    <div class="of-product-footer">
                                        <div class="of-product-pricing">
                                            ${product.original_price ? `
                                                <span class="of-product-original-price">₦${product.original_price.toLocaleString()}</span>
                                            ` : ''}
                                            <span class="of-product-price">₦${product.price ? product.price.toLocaleString() : '0.00'}</span>
                                        </div>
                                        <button class="of-product-select-btn ${this.selectedProducts[product.id] ? 'selected' : ''}" data-product-id="${product.id}">
                                            ${this.selectedProducts[product.id] ? `
                                                <svg viewBox="0 0 20 20" fill="currentColor" class="of-cart-icon">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                Added
                                            ` : `
                                                <svg viewBox="0 0 20 20" fill="currentColor" class="of-cart-icon">
                                                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                                                </svg>
                                                Add to Cart
                                            `}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                    
                    <div class="of-selected-products" id="selected-products-container">
                        <h3 class="of-selected-title">Your Cart</h3>
                        <div class="of-selected-list" id="selected-products-list"></div>
                        <div class="of-selected-total">
                            <span>Subtotal:</span>
                            <span class="of-subtotal-amount">₦0.00</span>
                        </div>
                        <button class="of-continue-btn" id="continue-to-form" ${Object.keys(this.selectedProducts).length === 0 ? 'disabled' : ''}>
                            Proceed to Checkout
                        </button>
                    </div>
                </div>
            </div>
        `;

        this.updateSelectedProductsList();
        this.toggleContinueButton();
        this.bindProductSelectionEvents();
    }

    renderRatingStars(rating) {
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 >= 0.5;
        let stars = '';
        
        for (let i = 0; i < 5; i++) {
            if (i < fullStars) {
                stars += `<svg viewBox="0 0 20 20" fill="currentColor" class="of-star-icon full">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>`;
            } else if (i === fullStars && hasHalfStar) {
                stars += `<svg viewBox="0 0 20 20" fill="currentColor" class="of-star-icon half">
                    <defs>
                        <linearGradient id="half-star-${i}" x1="0" x2="100%" y1="0" y2="0">
                            <stop offset="50%" stop-color="currentColor" />
                            <stop offset="50%" stop-color="#d1d5db" />
                        </linearGradient>
                    </defs>
                    <path fill="url(#half-star-${i})" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>`;
            } else {
                stars += `<svg viewBox="0 0 20 20" fill="#d1d5db" class="of-star-icon empty">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>`;
            }
        }
        
        return stars;
    }


    renderOrderForm() {
        const container = document.getElementById(this.containerId);
        this.currentStep = 2;
        
        container.innerHTML = `
            <div class="of-container">
                <div class="of-header">
                    <h2 class="of-title" style="color: ${this.config.primary_color || '#3b82f6'}">
                        ${this.config.title || 'Order Information'}
                    </h2>
                    <div class="of-step-indicator">
                        <span class="of-step active">1</span>
                        <span class="of-step-divider"></span>
                        <span class="of-step active">2</span>
                    </div>
                </div>
                
                <div class="of-content">
                    <div class="of-order-summary">
                        <h3 class="of-summary-title">Order Summary</h3>
                        <div class="of-summary-items" id="order-summary-items">
                            ${Object.values(this.selectedProducts).map(item => `
                                <div class="of-summary-item">
                                    <span class="of-summary-product">${item.title} × ${item.quantity}</span>
                                    <span class="of-summary-price">₦${(item.price * item.quantity).toLocaleString()}</span>
                                </div>
                            `).join('')}
                        </div>
                        <div class="of-summary-total">
                            <span>Total:</span>
                            <span class="of-total-amount">₦${this.calculateTotal().toLocaleString()}</span>
                        </div>
                    </div>
                    
                    <form id="order-form" class="of-order-form">
                        <h3 class="of-form-title">Customer Information</h3>
                        
                        <div class="of-form-grid">
                            <div class="of-form-group">
                                <label for="full_name" class="of-form-label">Full Name *</label>
                                <input type="text" id="full_name" name="full_name" required class="of-form-input">
                            </div>
                            
                            <div class="of-form-group">
                                <label for="email" class="of-form-label">Email </label>
                                <input type="email" id="email" name="email" class="of-form-input">
                            </div>
                            
                            <div class="of-form-group">
                                <label for="mobile" class="of-form-label">Phone Number *</label>
                                <input type="tel" id="mobile" name="mobile" required class="of-form-input">
                            </div>
                            
                            <div class="of-form-group">
                                <label for="address" class="of-form-label">Delivery Address *</label>
                                <textarea id="address" name="address" rows="2" required class="of-form-input"></textarea>
                            </div>
                            
                            <div class="of-form-group">
                                <label for="state" class="of-form-label">State *</label>
                                <input type="text" id="state" name="state" required class="of-form-input">
                            </div>
                            
                            <div class="of-form-group">
                                <label for="notes" class="of-form-label">Order Notes</label>
                                <textarea id="notes" name="notes" rows="2" class="of-form-input"></textarea>
                            </div>
                        </div>
                        
                        <div class="of-form-actions">
                            <button type="button" class="of-back-btn" id="back-to-products">Back to Products</button>
                            <button type="submit" class="of-submit-btn" style="background-color: ${this.config.primary_color || '#3b82f6'}">
                                Complete Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        this.bindOrderFormEvents();
    }

    calculateTotal() {
        return Object.values(this.selectedProducts).reduce(
            (total, item) => total + (item.price * item.quantity), 0
        );
    }

    bindProductSelectionEvents() {
        document.querySelectorAll('.of-product-select-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const productId = e.currentTarget.dataset.productId;
                const product = this.config.products.find(p => p.id == productId);
                
                if (product) {
                    if (this.selectedProducts[productId]) {
                        // Deselect if already selected
                        delete this.selectedProducts[productId];
                        e.currentTarget.classList.remove('selected');
                        e.currentTarget.innerHTML = `
                            <svg viewBox="0 0 20 20" fill="currentColor" class="of-cart-icon">
                                <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                            </svg>
                            Add to Cart
                        `;
                    } else {
                        // Select product
                        this.selectedProducts[productId] = {
                            ...product,
                            quantity: 1
                        };
                        e.currentTarget.classList.add('selected');
                        e.currentTarget.innerHTML = `
                            <svg viewBox="0 0 20 20" fill="currentColor" class="of-cart-icon">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Added
                        `;
                    }
                    
                    this.updateSelectedProductsList();
                    this.toggleContinueButton();
                }
            });
        });

        document.getElementById('continue-to-form').addEventListener('click', () => {
            this.renderOrderForm();
        });
    }

    toggleContinueButton() {
        const continueBtn = document.getElementById('continue-to-form');

        if (continueBtn) {
            const hasProducts = Object.keys(this.selectedProducts).length > 0;
            continueBtn.disabled = !hasProducts;
            continueBtn.classList.toggle('active', hasProducts);
            
            // Update subtotal
            const subtotalEl = document.querySelector('.of-subtotal-amount');
            if (subtotalEl) {
                subtotalEl.textContent = `₦${this.calculateTotal().toLocaleString()}`;
            }
        }
    }


    updateSelectedProductsList() {
        const container = document.getElementById('selected-products-list');
        if (!container) return;
        
        container.innerHTML = Object.values(this.selectedProducts).map(item => `
            <div class="of-selected-item">
                <div class="of-selected-item-info">
                    <span class="of-selected-item-name">${item.title}</span>
                    <span class="of-selected-item-price">₦${item.price.toLocaleString()}</span>
                </div>
                <div class="of-selected-item-actions">
                    <button class="of-quantity-btn" data-action="decrease" data-product-id="${item.id}">−</button>
                    <span class="of-quantity-value">${item.quantity}</span>
                    <button class="of-quantity-btn" data-action="increase" data-product-id="${item.id}">+</button>
                    <button class="of-remove-btn" data-product-id="${item.id}">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="of-remove-icon">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        `).join('');
        
        // Bind events for the new elements
        document.querySelectorAll('.of-quantity-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const action = e.currentTarget.dataset.action;
                const productId = e.currentTarget.dataset.productId;
                
                if (action === 'increase') {
                    this.selectedProducts[productId].quantity += 1;
                } else if (action === 'decrease' && this.selectedProducts[productId].quantity > 1) {
                    this.selectedProducts[productId].quantity -= 1;
                }
                
                this.updateSelectedProductsList();
                this.toggleContinueButton(); // Add this line
            });
        });
        
        document.querySelectorAll('.of-remove-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const productId = e.currentTarget.dataset.productId;
                delete this.selectedProducts[productId];
                this.updateSelectedProductsList();
                
                // Reset the select button in the grid
                const selectBtn = document.querySelector(`.of-product-card[data-product-id="${productId}"] .of-product-select-btn`);
                if (selectBtn) {
                    selectBtn.textContent = 'Select';
                    selectBtn.classList.remove('selected');
                }
                
                // Hide selected container if empty
                if (Object.keys(this.selectedProducts).length === 0) {
                    document.getElementById('selected-products-container').style.display = 'none';
                }
            });
        });
    }

    bindOrderFormEvents() {
        document.getElementById('back-to-products').addEventListener('click', () => {
            this.renderProductSelection();
        });

        document.getElementById('order-form').addEventListener('submit', (e) => {
            this.handleSubmit(e);
        });
    }

    async handleSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('.of-submit-btn');
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <span class="of-spinner"></span> Processing Order...
        `;
        
        try {
            const response = await fetch(`${this.options.apiBase || ''}/orders`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken // Include CSRF token
                },
                body: JSON.stringify({
                    form_id: this.formId,
                    customer: {
                        full_name: form.full_name.value,
                        email: form.email.value,
                        mobile: form.mobile.value,
                        address: form.address.value,
                        state: form.state.value,
                        notes: form.notes.value
                    },
                    products: Object.values(this.selectedProducts).map(item => ({
                        product_id: item.id,
                        quantity: item.quantity,
                        price: item.price
                    })),

                })
            });
            
            const result = await response.json();
            
            if (response.ok) {
                this.showSuccessScreen();
            } else {
                this.showMessage(result.message || 'Error placing order', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Complete Order';
            }
        } catch (error) {
            console.error('Submission error:', error);
            console.log("PRODUCT >> ", this.selectedProducts);
            this.showMessage('Network error. Please try again.', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Complete Order';
        }
    }

    showSuccessScreen() {
        const container = document.getElementById(this.containerId);
        container.innerHTML = `
            <div class="of-success-container">
                <div class="of-success-icon">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <h2 class="of-success-title">Order Placed Successfully!</h2>
                <p class="of-success-message">Thank you for your order. We'll contact you shortly with more details.</p>
                <button class="of-success-btn" id="new-order-btn" style="background-color: ${this.config.primary_color || '#3b82f6'}">
                    Start New Order
                </button>
            </div>
        `;
        
        document.getElementById('new-order-btn').addEventListener('click', () => {
            this.currentStep = 1; // Reset the step counter
            this.selectedProducts = {};
            this.renderProductSelection();
        });
    }

    showMessage(text, type) {
        const messageEl = document.createElement('div');
        messageEl.className = `of-message of-message-${type}`;
        messageEl.textContent = text;
        
        const container = document.getElementById(this.containerId);
        container.prepend(messageEl);
        
        setTimeout(() => {
            messageEl.classList.add('of-message-fade');
            setTimeout(() => messageEl.remove(), 300);
        }, 5000);
    }
}

// Initialization and auto-loading code remains the same
(function() {
    function getFormId() {
        const container = document.getElementById('order-form-container');
        if (container && container.dataset.formId) return container.dataset.formId;
        
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('form_id')) return urlParams.get('form_id');
        
        const hashParams = new URLSearchParams(window.location.hash.substring(1));
        if (hashParams.has('form_id')) return hashParams.get('form_id');
        
        const scriptTags = document.getElementsByTagName('script');
        for (let script of scriptTags) {
            if (script.src.includes('order-form.js')) {
                const scriptParams = new URL(script.src).searchParams;
                if (scriptParams.has('form_id')) return scriptParams.get('form_id');
            }
        }
        
        return null;
    }

    function initialize() {
        const formId = getFormId();
        if (formId) {
            const container = document.getElementById('order-form-container');
            const apiBase = container ? container.dataset.apiBase : null;
            
            new OrderFormEmbed(formId, {
                apiBase: apiBase || window.orderFormApiBase || null
            });
        } else {
            console.error('OrderFormEmbed: No form_id found');
            const container = document.getElementById('order-form-container');
            if (container) {
                container.innerHTML = `
                    <div class="of-error-message">
                        <svg class="of-error-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <p>Order form configuration missing (form_id required)</p>
                    </div>
                `;
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
})();