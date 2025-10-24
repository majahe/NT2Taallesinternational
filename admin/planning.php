<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: index.php");
  exit;
}

include '../includes/db_connect.php';

// First, update the status ENUM to include 'Scheduled'
$alter_status_sql = "ALTER TABLE registrations MODIFY COLUMN status ENUM('New', 'Pending', 'Planned', 'Scheduled', 'Completed', 'Cancelled') DEFAULT 'New'";
$conn->query($alter_status_sql);

// Check if required columns exist, if not add them
$columns_check = $conn->query("SHOW COLUMNS FROM registrations LIKE 'course_date'");
if ($columns_check->num_rows == 0) {
    // Add missing columns
    $alter_sql = "ALTER TABLE registrations 
                  ADD COLUMN course_date DATE NULL,
                  ADD COLUMN course_time TIME NULL,
                  ADD COLUMN instructor VARCHAR(100) NULL,
                  ADD COLUMN location VARCHAR(100) NULL,
                  ADD COLUMN planning_notes TEXT NULL";
    $conn->query($alter_sql);
}

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
  if ($stmt) {
    $stmt->bind_param("sssssi", $course_date, $course_time, $instructor, $location, $notes, $registration_id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: planning.php?success=1");
    exit;
  } else {
    $error_message = "Database error: " . $conn->error;
  }
}

