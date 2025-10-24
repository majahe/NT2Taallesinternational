<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: index.php");
  exit;
}

include '../includes/db_connect.php';

// Handle course scheduling
if (isset($_POST['schedule_course'])) {
  $registration_id = intval($_POST['registration_id']);
  $course_date = $_POST['course_date'];
  $course_time = $_POST['course_time'];
  $instructor = $_POST['instructor'];
  $location = $_POST['location'];
  $notes = $_POST['notes'];
  
  // Update the registration with course details
  $sql = "UPDATE registrations SET 
          course_date = ?, 
          course_time = ?, 
          instructor = ?, 
          location = ?, 
          planning_notes = ?,
          status = 'Scheduled'
          WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssssi", $course_date, $course_time, $instructor, $location, $notes, $registration_id);
  $stmt->execute();
  $stmt->close();
  
  header("Location: planning.php?success=1");
  exit;
}

// Get all planned registrations
$planned_registrations = $conn->query("
  SELECT * FROM registrations 
  WHERE status = 'Planned' 
  ORDER BY created_at ASC
");

// Get scheduled courses for calendar view
$scheduled_courses = $conn->query("
  SELECT * FROM registrations 
  WHERE status = 'Scheduled' AND course_date IS NOT NULL
  ORDER BY course_date, course_time ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Course Planning - Admin Dashboard</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    .planning-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }
    
    .planning-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 30px;
    }
    
    .planning-card {
      background: white;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .course-calendar {
      grid-column: 1 / -1;
    }
    
    .calendar-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 1px;
      background: #ddd;
      border: 1px solid #ddd;
    }
    
    .calendar-day {
      background: white;
      padding: 10px;
      min-height: 80px;
      border: 1px solid #eee;
    }
    
    .calendar-day.other-month {
      background: #f9f9f9;
      color: #999;
    }
    
    .course-slot {
      background: #e3f2fd;
      border: 1px solid #2196f3;
      border-radius: 4px;
      padding: 4px;
      margin: 2px 0;
      font-size: 12px;
    }
    
    .schedule-form {
      background: #f8f9fa;
      padding: 15px;
      border-radius: 5px;
      margin-top: 10px;
    }
    
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      margin-bottom: 10px;
    }
    
    .form-group {
      display: flex;
      flex-direction: column;
    }
    
    .form-group label {
      font-weight: bold;
      margin-bottom: 5px;
    }
    
    .form-group input, .form-group select, .form-group textarea {
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 4px;
    }
    
    .btn {
      background: #2196f3;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 4px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
    }
    
    .btn:hover {
      background: #1976d2;
    }
    
    .btn.small {
      padding: 5px 10px;
      font-size: 12px;
    }
    
    .btn.danger {
      background: #f44336;
    }
    
    .btn.danger:hover {
      background: #d32f2f;
    }
    
    .planned-student {
      border: 1px solid #ddd;
      padding: 15px;
      margin-bottom: 10px;
      border-radius: 5px;
      background: #fff;
    }
    
    .student-info {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      margin-bottom: 10px;
    }
    
    .success-message {
      background: #d4edda;
      color: #155724;
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 20px;
      border: 1px solid #c3e6cb;
    }
    
    .admin-header {
      background: #2196f3;
      color: white;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 8px;
    }
    
    .admin-header h1 {
      margin: 0 0 10px 0;
    }
    
    .admin-controls {
      display: flex;
      gap: 10px;
      align-items: center;
    }
    
    .admin-controls span {
      margin-right: 20px;
    }
  </style>
