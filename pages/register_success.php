<?php 
session_start();

// Check if user came from successful registration
if (!isset($_SESSION['registration_success'])) {
    header('Location: /pages/register.php');
    exit;
}

// Clear the session flag
unset($_SESSION['registration_success']);

include '../includes/header.php'; 
?>

<section class="form-section">
  <div class="success-container">
    <div class="success-icon">
      <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="12" cy="12" r="10" stroke="#4CAF50" stroke-width="2"/>
        <path d="M9 12l2 2 4-4" stroke="#4CAF50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>
    
    <h2>Registration Successful!</h2>
    
    <div class="success-message">
      <p>Thank you for registering for a Dutch course at <strong>NT2 Taalles International</strong>!</p>
      
      <div class="success-details">
        <h3>What happens next?</h3>
        <ul>
          <li>✅ You will receive a confirmation email shortly</li>
          <li>📧 We will review your registration and contact you within 24 hours</li>
          <li>📅 We'll discuss your learning goals and schedule preferences</li>
          <li>🎯 Your personalized Dutch learning journey will begin!</li>
        </ul>
      </div>
      
      <div class="success-actions">
        <a href="/" class="btn-primary">Return to Homepage</a>
        <a href="/pages/about.php" class="btn-secondary">Learn More About Us</a>
      </div>
    </div>
  </div>
</section>

<style>
.success-container {
  text-align: center;
  max-width: 600px;
  margin: 0 auto;
  padding: 2rem;
}

.success-icon {
  margin-bottom: 1.5rem;
}

.success-icon svg {
  animation: checkmark 0.6s ease-in-out;
}

@keyframes checkmark {
  0% {
    transform: scale(0);
    opacity: 0;
  }
  50% {
    transform: scale(1.1);
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.success-message h2 {
  color: #2c3e50;
  margin-bottom: 1rem;
  font-size: 2rem;
}

.success-message p {
  font-size: 1.1rem;
  color: #555;
  margin-bottom: 2rem;
  line-height: 1.6;
}

.success-details {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 1.5rem;
  margin: 2rem 0;
  text-align: left;
}

.success-details h3 {
  color: #2c3e50;
  margin-bottom: 1rem;
  font-size: 1.3rem;
}

.success-details ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.success-details li {
  padding: 0.5rem 0;
  color: #555;
  font-size: 1rem;
  display: flex;
  align-items: center;
}

.success-details li:before {
  content: '';
  margin-right: 0.5rem;
}

.success-actions {
  margin-top: 2rem;
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.btn-primary, .btn-secondary {
  display: inline-block;
  padding: 12px 24px;
  text-decoration: none;
  border-radius: 6px;
  font-weight: 500;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.btn-primary {
  background: #007bff;
  color: white;
}

.btn-primary:hover {
  background: #0056b3;
  transform: translateY(-2px);
}

.btn-secondary {
  background: transparent;
  color: #007bff;
  border-color: #007bff;
}

.btn-secondary:hover {
  background: #007bff;
  color: white;
  transform: translateY(-2px);
}

@media (max-width: 768px) {
  .success-actions {
    flex-direction: column;
    align-items: center;
  }
  
  .btn-primary, .btn-secondary {
    width: 100%;
    max-width: 300px;
  }
}
</style>

<?php include '../includes/footer.php'; ?>
