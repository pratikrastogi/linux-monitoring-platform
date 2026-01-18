<!-- Query/Contact Popup Widget -->
<style>
.query-popup-trigger {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.5);
    cursor: pointer;
    z-index: 997;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    transition: all 0.3s;
    animation: pulse-ring 2s infinite;
}

.query-popup-trigger:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 30px rgba(102, 126, 234, 0.7);
}

@keyframes pulse-ring {
    0% {
        box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.7);
    }
    70% {
        box-shadow: 0 0 0 20px rgba(102, 126, 234, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(102, 126, 234, 0);
    }
}

.query-popup-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    backdrop-filter: blur(5px);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.query-popup-container.active {
    display: flex;
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.query-popup {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    max-width: 500px;
    width: 100%;
    box-shadow: 0 10px 50px rgba(0,0,0,0.3);
    animation: slideUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.query-popup-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 35px;
    height: 35px;
    background: #f0f0f0;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.2rem;
    color: #333;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.query-popup-close:hover {
    background: #e74c3c;
    color: white;
    transform: rotate(90deg);
}

.query-popup h3 {
    color: #667eea;
    font-size: 1.8rem;
    margin-bottom: 0.5rem;
    font-family: 'Orbitron', sans-serif;
}

.query-popup p {
    color: #666;
    margin-bottom: 1.5rem;
}

.query-form-group {
    margin-bottom: 1.5rem;
}

.query-form-group label {
    display: block;
    color: #333;
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.query-form-group input,
.query-form-group textarea {
    width: 100%;
    padding: 0.9rem;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 1rem;
    font-family: inherit;
    transition: all 0.3s;
}

.query-form-group input:focus,
.query-form-group textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.query-form-group textarea {
    resize: vertical;
    min-height: 120px;
}

.query-submit-btn {
    width: 100%;
    padding: 1rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.query-submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
}

.query-submit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.query-success-message,
.query-error-message {
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1rem;
    display: none;
}

.query-success-message {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.query-error-message {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

@media (max-width: 768px) {
    .query-popup {
        padding: 2rem 1.5rem;
    }
    
    .query-popup-trigger {
        width: 55px;
        height: 55px;
        bottom: 20px;
        right: 20px;
    }
}
</style>

<!-- Font Awesome for Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Query Popup Trigger Button -->
<div class="query-popup-trigger" onclick="openQueryPopup()" title="Send us a query">
    <i class="fas fa-question-circle"></i>
</div>

<!-- Query Popup Container -->
<div class="query-popup-container" id="queryPopupContainer" onclick="closeQueryPopupOnOverlay(event)">
    <div class="query-popup">
        <button class="query-popup-close" onclick="closeQueryPopup()">
            <i class="fas fa-times"></i>
        </button>
        
        <h3>💬 Send us a Query</h3>
        <p>Have a question or need help? Fill out the form below and we'll get back to you soon!</p>
        
        <div class="query-success-message" id="querySuccessMessage">
            ✓ Your query has been submitted successfully! We'll contact you soon.
        </div>
        
        <div class="query-error-message" id="queryErrorMessage">
            ✗ <span id="queryErrorText">Something went wrong. Please try again.</span>
        </div>
        
        <form id="queryForm" onsubmit="submitQuery(event)">
            <div class="query-form-group">
                <label for="queryName">Your Name *</label>
                <input type="text" id="queryName" name="name" required placeholder="Enter your full name">
            </div>
            
            <div class="query-form-group">
                <label for="queryMobile">Mobile Number *</label>
                <input type="tel" id="queryMobile" name="mobile" required pattern="[0-9]{10}" placeholder="10-digit mobile number">
            </div>
            
            <div class="query-form-group">
                <label for="queryEmail">Email (Optional)</label>
                <input type="email" id="queryEmail" name="email" placeholder="your.email@example.com">
            </div>
            
            <div class="query-form-group">
                <label for="queryRequirement">Your Requirement *</label>
                <textarea id="queryRequirement" name="requirement" required placeholder="Describe your query or requirement in detail..."></textarea>
            </div>
            
            <button type="submit" class="query-submit-btn" id="querySubmitBtn">
                <i class="fas fa-paper-plane"></i> Submit Query
            </button>
        </form>
    </div>
</div>

<script>
function openQueryPopup() {
    document.getElementById('queryPopupContainer').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeQueryPopup() {
    document.getElementById('queryPopupContainer').classList.remove('active');
    document.body.style.overflow = '';
}

function closeQueryPopupOnOverlay(event) {
    if (event.target.id === 'queryPopupContainer') {
        closeQueryPopup();
    }
}

// Close on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeQueryPopup();
    }
});

function submitQuery(event) {
    event.preventDefault();
    
    const form = event.target;
    const submitBtn = document.getElementById('querySubmitBtn');
    const successMsg = document.getElementById('querySuccessMessage');
    const errorMsg = document.getElementById('queryErrorMessage');
    
    // Hide messages
    successMsg.style.display = 'none';
    errorMsg.style.display = 'none';
    
    // Disable button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    
    // Prepare form data
    const formData = new FormData(form);
    
    // Submit via AJAX
    fetch('api/submit_query.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            successMsg.style.display = 'block';
            form.reset();
            
            // Close popup after 3 seconds
            setTimeout(() => {
                closeQueryPopup();
                successMsg.style.display = 'none';
            }, 3000);
        } else {
            errorMsg.style.display = 'block';
            document.getElementById('queryErrorText').textContent = data.error || 'Something went wrong. Please try again.';
        }
    })
    .catch(error => {
        errorMsg.style.display = 'block';
        document.getElementById('queryErrorText').textContent = 'Network error. Please check your connection.';
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Query';
    });
}
</script>
