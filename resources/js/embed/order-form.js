import '../../css/embed.css';

class OrderFormEmbed {
    constructor(formId, options = {}) {
        this.formId = formId;
        this.options = options;
        this.containerId = options.containerId || 'order-form-container';
        this.redirectUrl = options.redirectUrl || null;
        this.csrfToken = this.getCSRFToken();
        this.init().catch(err => {
            console.error('OrderFormEmbed initialization failed:', err);
            this.showError('Failed to load order form. Please refresh the page.');
        });
    }

    getCSRFToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : '';
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

    async init() {
        try {
            this.showLoading();
            const response = await fetch(`${this.options.apiBase || ''}/embeddable-forms/${this.formId}`);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            this.config = await response.json();
            this.renderForm();
        } catch (error) {
            console.error('Error initializing form:', error);
            this.showError('Failed to load order form. Please try again later.');
            throw error;
        }
    }

    renderForm() {
        const container = document.getElementById(this.containerId);
        if (!container) return;

        const primaryColor = this.config.primary_color || '#3b82f6';
        const buttonTextColor = this.config.button_text_color || '#ffffff';

        container.innerHTML = `
            <div class="of-container" style="--primary-color: ${primaryColor}; --button-text: ${buttonTextColor}">
                <div class="of-header">
                    <h2 class="of-title" style="color: ${primaryColor}">
                        ${this.config.title || 'Order Form'}
                    </h2>
                </div>
                
                <form id="order-form" class="of-order-form">
                    <div class="of-products-section">
                        <h3 class="of-section-title">Products</h3>
                        <div class="of-products-list">
                            ${this.config.products.map(product => `
                                <div class="of-product-item">
                                    <div class="of-product-info">
                                        <h4 class="of-product-name">${product.title}</h4>
                                        <span class="of-product-price">₦${product.price.toLocaleString()}</span>
                                    </div>
                                    <div class="of-product-quantity">
                                        <button type="button" class="of-quantity-btn" data-action="decrease" data-product-id="${product.id}">−</button>
                                        <input type="number" name="products[${product.id}]" value="1" min="1" class="of-quantity-input">
                                        <button type="button" class="of-quantity-btn" data-action="increase" data-product-id="${product.id}">+</button>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    
                    <div class="of-order-summary">
                        <h3 class="of-section-title">Order Summary</h3>
                        <div class="of-summary-items" id="summary-items"></div>
                        <div class="of-summary-total">
                            <span>Total:</span>
                            <span id="order-total">₦${this.calculateTotal().toLocaleString()}</span>
                        </div>
                    </div>
                    
                    <div class="of-customer-info">
                        <h3 class="of-section-title">Customer Information</h3>
                        <div class="of-form-grid">
                            <div class="of-form-group">
                                <label for="full_name" class="of-form-label">Full Name *</label>
                                <input type="text" id="full_name" name="full_name" required class="of-form-input">
                            </div>
                            
                            <div class="of-form-group">
                                <label for="email" class="of-form-label">Email</label>
                                <input type="email" id="email" name="email" class="of-form-input">
                            </div>
                            
                            <div class="of-form-group">
                                <label for="phone" class="of-form-label">Phone Number *</label>
                                <input type="tel" id="phone" name="phone" required class="of-form-input">
                            </div>

                            <div class="of-form-group">
                                <label for="state" class="of-form-label">State Number *</label>
                                <input type="text" id="state" name="state" required class="of-form-input">
                            </div>
                            
                            <div class="of-form-group">
                                <label for="address" class="of-form-label">Delivery Address *</label>
                                <textarea id="address" name="address" rows="2" required class="of-form-input"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="of-form-actions">
                        <button type="submit" class="of-submit-btn" disabled>
                            Place Order
                        </button>
                    </div>
                </form>
            </div>
        `;

        this.bindEvents();
        this.updateSummary();
        this.validateForm();
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

    bindEvents() {
        // Quantity buttons
        document.querySelectorAll('.of-quantity-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const action = e.currentTarget.dataset.action;
                const productId = e.currentTarget.dataset.productId;
                const input = e.currentTarget.parentElement.querySelector('.of-quantity-input');
                let value = parseInt(input.value);
                
                if (action === 'increase') {
                    input.value = value + 1;
                } else if (action === 'decrease' && value > 1) { // Minimum 1
                    input.value = value - 1;
                }
                
                this.updateSummary();
                this.validateForm();
            });
        });

        // Input validation
        document.querySelectorAll('.of-form-input[required]').forEach(input => {
            input.addEventListener('input', () => this.validateForm());
        });

        // Form submission
        document.getElementById('order-form').addEventListener('submit', (e) => {
            this.handleSubmit(e);
        });
    }

    calculateTotal() {
        let total = 0;
        this.config.products.forEach(product => {
            total += product.price * 1; // Default quantity is 1
        });
        return total;
    }

    updateSummary() {
        const form = document.getElementById('order-form');
        const formData = new FormData(form);
        const summaryContainer = document.getElementById('summary-items');
        const totalElement = document.getElementById('order-total');
        
        let total = 0;
        let summaryHTML = '';
        
        this.config.products.forEach(product => {
            const quantity = parseInt(formData.get(`products[${product.id}]`)) || 1; // Default to 1
            const productTotal = product.price * quantity;
            total += productTotal;
            
            summaryHTML += `
                <div class="of-summary-item">
                    <span>${product.title} × ${quantity}</span>
                    <span>₦${productTotal.toLocaleString()}</span>
                </div>
            `;
        });
        
        summaryContainer.innerHTML = summaryHTML;
        totalElement.textContent = `₦${total.toLocaleString()}`;
    }

    validateForm() {
        const form = document.getElementById('order-form');
        const submitBtn = form.querySelector('.of-submit-btn');
        let isValid = true;

        // Check required fields
        document.querySelectorAll('.of-form-input[required]').forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
            }
        });

        // Check phone number format
        const phoneInput = document.getElementById('phone');
        if (phoneInput && !/^[\d\s\+\-\(\)]{10,}$/.test(phoneInput.value.trim())) {
            isValid = false;
        }

        submitBtn.disabled = !isValid;
    }

    async handleSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('.of-submit-btn');
        const formData = new FormData(form);
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="of-spinner"></span> Processing...';
        
        try {
            const products = this.config.products.map(product => ({
                product_id: product.id,
                quantity: parseInt(formData.get(`products[${product.id}]`)) || 1,
                price: product.price
            }));
            
            const response = await fetch(`${this.options.apiBase || ''}/orders`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    form_id: this.formId,
                    customer: {
                        full_name: formData.get('full_name'),
                        email: formData.get('email'),
                        mobile: formData.get('phone'),
                        address: formData.get('address'),
                        state: formData.get('state'),
                    },
                    products: products,
                    redirect_url: this.redirectUrl
                })
            });
            
            const result = await response.json();
            
            if (response.ok) {
                if (this.redirectUrl) {
                    window.location.href = this.redirectUrl;
                } else {
                    this.showSuccess();
                }
            } else {
                throw new Error(result.message || 'Failed to place order');
            }
        } catch (error) {
            this.showMessage(error.message, 'error');
            console.log("ERR ", error);
            submitBtn.disabled = false;
            submitBtn.textContent = 'Place Order';
        }
    }

    showSuccess() {
        const container = document.getElementById(this.containerId);
        container.innerHTML = `
            <div class="of-success">
                <div class="of-success-icon">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <h2 class="of-success-title">Order Placed Successfully!</h2>
                <p class="of-success-message">Thank you for your order. We'll contact you shortly.</p>
            </div>
        `;
    }

    showMessage(message, type) {
        const messageEl = document.createElement('div');
        messageEl.className = `of-message of-message-${type}`;
        messageEl.textContent = message;
        
        const container = document.getElementById(this.containerId);
        container.prepend(messageEl);
        
        setTimeout(() => {
            messageEl.classList.add('of-message-fade');
            setTimeout(() => messageEl.remove(), 300);
        }, 5000);
    }
}

// Initialization
(function() {
    function getFormId() {
        const container = document.getElementById('order-form-container');
        if (container && container.dataset.formId) return container.dataset.formId;
        
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('form_id')) return urlParams.get('form_id');
        
        return null;
    }

    function getRedirectUrl() {
        const container = document.getElementById('order-form-container');
        if (container && container.dataset.redirectUrl) return container.dataset.redirectUrl;
        
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('redirect_url')) return urlParams.get('redirect_url');
        
        return null;
    }

    function initialize() {
        const formId = getFormId();
        if (formId) {
            const container = document.getElementById('order-form-container');
            const apiBase = container ? container.dataset.apiBase : null;
            const redirectUrl = getRedirectUrl();
            
            new OrderFormEmbed(formId, {
                apiBase: apiBase || window.orderFormApiBase || '',
                redirectUrl: redirectUrl
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
})();



// import '../../css/embed.css';

// class OrderFormEmbed {
//     constructor(formId, options = {}) {
//         this.formId = formId;
//         this.options = options;
//         this.containerId = options.containerId || 'order-form-container';
//         this.selectedProducts = {};
//         this.csrfToken = this.getCSRFToken();
//         this.init().catch(err => {
//             console.error('OrderFormEmbed initialization failed:', err);
//             this.showError('Failed to load order form. Please refresh the page.');
//         });
//     }

//     getCSRFToken() {
//         const metaTag = document.querySelector('meta[name="csrf-token"]');
//         return metaTag ? metaTag.getAttribute('content') : '';
//     }

//     async init() {
//         try {
//             this.showLoading();
//             const response = await fetch(`${this.options.apiBase || ''}/embeddable-forms/${this.formId}`);
//             if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
//             this.config = await response.json();
//             this.renderForm();
//         } catch (error) {
//             console.error('Error initializing form:', error);
//             this.showError('Failed to load order form. Please try again later.');
//             throw error;
//         }
//     }

//     showLoading() {
//         const container = document.getElementById(this.containerId);
//         if (container) {
//             container.innerHTML = `
//                 <div class="of-loading-container">
//                     <div class="of-loading-spinner"></div>
//                     <p class="of-loading-text">Loading order form...</p>
//                 </div>
//             `;
//         }
//     }

//     showError(message) {
//         const container = document.getElementById(this.containerId);
//         if (container) {
//             container.innerHTML = `
//                 <div class="of-error-message">
//                     <svg class="of-error-icon" viewBox="0 0 20 20" fill="currentColor">
//                         <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
//                     </svg>
//                     <p>${message}</p>
//                 </div>
//             `;
//         }
//     }

//     renderForm() {
//         const container = document.getElementById(this.containerId);
//         if (!container) return;

//         const primaryColor = this.config.primary_color || '#3b82f6';
//         const buttonTextColor = this.config.button_text_color || '#ffffff';

//         container.innerHTML = `
//             <div class="of-container" style="--primary-color: ${primaryColor}; --button-text: ${buttonTextColor}">
//                 <div class="of-header">
//                     <h2 class="of-title" style="color: ${primaryColor}">
//                         ${this.config.title || 'Order Form'}
//                     </h2>
//                 </div>
                
//                 <form id="order-form" class="of-order-form">
//                     <div class="of-products-section">
//                         <h3 class="of-section-title">Products</h3>
//                         <div class="of-products-list">
//                             ${this.config.products.map(product => `
//                                 <div class="of-product-item">
//                                     <div class="of-product-info">
//                                         <h4 class="of-product-name">${product.title}</h4>
//                                         <span class="of-product-price">₦${product.price.toLocaleString()}</span>
//                                     </div>
//                                     <div class="of-product-quantity">
//                                         <button type="button" class="of-quantity-btn" data-action="decrease" data-product-id="${product.id}">−</button>
//                                         <input type="number" name="products[${product.id}]" value="0" min="0" class="of-quantity-input">
//                                         <button type="button" class="of-quantity-btn" data-action="increase" data-product-id="${product.id}">+</button>
//                                     </div>
//                                 </div>
//                             `).join('')}
//                         </div>
//                     </div>
                    
//                     <div class="of-order-summary">
//                         <h3 class="of-section-title">Order Summary</h3>
//                         <div class="of-summary-items" id="summary-items"></div>
//                         <div class="of-summary-total">
//                             <span>Total:</span>
//                             <span id="order-total">₦0.00</span>
//                         </div>
//                     </div>
                    
//                     <div class="of-customer-info">
//                         <h3 class="of-section-title">Customer Information</h3>
//                         <div class="of-form-grid">
//                             <div class="of-form-group">
//                                 <label for="full_name" class="of-form-label">Full Name *</label>
//                                 <input type="text" id="full_name" name="full_name" required class="of-form-input">
//                             </div>
                            
//                             <div class="of-form-group">
//                                 <label for="email" class="of-form-label">Email *</label>
//                                 <input type="email" id="email" name="email" class="of-form-input">
//                             </div>
                            
//                             <div class="of-form-group">
//                                 <label for="mobile" class="of-form-label">Mobile Number *</label>
//                                 <input type="tel" id="mobile" name="mobile" required class="of-form-input">
//                             </div>

//                             <div class="of-form-group">
//                                 <label for="state" class="of-form-label">state Number *</label>
//                                 <input type="text" id="state" name="state" required class="of-form-input">
//                             </div>
                            
//                             <div class="of-form-group">
//                                 <label for="address" class="of-form-label">Delivery Address *</label>
//                                 <textarea id="address" name="address" rows="2" required class="of-form-input"></textarea>
//                             </div>
//                         </div>
//                     </div>
                    
//                     <div class="of-form-actions">
//                         <button type="submit" class="of-submit-btn">
//                             Place Order
//                         </button>
//                     </div>
//                 </form>
//             </div>
//         `;

//         this.bindEvents();
//         this.updateSummary();
//     }

//     bindEvents() {
//         // Quantity buttons
//         document.querySelectorAll('.of-quantity-btn').forEach(btn => {
//             btn.addEventListener('click', (e) => {
//                 const action = e.currentTarget.dataset.action;
//                 const productId = e.currentTarget.dataset.productId;
//                 const input = e.currentTarget.parentElement.querySelector('.of-quantity-input');
//                 let value = parseInt(input.value);
                
//                 if (action === 'increase') {
//                     input.value = value + 1;
//                 } else if (action === 'decrease' && value > 0) {
//                     input.value = value - 1;
//                 }
                
//                 this.updateSummary();
//             });
//         });

//         // Form submission
//         document.getElementById('order-form').addEventListener('submit', (e) => {
//             this.handleSubmit(e);
//         });
//     }

//     updateSummary() {
//         const form = document.getElementById('order-form');
//         const formData = new FormData(form);
//         const summaryContainer = document.getElementById('summary-items');
//         const totalElement = document.getElementById('order-total');
        
//         let total = 0;
//         let summaryHTML = '';
        
//         this.config.products.forEach(product => {
//             const quantity = parseInt(formData.get(`products[${product.id}]`)) || 0;
//             if (quantity > 0) {
//                 const productTotal = product.price * quantity;
//                 total += productTotal;
                
//                 summaryHTML += `
//                     <div class="of-summary-item">
//                         <span>${product.title} × ${quantity}</span>
//                         <span>₦${productTotal.toLocaleString()}</span>
//                     </div>
//                 `;
//             }
//         });
        
//         summaryContainer.innerHTML = summaryHTML || '<div class="of-summary-empty">No products selected</div>';
//         totalElement.textContent = `₦${total.toLocaleString()}`;
//     }

//     async handleSubmit(e) {
//         e.preventDefault();
//         const form = e.target;
//         const submitBtn = form.querySelector('.of-submit-btn');
//         const formData = new FormData(form);
        
//         submitBtn.disabled = true;
//         submitBtn.innerHTML = '<span class="of-spinner"></span> Processing...';
        
//         try {
//             const products = this.config.products
//                 .map(product => {
//                     const quantity = parseInt(formData.get(`products[${product.id}]`)) || 0;
//                     return quantity > 0 ? {
//                         product_id: product.id,
//                         quantity: quantity,
//                         price: product.price
//                     } : null;
//                 })
//                 .filter(Boolean);
            
//             if (products.length === 0) {
//                 throw new Error('Please select at least one product');
//             }
            
//             const response = await fetch(`${this.options.apiBase || ''}/orders`, {
//                 method: 'POST',
//                 headers: {
//                     'Content-Type': 'application/json',
//                     'X-CSRF-TOKEN': this.csrfToken
//                 },
//                 body: JSON.stringify({
//                     form_id: this.formId,
//                     customer: {
//                         full_name: formData.get('full_name'),
//                         email: formData.get('email'),
//                         mobile: formData.get('mobile'),
//                         address: formData.get('address')
//                     },
//                     products: products
//                 })
//             });
            
//             const result = await response.json();
            
//             if (response.ok) {
//                 this.showSuccess();
//             } else {
//                 throw new Error(result.message || 'Failed to place order');
//             }
//         } catch (error) {
//             this.showMessage(error.message, 'error');
//             submitBtn.disabled = false;
//             submitBtn.textContent = 'Place Order';
//         }
//     }

//     showSuccess() {
//         const container = document.getElementById(this.containerId);
//         container.innerHTML = `
//             <div class="of-success">
//                 <div class="of-success-icon">
//                     <svg viewBox="0 0 20 20" fill="currentColor">
//                         <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
//                     </svg>
//                 </div>
//                 <h2 class="of-success-title">Order Placed Successfully!</h2>
//                 <p class="of-success-message">Thank you for your order. We'll contact you shortly.</p>
//             </div>
//         `;
//     }

//     showMessage(message, type) {
//         const messageEl = document.createElement('div');
//         messageEl.className = `of-message of-message-${type}`;
//         messageEl.textContent = message;
        
//         const container = document.getElementById(this.containerId);
//         container.prepend(messageEl);
        
//         setTimeout(() => {
//             messageEl.classList.add('of-message-fade');
//             setTimeout(() => messageEl.remove(), 300);
//         }, 5000);
//     }
// }

// // Initialization
// (function() {
//     function getFormId() {
//         const container = document.getElementById('order-form-container');
//         if (container && container.dataset.formId) return container.dataset.formId;
        
//         const urlParams = new URLSearchParams(window.location.search);
//         if (urlParams.has('form_id')) return urlParams.get('form_id');
        
//         return null;
//     }

//     function initialize() {
//         const formId = getFormId();
//         if (formId) {
//             const container = document.getElementById('order-form-container');
//             const apiBase = container ? container.dataset.apiBase : null;
            
//             new OrderFormEmbed(formId, {
//                 apiBase: apiBase || window.orderFormApiBase || ''
//             });
//         }
//     }

//     if (document.readyState === 'loading') {
//         document.addEventListener('DOMContentLoaded', initialize);
//     } else {
//         initialize();
//     }
// })();