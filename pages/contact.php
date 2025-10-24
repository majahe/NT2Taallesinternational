<?php
  $isInPages = strpos($_SERVER['SCRIPT_NAME'], '/pages/') !== false || strpos($_SERVER['PHP_SELF'], '/pages/') !== false;
  $basePath = $isInPages ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us - NT2 Taallessen International</title>
  <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/style.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/contact.css?v=<?php echo time(); ?>">
</head>
<body>
  <?php include $basePath . 'includes/header.php'; ?>
  
  <!-- Hero Section -->
  <section class="contact-hero">
    <div class="container">
      <div class="hero-content">
        <h1>Get in Touch</h1>
        <p>We're here to help you on your language learning journey. Contact us for any questions about our courses or to get started.</p>
      </div>
    </div>
  </section>

  <!-- Contact Information Cards -->
  <section class="contact-info-section">
    <div class="container">
      <h2 class="section-title">Contact Information.</h2>
      <div class="contact-cards">
        <div class="contact-card">
          <div class="contact-icon">📧</div>
          <h3>Email Us</h3>
          <p>info@nt2taallesinternational.com</p>
          
        </div>
        <div class="contact-card">
          <div class="contact-icon">📞</div>
          <h3>Call Us</h3>
          <p>+31 (0) </p>
          <p>Mon-Fri: 9:00-17:00</p>
        </div>
        
      </div>
    </div>
  </section>

  <!-- Contact Form Section -->
  <section class="contact-form-section">
    <div class="container">
      <div class="form-container">
        <div class="form-header">
          <h2>Send us a Message</h2>
          <p>Fill out the form below and we'll get back to you within 24 hours.</p>
        </div>
        
        <?php if (isset($_SESSION['contact_success'])): ?>
          <div class="alert alert-success">
            <strong>Success!</strong> <?php echo htmlspecialchars($_SESSION['contact_success']); ?>
          </div>
          <?php unset($_SESSION['contact_success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['contact_errors'])): ?>
          <div class="alert alert-error">
            <strong>Please fix the following errors:</strong>
            <ul>
              <?php foreach ($_SESSION['contact_errors'] as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
          <?php unset($_SESSION['contact_errors']); ?>
        <?php endif; ?>
        
        <?php 
        // Don't clean up form data here - let it persist for form field values
        ?>
        
        <form class="contact-form" action="../handlers/submit_contact.php" method="POST">
          <div class="form-row">
            <div class="form-group">
              <label for="firstName">First Name *</label>
              <input type="text" id="firstName" name="firstName" value="<?php echo isset($_SESSION['contact_form_data']['firstName']) ? htmlspecialchars($_SESSION['contact_form_data']['firstName']) : ''; ?>" required>
            </div>
            <div class="form-group">
              <label for="lastName">Last Name *</label>
              <input type="text" id="lastName" name="lastName" value="<?php echo isset($_SESSION['contact_form_data']['lastName']) ? htmlspecialchars($_SESSION['contact_form_data']['lastName']) : ''; ?>" required>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="email">Email Address *</label>
              <input type="email" id="email" name="email" value="<?php echo isset($_SESSION['contact_form_data']['email']) ? htmlspecialchars($_SESSION['contact_form_data']['email']) : ''; ?>" required>
            </div>
            <div class="form-group">
              <label for="phone">Phone Number</label>
              <input type="tel" id="phone" name="phone" value="<?php echo isset($_SESSION['contact_form_data']['phone']) ? htmlspecialchars($_SESSION['contact_form_data']['phone']) : ''; ?>">
            </div>
          </div>
          
          <div class="form-group">
            <label for="subject">Subject *</label>
            <select id="subject" name="subject" required>
              <option value="">Select a subject</option>
              <option value="general" <?php echo (isset($_SESSION['contact_form_data']['subject']) && $_SESSION['contact_form_data']['subject'] === 'general') ? 'selected' : ''; ?>>General Inquiry</option>
              <option value="course-info" <?php echo (isset($_SESSION['contact_form_data']['subject']) && $_SESSION['contact_form_data']['subject'] === 'course-info') ? 'selected' : ''; ?>>Course Information</option>
              <option value="registration" <?php echo (isset($_SESSION['contact_form_data']['subject']) && $_SESSION['contact_form_data']['subject'] === 'registration') ? 'selected' : ''; ?>>Registration Help</option>
              <option value="technical" <?php echo (isset($_SESSION['contact_form_data']['subject']) && $_SESSION['contact_form_data']['subject'] === 'technical') ? 'selected' : ''; ?>>Technical Support</option>
              <option value="feedback" <?php echo (isset($_SESSION['contact_form_data']['subject']) && $_SESSION['contact_form_data']['subject'] === 'feedback') ? 'selected' : ''; ?>>Feedback</option>
              <option value="other" <?php echo (isset($_SESSION['contact_form_data']['subject']) && $_SESSION['contact_form_data']['subject'] === 'other') ? 'selected' : ''; ?>>Other</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="courseInterest">Course Interest</label>
            <select id="courseInterest" name="courseInterest">
              <option value="">Select a course (optional)</option>
              <option value="russian-dutch" <?php echo (isset($_SESSION['contact_form_data']['courseInterest']) && $_SESSION['contact_form_data']['courseInterest'] === 'russian-dutch') ? 'selected' : ''; ?>>Russian to Dutch</option>
              <option value="english-dutch" <?php echo (isset($_SESSION['contact_form_data']['courseInterest']) && $_SESSION['contact_form_data']['courseInterest'] === 'english-dutch') ? 'selected' : ''; ?>>English to Dutch</option>
              <option value="both" <?php echo (isset($_SESSION['contact_form_data']['courseInterest']) && $_SESSION['contact_form_data']['courseInterest'] === 'both') ? 'selected' : ''; ?>>Both Courses</option>
              <option value="not-sure" <?php echo (isset($_SESSION['contact_form_data']['courseInterest']) && $_SESSION['contact_form_data']['courseInterest'] === 'not-sure') ? 'selected' : ''; ?>>Not Sure Yet</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="message">Message *</label>
            <textarea id="message" name="message" rows="6" placeholder="Tell us how we can help you..." required><?php echo isset($_SESSION['contact_form_data']['message']) ? htmlspecialchars($_SESSION['contact_form_data']['message']) : ''; ?></textarea>
          </div>
          
          <div class="form-group">
            <label class="checkbox-label">
              <input type="checkbox" name="newsletter" value="1">
              <span class="checkmark"></span>
              I would like to receive updates about new courses and language learning tips
            </label>
          </div>
          
          <div class="form-group">
            <label class="checkbox-label">
              <input type="checkbox" name="privacy" value="1">
              <span class="checkmark"></span>
              I agree to the <a href="#" class="privacy-link">Privacy Policy</a> and <a href="#" class="privacy-link">Terms of Service</a> *
            </label>
          </div>
          
          <button type="submit" class="btn-submit">
            <span class="btn-text">Send Message</span>
            <span class="btn-icon">→</span>
          </button>
        </form>
      </div>
    </div>
  </section>


  <?php include $basePath . 'includes/footer.php'; ?>

  <script>
    // Form validation and enhancement
    document.addEventListener('DOMContentLoaded', function() {
      console.log('Contact form validation script loaded');
      
      const form = document.querySelector('.contact-form');
      const submitBtn = document.querySelector('.btn-submit');
      
      if (!form) {
        console.error('Contact form not found!');
        return;
      }
      
      console.log('Form found:', form);
      
      // Create error message container
      const errorContainer = document.createElement('div');
      errorContainer.className = 'validation-error';
      errorContainer.style.cssText = `
        display: none;
        background: #fed7d7;
        color: #742a2a;
        padding: 0.8rem 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        border-left: 4px solid #e53e3e;
        font-size: 0.9rem;
        line-height: 1.4;
      `;
      
      // Insert error container after form header
      const formHeader = document.querySelector('.form-header');
      if (formHeader) {
        formHeader.parentNode.insertBefore(errorContainer, formHeader.nextSibling);
        console.log('Error container inserted');
      } else {
        console.error('Form header not found');
      }
      
      form.addEventListener('submit', function(e) {
        console.log('Form submission attempted');
        
        // Clear previous errors
        errorContainer.style.display = 'none';
        errorContainer.innerHTML = '';
        
        // Basic validation
        const requiredFields = form.querySelectorAll('[required]');
        console.log('Required fields found:', requiredFields.length);
        
        let isValid = true;
        let errorMessages = [];
        
        // Validate required fields
        requiredFields.forEach(field => {
          console.log('Validating field:', field.name, 'type:', field.type, 'checked:', field.checked);
          
          if (field.type === 'checkbox') {
            // Special handling for checkbox
            if (!field.checked) {
              isValid = false;
            }
          } else {
            // Regular input validation
            if (!field.value.trim()) {
              field.style.borderColor = '#e11d48';
              isValid = false;
            } else {
              field.style.borderColor = '#1a365d';
            }
          }
        });
        
        // Special validation for privacy checkbox (not marked as required)
        const privacyCheckbox = form.querySelector('input[name="privacy"]');
        console.log('Checking privacy checkbox:', privacyCheckbox, 'checked:', privacyCheckbox ? privacyCheckbox.checked : 'not found');
        
        if (privacyCheckbox && !privacyCheckbox.checked) {
          console.log('Privacy checkbox not checked - adding error');
          errorMessages.push('⚠️ You must agree to the Privacy Policy and Terms of Service before you can send the message.');
          isValid = false;
          
          // Add visual feedback to the checkbox label
          const checkboxLabel = privacyCheckbox.closest('.checkbox-label');
          console.log('Checkbox label found:', checkboxLabel);
          if (checkboxLabel) {
            checkboxLabel.style.color = '#e11d48';
            checkboxLabel.style.fontWeight = '600';
            checkboxLabel.style.border = '2px solid #e11d48';
            checkboxLabel.style.borderRadius = '4px';
            checkboxLabel.style.padding = '8px';
            checkboxLabel.style.backgroundColor = '#fef2f2';
            console.log('Applied visual feedback to checkbox label');
          }
        } else if (privacyCheckbox && privacyCheckbox.checked) {
          // Reset visual feedback if checkbox is checked
          const checkboxLabel = privacyCheckbox.closest('.checkbox-label');
          if (checkboxLabel) {
            checkboxLabel.style.color = '#4a5568';
            checkboxLabel.style.fontWeight = 'normal';
            checkboxLabel.style.border = 'none';
            checkboxLabel.style.padding = '0';
            checkboxLabel.style.backgroundColor = 'transparent';
          }
        }
        
        console.log('Form validation result - isValid:', isValid, 'errorMessages:', errorMessages);
        
        if (!isValid) {
          console.log('Form is invalid - preventing submission');
          e.preventDefault();
          
          // Show error messages
          if (errorMessages.length > 0) {
            console.log('Showing error messages:', errorMessages);
            errorContainer.innerHTML = '<strong>⚠️ Please fix the following:</strong><ul style="margin: 0.5rem 0 0 1.2rem;"><li>' + errorMessages.join('</li><li>') + '</li></ul>';
            errorContainer.style.display = 'block';
            console.log('Error container display set to block');
          }
          
          // Scroll to error message
          errorContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
          console.log('Form is valid - allowing submission');
          // Show loading state
          if (submitBtn) {
            submitBtn.innerHTML = '<span class="btn-text">Sending...</span><span class="btn-icon">⏳</span>';
            submitBtn.disabled = true;
          }
          
          // Allow form to submit normally to the server
          // The form will submit to submit_contact.php
        }
      });
      
      // Real-time validation
      const inputs = form.querySelectorAll('input, select, textarea');
      inputs.forEach(input => {
        input.addEventListener('blur', function() {
          if (this.hasAttribute('required') && !this.value.trim()) {
            this.style.borderColor = '#e11d48';
          } else {
            this.style.borderColor = '#1a365d';
          }
        });
        
        input.addEventListener('input', function() {
          if (this.style.borderColor === 'rgb(225, 29, 72)') {
            this.style.borderColor = '#1a365d';
          }
        });
      });
      
      // Privacy checkbox specific validation
      const privacyCheckbox = form.querySelector('input[name="privacy"]');
      console.log('Privacy checkbox found:', privacyCheckbox);
      
      if (privacyCheckbox) {
        privacyCheckbox.addEventListener('change', function() {
          console.log('Privacy checkbox changed, checked:', this.checked);
          if (this.checked) {
            console.log('Privacy checkbox checked - hiding error and resetting styles');
            // Hide error message if checkbox is checked
            errorContainer.style.display = 'none';
            // Reset visual feedback
            const checkboxLabel = this.closest('.checkbox-label');
            console.log('Checkbox label for reset:', checkboxLabel);
            if (checkboxLabel) {
              checkboxLabel.style.color = '#4a5568';
              checkboxLabel.style.fontWeight = 'normal';
              checkboxLabel.style.border = 'none';
              checkboxLabel.style.padding = '0';
              checkboxLabel.style.backgroundColor = 'transparent';
              console.log('Reset visual feedback applied');
            }
          }
        });
      } else {
        console.error('Privacy checkbox not found!');
      }
    });
  </script>
</body>
</html>
