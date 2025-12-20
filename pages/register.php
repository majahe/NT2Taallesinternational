<?php 
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/csrf.php';

$recaptchaSiteKey = RECAPTCHA_SITE_KEY;
include '../includes/header.php';
?>
<?php if (!empty($recaptchaSiteKey)): ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>
<section class="form-section">
  <h2>Register for a Course</h2>
  
  <?php if (isset($_SESSION['registration_error'])): ?>
    <div class="alert alert-error" style="background: #fed7d7; color: #742a2a; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 4px solid #e53e3e;">
      <strong>Error:</strong> <?php echo htmlspecialchars($_SESSION['registration_error']); unset($_SESSION['registration_error']); ?>
    </div>
  <?php endif; ?>
  
  <form action="../handlers/submit_registration.php" method="POST" class="form-grid">
    <?php echo CSRF::getTokenField(); ?>

    <div class="form-row">
      <div class="form-group">
        <label>Full Name *</label>
        <input type="text" name="name" required>
      </div>
      <div class="form-group">
        <label>Email Address *</label>
        <input type="email" name="email" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Course *</label>
        <select name="course" required>
          <option value="">Select a course</option>
          <option value="Beginner Dutch">Beginner Dutch</option>
          <option value="Intermediate Dutch">Intermediate Dutch</option>
          <option value="Advanced Dutch">Advanced Dutch</option>
          <option value="Business Dutch">Business Dutch</option>
          <option value="Conversation Practice">Conversation Practice</option>
        </select>
      </div>
      <div class="form-group">
        <label>Spoken Language *</label>
        <select name="spoken_language" required>
          <option value="">Select your native language</option>
          <option value="Russian">Russian</option>
          <option value="English">English</option>
          <option value="Other">Other</option>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Preferred Time *</label>
        <select name="preferred_time" required>
          <option value="">Select a time of day</option>
          <option value="Morning (9:00 - 12:00)">Morning (9:00 - 12:00)</option>
          <option value="Afternoon (12:00 - 17:00)">Afternoon (12:00 - 17:00)</option>
          <option value="Evening (17:00 - 21:00)">Evening (17:00 - 21:00)</option>
        </select>
      </div>
      <div class="form-group">
        <label>&nbsp;</label>
        <div style="height: 48px;"></div>
      </div>
    </div>

    <div class="form-group full">
      <label>Additional Information</label>
      <textarea name="message" rows="4" placeholder="Tell us about your learning goals, experience with Dutch, or any questions..."></textarea>
    </div>

    <div class="form-group full">
      <?php if (!empty($recaptchaSiteKey)): ?>
        <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($recaptchaSiteKey); ?>"></div>
      <?php else: ?>
        <p style="color: #e53e3e; font-size: 0.875rem; padding: 1rem; background: #fed7d7; border-radius: 4px;">
          ⚠️ reCAPTCHA is not configured. Please set RECAPTCHA_SITE_KEY in your config/.env file.
          <?php if (defined('RECAPTCHA_SITE_KEY')): ?>
            <br><small>Debug: RECAPTCHA_SITE_KEY is defined but empty. Value: "<?php echo htmlspecialchars(RECAPTCHA_SITE_KEY); ?>"</small>
          <?php else: ?>
            <br><small>Debug: RECAPTCHA_SITE_KEY constant is not defined.</small>
          <?php endif; ?>
        </p>
      <?php endif; ?>
    </div>

    <div class="form-group full">
      <button type="submit" class="btn-submit">Submit Registration</button>
    </div>
  </form>
</section>
<?php include '../includes/footer.php'; ?>
