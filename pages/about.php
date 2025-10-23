<?php
  $isInPages = strpos($_SERVER['SCRIPT_NAME'], '/pages/') !== false || strpos($_SERVER['PHP_SELF'], '/pages/') !== false;
  $basePath = $isInPages ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us - NT2 Taallessen International</title>
  <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/style.css">
  <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/about.css">
</head>
<body>
  <?php include $basePath . 'includes/header.php'; ?>
  
  <!-- Hero Section -->
  <section class="about-hero">
    <div class="container">
      <div class="hero-content">
        <h1>About NT2 Taallessen International</h1>
        <p>Empowering language learners to achieve their dreams through expert instruction, innovative teaching methods, and personalized support.</p>
      </div>
    </div>
  </section>

  <!-- Mission, Vision, Values Section -->
  <section class="mvv-section">
    <div class="container">
      <div class="mvv-grid">
        <div class="mvv-card">
          <div class="mvv-icon">🎯</div>
          <h2>Our Mission</h2>
          <p>To provide high-quality Dutch language education that empowers international students and professionals to integrate successfully into Dutch society while maintaining their cultural identity.</p>
        </div>
        <div class="mvv-card">
          <div class="mvv-icon">👁️</div>
          <h2>Our Vision</h2>
          <p>To be the leading provider of NT2 (Dutch as a Second Language) education, creating a bridge between cultures and fostering understanding in our globalized world.</p>
        </div>
        <div class="mvv-card">
          <div class="mvv-icon">💎</div>
          <h2>Our Values</h2>
          <p>Excellence in education, cultural sensitivity, personalized learning, and creating an inclusive environment where every student can thrive and succeed.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Our Story Section -->
  <section class="story-section">
    <div class="container">
      <div class="story-content">
        <div class="story-text">
          <h2>Our Story</h2>
          <p>Founded in 2015, NT2 Taallessen International began as a small initiative to help international students and professionals learn Dutch effectively. Our founder, recognizing the challenges faced by newcomers to the Netherlands, created a unique approach that combines traditional language learning with modern teaching methods.</p>
          
          <p>Over the years, we have grown from a single classroom to a comprehensive language learning center, serving hundreds of students from diverse backgrounds. Our success lies in understanding that learning a new language is not just about grammar and vocabulary—it's about building confidence, understanding culture, and creating connections.</p>
          
          <p>Today, we continue to innovate and adapt our teaching methods to meet the evolving needs of our students, ensuring that each learner receives the personalized attention and support they need to succeed in their Dutch language journey.</p>
        </div>
      </div>
    </div>
  </section>


  <!-- Team Section -->
  <section class="team-section">
    <div class="container">
      <h2 class="section-title">Meet Our Team</h2>
      <div class="team-grid">
        <div class="team-card">
          <div class="team-image">
            <div class="image-placeholder">
              <div class="initials">AS</div>
            </div>
          </div>
          <div class="team-info">
            <h3>Assel Smagulova</h3>
            <p class="team-role">Founder & Lead Instructor</p>
            <p class="team-bio">With over 10 years of experience in language education, Assel specializes in helping international students achieve fluency in Dutch. She holds a Master's in Applied Linguistics and is passionate about cultural integration.</p>
            <div class="team-skills">
              <span class="skill-tag">Dutch Language Expert</span>
              <span class="skill-tag">Applied Linguistics</span>
              <span class="skill-tag">Cultural Integration</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Teaching Methods Section -->
  <section class="methods-section">
    <div class="container">
      <h2 class="section-title">Our Teaching Approach</h2>
      <div class="methods-grid">
        <div class="method-card">
          <div class="method-icon">🎯</div>
          <h3>Personalized Learning</h3>
          <p>Every student has unique needs and learning styles. We tailor our approach to match your goals, pace, and preferences.</p>
        </div>
        <div class="method-card">
          <div class="method-icon">💬</div>
          <h3>Interactive Communication</h3>
          <p>We focus on practical communication skills through role-playing, discussions, and real-world scenarios.</p>
        </div>
        <div class="method-card">
          <div class="method-icon">🌍</div>
          <h3>Cultural Integration</h3>
          <p>Language learning goes hand-in-hand with understanding Dutch culture, customs, and social norms.</p>
        </div>
        <div class="method-card">
          <div class="method-icon">📚</div>
          <h3>Modern Resources</h3>
          <p>We use the latest technology and teaching materials to make learning engaging and effective.</p>
        </div>
        <div class="method-card">
          <div class="method-icon">🤝</div>
          <h3>Supportive Environment</h3>
          <p>We create a safe, encouraging space where students feel comfortable making mistakes and learning from them.</p>
        </div>
        <div class="method-card">
          <div class="method-icon">📈</div>
          <h3>Progress Tracking</h3>
          <p>Regular assessments and feedback help you see your progress and stay motivated throughout your journey.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Testimonials Section -->
  <section class="testimonials-section">
    <div class="container">
      <h2 class="section-title">What Our Students Say</h2>
      <div class="testimonials-grid">
        <div class="testimonial-card">
          <div class="testimonial-content">
            <p>"NT2 Taallessen International transformed my Dutch learning experience. The personalized approach and cultural integration made all the difference in my journey to fluency."</p>
          </div>
          <div class="testimonial-author">
            <div class="author-info">
              <h4>Sarah Johnson</h4>
              <p>Software Engineer from USA</p>
            </div>
            <div class="testimonial-rating">⭐⭐⭐⭐⭐</div>
          </div>
        </div>
        
        <div class="testimonial-card">
          <div class="testimonial-content">
            <p>"The teachers here don't just teach language—they help you understand Dutch culture and society. I felt confident speaking Dutch in professional settings after just 6 months."</p>
          </div>
          <div class="testimonial-author">
            <div class="author-info">
              <h4>Ahmed Hassan</h4>
              <p>Business Analyst from Egypt</p>
            </div>
            <div class="testimonial-rating">⭐⭐⭐⭐⭐</div>
          </div>
        </div>
        
        <div class="testimonial-card">
          <div class="testimonial-content">
            <p>"I was nervous about learning Dutch as an adult, but the supportive environment and excellent teaching methods made it enjoyable and effective. Highly recommended!"</p>
          </div>
          <div class="testimonial-author">
            <div class="author-info">
              <h4>Elena Rodriguez</h4>
              <p>Marketing Manager from Spain</p>
            </div>
            <div class="testimonial-rating">⭐⭐⭐⭐⭐</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Call to Action Section -->
  <section class="cta-section">
    <div class="container">
      <div class="cta-content">
        <h2>Ready to Start Your Dutch Learning Journey?</h2>
        <p>Join hundreds of successful students who have achieved their language goals with NT2 Taallessen International.</p>
        <div class="cta-buttons">
          <a href="<?php echo $basePath; ?>pages/register.php" class="btn-primary">Register Now</a>
          <a href="<?php echo $basePath; ?>pages/contact.php" class="btn-secondary">Contact Us</a>
        </div>
      </div>
    </div>
  </section>

  <?php include $basePath . 'includes/footer.php'; ?>

  <script>
    // Add smooth scrolling and animations
    document.addEventListener('DOMContentLoaded', function() {
      // Animate statistics on scroll
      const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px 0px -100px 0px'
      };

      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('animate');
          }
        });
      }, observerOptions);

      // Observe all stat cards
      document.querySelectorAll('.stat-card').forEach(card => {
        observer.observe(card);
      });

      // Add hover effects to team cards
      document.querySelectorAll('.team-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
          this.style.transform = 'translateY(-8px)';
        });
        
        card.addEventListener('mouseleave', function() {
          this.style.transform = 'translateY(0)';
        });
      });
    });
  </script>
</body>
</html>