</head>
<body class="dashboard-body">
  <header class="admin-header">
    <h1>📅 Course Planning</h1>
    <div class="admin-controls">
      <span>Logged in as: <strong><?= $_SESSION['admin'] ?></strong></span>
      <a href="dashboard.php" class="btn small">← Back to Dashboard</a>
      <a href="logout.php" class="btn danger small">Logout</a>
    </div>
  </header>

  <div class="planning-container">
    <?php if(isset($_GET['success'])): ?>
      <div class="success-message">
        Course scheduled successfully!
      </div>
    <?php endif; ?>

    <div class="planning-grid">
      <!-- Planned Students Section -->
      <div class="planning-card">
        <h2>📋 Students to Schedule (<?= $planned_registrations->num_rows ?>)</h2>
        
        <?php while($student = $planned_registrations->fetch_assoc()): ?>
        <div class="planned-student">
          <div class="student-info">
            <div>
              <strong><?= htmlspecialchars($student['name']) ?></strong><br>
              <small><?= htmlspecialchars($student['email']) ?></small>
            </div>
            <div>
              <strong>Course:</strong> <?= htmlspecialchars($student['course']) ?><br>
              <strong>Language:</strong> <?= htmlspecialchars($student['spoken_language']) ?><br>
              <strong>Preferred Time:</strong> <?= htmlspecialchars($student['preferred_time']) ?>
            </div>
          </div>
          
          <form method="POST" class="schedule-form">
            <input type="hidden" name="registration_id" value="<?= $student['id'] ?>">
            
            <div class="form-row">
              <div class="form-group">
                <label>Course Date</label>
                <input type="date" name="course_date" required>
              </div>
              <div class="form-group">
                <label>Course Time</label>
                <input type="time" name="course_time" required>
              </div>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label>Instructor</label>
                <select name="instructor" required>
                  <option value="">Select Instructor</option>
                  <option value="Dr. Maria van der Berg">Dr. Maria van der Berg</option>
                  <option value="Prof. Jan de Vries">Prof. Jan de Vries</option>
                  <option value="Ms. Anna Schmidt">Ms. Anna Schmidt</option>
                  <option value="Mr. Peter Bakker">Mr. Peter Bakker</option>
                </select>
              </div>
              <div class="form-group">
                <label>Location</label>
                <select name="location" required>
                  <option value="">Select Location</option>
                  <option value="Room A101">Room A101</option>
                  <option value="Room A102">Room A102</option>
                  <option value="Room B201">Room B201</option>
                  <option value="Online (Zoom)">Online (Zoom)</option>
                </select>
              </div>
            </div>
            
            <div class="form-group">
              <label>Notes</label>
              <textarea name="notes" rows="2" placeholder="Additional notes for this course..."></textarea>
            </div>
            
            <button type="submit" name="schedule_course" class="btn">Schedule Course</button>
          </form>
        </div>
        <?php endwhile; ?>
        
        <?php if($planned_registrations->num_rows == 0): ?>
          <p style="text-align: center; color: #666; padding: 20px;">
            No students with "Planned" status found.
          </p>
        <?php endif; ?>
      </div>

      <!-- Course Calendar Section -->
      <div class="planning-card course-calendar">
        <h2>📅 Course Calendar</h2>
        <div class="calendar-grid">
          <!-- Calendar header -->
          <div style="background: #2196f3; color: white; text-align: center; padding: 10px; font-weight: bold;">Mon</div>
          <div style="background: #2196f3; color: white; text-align: center; padding: 10px; font-weight: bold;">Tue</div>
          <div style="background: #2196f3; color: white; text-align: center; padding: 10px; font-weight: bold;">Wed</div>
          <div style="background: #2196f3; color: white; text-align: center; padding: 10px; font-weight: bold;">Thu</div>
          <div style="background: #2196f3; color: white; text-align: center; padding: 10px; font-weight: bold;">Fri</div>
          <div style="background: #2196f3; color: white; text-align: center; padding: 10px; font-weight: bold;">Sat</div>
          <div style="background: #2196f3; color: white; text-align: center; padding: 10px; font-weight: bold;">Sun</div>
          
          <!-- Calendar days would be generated here -->
          <!-- This is a simplified version - you'd want to generate the full calendar -->
          <?php
          $current_date = date('Y-m-d');
          $courses_by_date = [];
          
          while($course = $scheduled_courses->fetch_assoc()) {
            $courses_by_date[$course['course_date']][] = $course;
          }
          
          // Generate calendar days (simplified - shows next 7 days)
          for($i = 0; $i < 7; $i++): 
            $date = date('Y-m-d', strtotime("+$i days"));
            $day_name = date('D', strtotime($date));
            $day_number = date('j', strtotime($date));
          ?>
          <div class="calendar-day">
            <strong><?= $day_name ?> <?= $day_number ?></strong>
            <?php if(isset($courses_by_date[$date])): ?>
              <?php foreach($courses_by_date[$date] as $course): ?>
                <div class="course-slot">
                  <strong><?= htmlspecialchars($course['name']) ?></strong><br>
                  <small><?= $course['course_time'] ?> - <?= htmlspecialchars($course['course']) ?></small><br>
                  <small><?= htmlspecialchars($course['instructor']) ?></small>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <?php endfor; ?>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