// Handle rescheduling of already scheduled courses
if (isset($_POST['reschedule_course'])) {
  $registration_id = intval($_POST['registration_id']);
  $course_date = $_POST['course_date'];
  $course_time = $_POST['course_time'];
  $instructor = $_POST['instructor'];
  $location = $_POST['location'];
  $notes = $_POST['notes'];
  
  $sql = "UPDATE registrations SET 
          course_date = ?, 
          course_time = ?, 
          instructor = ?, 
          location = ?, 
          planning_notes = ?
          WHERE id = ?";
  $stmt = $conn->prepare($sql);
  if ($stmt) {
    $stmt->bind_param("sssssi", $course_date, $course_time, $instructor, $location, $notes, $registration_id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: planning.php?success=1&type=rescheduled");
    exit;
  } else {
    $error_message = "Database error: " . $conn->error;
  }
}

// Get all planned registrations
$planned_registrations = $conn->query("
  SELECT * FROM registrations 
  WHERE status = 'Planned' 
  ORDER BY created_at ASC
");

// Get scheduled registrations for rescheduling
$scheduled_registrations = $conn->query("
  SELECT * FROM registrations 
  WHERE status = 'Scheduled' 
  ORDER BY course_date ASC
");

// Get scheduled courses for calendar view
$scheduled_courses = $conn->query("
  SELECT * FROM registrations 
  WHERE status = 'Scheduled' AND course_date IS NOT NULL
  ORDER BY course_date, course_time ASC
");

// Calendar view and navigation
$view = isset($_GET['view']) ? $_GET['view'] : 'week';
$current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Calculate start and end dates based on view
if ($view === 'month') {
    $start_date = date('Y-m-01', mktime(0, 0, 0, $current_month, 1, $current_year));
    $end_date = date('Y-m-t', mktime(0, 0, 0, $current_month, 1, $current_year));
} else {
    // Week view - get current week
    $week_start = isset($_GET['week_start']) ? $_GET['week_start'] : date('Y-m-d', strtotime('monday this week'));
    $start_date = $week_start;
    $end_date = date('Y-m-d', strtotime($week_start . ' +6 days'));
}
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
    
    .calendar-day.today {
      background: #fff3cd;
      border: 2px solid #ffc107;
    }
    
    .calendar-day.other-month {
      background: #f8f9fa;
      color: #6c757d;
    }
  </style>
  <script>
    function changeView(view) {
      const url = new URL(window.location);
      url.searchParams.set('view', view);
      if (view === 'week') {
        url.searchParams.delete('month');
        url.searchParams.delete('year');
      }
      window.location.href = url.toString();
    }
    
    function changeMonth(month) {
      const url = new URL(window.location);
      url.searchParams.set('month', month);
      url.searchParams.set('view', 'month');
      window.location.href = url.toString();
    }
    
    function changeYear(year) {
      const url = new URL(window.location);
      url.searchParams.set('year', year);
      url.searchParams.set('view', 'month');
      window.location.href = url.toString();
    }
    
    function navigateCalendar(direction) {
      const url = new URL(window.location);
      const currentView = url.searchParams.get('view') || 'week';
      
      if (currentView === 'month') {
        let month = parseInt(url.searchParams.get('month')) || new Date().getMonth() + 1;
        let year = parseInt(url.searchParams.get('year')) || new Date().getFullYear();
        
        if (direction === 'prev') {
          month--;
          if (month < 1) {
            month = 12;
            year--;
          }
        } else if (direction === 'next') {
          month++;
          if (month > 12) {
            month = 1;
            year++;
          }
        } else if (direction === 'today') {
          const today = new Date();
          month = today.getMonth() + 1;
          year = today.getFullYear();
        }
        
        url.searchParams.set('month', month);
        url.searchParams.set('year', year);
      } else {
        // Week view
        let weekStart = url.searchParams.get('week_start');
        if (!weekStart) {
          weekStart = getMonday(new Date()).toISOString().split('T')[0];
        }
        
        if (direction === 'prev') {
          weekStart = new Date(new Date(weekStart).getTime() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
        } else if (direction === 'next') {
          weekStart = new Date(new Date(weekStart).getTime() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
        } else if (direction === 'today') {
          weekStart = getMonday(new Date()).toISOString().split('T')[0];
        }
        
        url.searchParams.set('week_start', weekStart);
      }
      
      window.location.href = url.toString();
    }
    
    function getMonday(date) {
      const d = new Date(date);
      const day = d.getDay();
      const diff = d.getDate() - day + (day === 0 ? -6 : 1);
      return new Date(d.setDate(diff));
    }
  </script>
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
        <?php if(isset($_GET['type']) && $_GET['type'] === 'rescheduled'): ?>
          Course rescheduled successfully!
        <?php else: ?>
          Course scheduled successfully!
        <?php endif; ?>
      </div>
    <?php endif; ?>
    
    <?php if(isset($error_message)): ?>
      <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
        Error: <?= htmlspecialchars($error_message) ?>
      </div>
    <?php endif; ?>

    <div class="planning-grid">
      <!-- Planned Students Section -->
      <div class="planning-card">
        <h2>📋 Students to Schedule (<?= $planned_registrations ? $planned_registrations->num_rows : 0 ?>)</h2>
        
        <?php if($planned_registrations && $planned_registrations->num_rows > 0): ?>
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
        <?php else: ?>
          <p style="text-align: center; color: #666; padding: 20px;">
            No students with "Planned" status found.
          </p>
        <?php endif; ?>
      </div>

      <!-- Scheduled Students Section for Rescheduling -->
      <div class="planning-card">
        <h2>🔄 Reschedule Students (<?= $scheduled_registrations ? $scheduled_registrations->num_rows : 0 ?>)</h2>
        
        <?php if($scheduled_registrations && $scheduled_registrations->num_rows > 0): ?>
        <?php while($student = $scheduled_registrations->fetch_assoc()): ?>
        <div class="planned-student">
          <div class="student-info">
            <div>
              <strong><?= htmlspecialchars($student['name']) ?></strong><br>
              <small><?= htmlspecialchars($student['email']) ?></small>
            </div>
            <div>
              <strong>Course:</strong> <?= htmlspecialchars($student['course']) ?><br>
              <strong>Current Date:</strong> <?= htmlspecialchars($student['course_date']) ?><br>
              <strong>Current Time:</strong> <?= htmlspecialchars($student['course_time']) ?>
            </div>
          </div>
          
          <form method="POST" class="schedule-form">
            <input type="hidden" name="registration_id" value="<?= $student['id'] ?>">
            
            <div class="form-row">
              <div class="form-group">
                <label>New Course Date</label>
                <input type="date" name="course_date" value="<?= htmlspecialchars($student['course_date']) ?>" required>
              </div>
              <div class="form-group">
                <label>New Course Time</label>
                <input type="time" name="course_time" value="<?= htmlspecialchars($student['course_time']) ?>" required>
              </div>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label>Instructor</label>
                <select name="instructor" required>
                  <option value="">Select Instructor</option>
                  <option value="Dr. Maria van der Berg" <?= $student['instructor'] === 'Dr. Maria van der Berg' ? 'selected' : '' ?>>Dr. Maria van der Berg</option>
                  <option value="Prof. Jan de Vries" <?= $student['instructor'] === 'Prof. Jan de Vries' ? 'selected' : '' ?>>Prof. Jan de Vries</option>
                  <option value="Ms. Anna Schmidt" <?= $student['instructor'] === 'Ms. Anna Schmidt' ? 'selected' : '' ?>>Ms. Anna Schmidt</option>
                  <option value="Mr. Peter Bakker" <?= $student['instructor'] === 'Mr. Peter Bakker' ? 'selected' : '' ?>>Mr. Peter Bakker</option>
                </select>
              </div>
              <div class="form-group">
                <label>Location</label>
                <select name="location" required>
                  <option value="">Select Location</option>
                  <option value="Room A101" <?= $student['location'] === 'Room A101' ? 'selected' : '' ?>>Room A101</option>
                  <option value="Room A102" <?= $student['location'] === 'Room A102' ? 'selected' : '' ?>>Room A102</option>
                  <option value="Room B201" <?= $student['location'] === 'Room B201' ? 'selected' : '' ?>>Room B201</option>
                  <option value="Online (Zoom)" <?= $student['location'] === 'Online (Zoom)' ? 'selected' : '' ?>>Online (Zoom)</option>
                </select>
              </div>
            </div>
            
            <div class="form-group">
              <label>Notes</label>
              <textarea name="notes" rows="2" placeholder="Additional notes for this course..."><?= htmlspecialchars($student['planning_notes']) ?></textarea>
            </div>
            
            <button type="submit" name="reschedule_course" class="btn">Update Schedule</button>
          </form>
        </div>
        <?php endwhile; ?>
        <?php else: ?>
          <p style="text-align: center; color: #666; padding: 20px;">
            No scheduled students found.
          </p>
        <?php endif; ?>
      </div>

      <!-- Course Calendar Section -->
      <div class="planning-card course-calendar">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
          <h2>📅 Course Calendar</h2>
          
          <!-- View Selection -->
          <div style="display: flex; gap: 10px; align-items: center;">
            <label>View:</label>
            <select onchange="changeView(this.value)" style="padding: 5px;">
              <option value="week" <?= $view === 'week' ? 'selected' : '' ?>>Week</option>
              <option value="month" <?= $view === 'month' ? 'selected' : '' ?>>Month</option>
            </select>
          </div>
        </div>

        <!-- Navigation Controls -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
          <div style="display: flex; gap: 10px; align-items: center;">
            <button onclick="navigateCalendar('prev')" class="btn small">← Previous</button>
            <button onclick="navigateCalendar('today')" class="btn small">Today</button>
            <button onclick="navigateCalendar('next')" class="btn small">Next →</button>
          </div>
          
          <div style="text-align: center;">
            <?php if ($view === 'month'): ?>
              <h3><?= date('F Y', mktime(0, 0, 0, $current_month, 1, $current_year)) ?></h3>
            <?php else: ?>
              <h3><?= date('M j', strtotime($start_date)) ?> - <?= date('M j, Y', strtotime($end_date)) ?></h3>
            <?php endif; ?>
          </div>
          
          <div style="display: flex; gap: 10px; align-items: center;">
            <label>Month:</label>
            <select onchange="changeMonth(this.value)" style="padding: 5px;">
              <?php for($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= $current_month == $m ? 'selected' : '' ?>>
                  <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                </option>
              <?php endfor; ?>
            </select>
            
            <label>Year:</label>
            <select onchange="changeYear(this.value)" style="padding: 5px;">
              <?php for($y = date('Y') - 1; $y <= date('Y') + 2; $y++): ?>
                <option value="<?= $y ?>" <?= $current_year == $y ? 'selected' : '' ?>><?= $y ?></option>
              <?php endfor; ?>
            </select>
          </div>
        </div>

        <div class="calendar-grid">
          <?php
          $courses_by_date = [];
          if($scheduled_courses) {
            while($course = $scheduled_courses->fetch_assoc()) {
              $courses_by_date[$course['course_date']][] = $course;
            }
          }
          
          if ($view === 'month'): 
            // Month view
            $first_day = date('w', mktime(0, 0, 0, $current_month, 1, $current_year));
            $days_in_month = date('t', mktime(0, 0, 0, $current_month, 1, $current_year));
            
            // Calendar header
            $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            foreach($days as $day): ?>
              <div style="background: #2196f3; color: white; text-align: center; padding: 10px; font-weight: bold;"><?= $day ?></div>
            <?php endforeach;
            
            // Empty cells for days before month starts
            for($i = 0; $i < $first_day; $i++): ?>
              <div class="calendar-day other-month"></div>
            <?php endfor;
            
            // Days of the month
            for($day = 1; $day <= $days_in_month; $day++): 
              $date = sprintf('%04d-%02d-%02d', $current_year, $current_month, $day);
              $is_today = $date === date('Y-m-d');
              $day_name = date('D', strtotime($date));
            ?>
              <div class="calendar-day <?= $is_today ? 'today' : '' ?>">
                <strong><?= $day ?></strong>
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
            <?php endfor;
            
          else: 
            // Week view
            $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            foreach($days as $day): ?>
              <div style="background: #2196f3; color: white; text-align: center; padding: 10px; font-weight: bold;"><?= $day ?></div>
            <?php endforeach;
            
            // Week days
            for($i = 0; $i < 7; $i++): 
              $date = date('Y-m-d', strtotime($start_date . " +$i days"));
              $day_name = date('D', strtotime($date));
              $day_number = date('j', strtotime($date));
              $is_today = $date === date('Y-m-d');
            ?>
              <div class="calendar-day <?= $is_today ? 'today' : '' ?>">
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
            <?php endfor;
          endif; ?>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
