<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: index.php");
  exit;
}

include '../includes/db_connect.php';

// Ensure necessary columns exist
$check_columns = $conn->query("SHOW COLUMNS FROM registrations LIKE 'start_date'");
if ($check_columns->num_rows == 0) {
    $alter_sql = "ALTER TABLE registrations 
                  ADD COLUMN start_date DATE NULL,
                  ADD COLUMN end_date DATE NULL,
                  ADD COLUMN payment_status VARCHAR(50) DEFAULT 'Pending',
                  ADD COLUMN amount_paid DECIMAL(10,2) DEFAULT 0,
                  ADD COLUMN total_amount DECIMAL(10,2) DEFAULT 0,
                  ADD COLUMN phone VARCHAR(20) NULL,
                  ADD COLUMN address TEXT NULL,
                  ADD COLUMN emergency_contact VARCHAR(100) NULL,
                  ADD COLUMN notes TEXT NULL";
    $conn->query($alter_sql);
}

// Update registered student
if (isset($_POST['update_student'])) {
    $id = intval($_POST['id']);
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    $payment_status = $_POST['payment_status'] ?? 'Pending';
    $amount_paid = floatval($_POST['amount_paid'] ?? 0);
    $total_amount = floatval($_POST['total_amount'] ?? 0);
    $phone = $_POST['phone'] ?? null;
    $address = $_POST['address'] ?? null;
    $emergency_contact = $_POST['emergency_contact'] ?? null;
    $notes = $_POST['notes'] ?? null;
    $total_lessons = intval($_POST['total_lessons'] ?? 0);

    $sql = "UPDATE registrations SET 
            start_date = ?, 
            end_date = ?, 
            payment_status = ?, 
            amount_paid = ?, 
            total_amount = ?,
            phone = ?,
            address = ?,
            emergency_contact = ?,
            notes = ?,
            total_lessons = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sssddssssii", $start_date, $end_date, $payment_status, $amount_paid, $total_amount, $phone, $address, $emergency_contact, $notes, $total_lessons, $id);
        $stmt->execute();
        $stmt->close();
        $success = "Student information updated successfully!";
    }
}

// Change status to Registered
if (isset($_POST['register_student'])) {
    $id = intval($_POST['id']);
    $conn->query("UPDATE registrations SET status='Registered' WHERE id=$id");
    header("Location: registered_students.php?success=Student registered");
    exit;
}

// Delete registered student
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM registrations WHERE id = $id");
    header("Location: registered_students.php?deleted=1");
    exit;
}

// Get all registered students
$result = $conn->query("SELECT * FROM registrations WHERE status='Registered' ORDER BY start_date DESC");

// Statistics
$total_registered = $conn->query("SELECT COUNT(*) AS c FROM registrations WHERE status='Registered'")->fetch_assoc()['c'];
$total_paid = $conn->query("SELECT COUNT(*) AS c FROM registrations WHERE status='Registered' AND payment_status='Paid'")->fetch_assoc()['c'];
$total_pending = $conn->query("SELECT COUNT(*) AS c FROM registrations WHERE status='Registered' AND payment_status='Pending'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <title>Registered Students - Admin</title>
  <link rel="stylesheet" href="../assets/css/style.css?v=<?= time() ?>">
  <style>
    .registered-body {
      background: #f8f9fa;
    }
    .registered-header {
      background: linear-gradient(135deg, #2c5282 0%, #1a365d 100%);
      color: white;
      padding: 2rem;
      border-radius: 0;
    }
    .registered-header h1 {
      margin: 0 0 1rem 0;
      font-size: 2rem;
    }
    .student-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1.5rem;
      padding: 2rem;
    }
    .student-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.1);
      padding: 1.5rem;
      border-left: 4px solid #2c5282;
      transition: all 0.3s ease;
    }
    .student-card:hover {
      box-shadow: 0 4px 16px rgba(0,0,0,0.15);
      transform: translateY(-2px);
    }
    .student-card h3 {
      margin: 0 0 1rem 0;
      color: #1a365d;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .student-info {
      font-size: 0.95rem;
      margin-bottom: 0.5rem;
      color: #555;
    }
    .student-info strong {
      color: #1a365d;
      display: inline-block;
      min-width: 120px;
    }
    .payment-status {
      display: inline-block;
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
      margin-top: 0.5rem;
    }
    .payment-status.paid {
      background: #d1fae5;
      color: #065f46;
    }
    .payment-status.pending {
      background: #fef3c7;
      color: #92400e;
    }
    .payment-status.partial {
      background: #dbeafe;
      color: #0c4a6e;
    }
    .card-actions {
      display: flex;
      gap: 0.5rem;
      margin-top: 1rem;
      flex-wrap: wrap;
    }
    .btn-small {
      padding: 0.4rem 0.8rem;
      font-size: 0.85rem;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      background: #2c5282;
      color: white;
      transition: all 0.2s ease;
    }
    .btn-small:hover {
      background: #1a365d;
    }
    .btn-danger-small {
      background: #dc2626;
    }
    .btn-danger-small:hover {
      background: #b91c1c;
    }
    .modal-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      z-index: 1000;
      justify-content: center;
      align-items: center;
    }
    .modal-overlay.show {
      display: flex;
    }
    .modal-content {
      background: white;
      padding: 2rem;
      border-radius: 12px;
      max-width: 500px;
      max-height: 90vh;
      overflow-y: auto;
      width: 90%;
    }
    .modal-content h2 {
      margin-top: 0;
      color: #1a365d;
    }
    .form-group {
      margin-bottom: 1rem;
    }
    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: #333;
      font-size: 0.9rem;
    }
    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 0.6rem;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-family: inherit;
      font-size: 0.9rem;
    }
    .form-group textarea {
      resize: vertical;
      min-height: 80px;
    }
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }
    .modal-buttons {
      display: flex;
      gap: 1rem;
      margin-top: 1.5rem;
      justify-content: flex-end;
    }
    .btn-primary {
      background: #2c5282;
      color: white;
      padding: 0.6rem 1.2rem;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
    }
    .btn-primary:hover {
      background: #1a365d;
    }
    .btn-cancel {
      background: #e5e7eb;
      color: #333;
      padding: 0.6rem 1.2rem;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    .btn-cancel:hover {
      background: #d1d5db;
    }
    .stats-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      padding: 2rem;
      background: white;
      margin: 2rem;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .stat-card {
      text-align: center;
      padding: 1rem;
    }
    .stat-card h3 {
      margin: 0;
      font-size: 2rem;
      color: #2c5282;
    }
    .stat-card p {
      margin: 0.5rem 0 0 0;
      color: #666;
      font-size: 0.9rem;
    }
    .filters {
      padding: 2rem;
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
      align-items: center;
    }
    .filters input,
    .filters select {
      padding: 0.6rem;
      border: 1px solid #ddd;
      border-radius: 6px;
    }
    .alert {
      padding: 1rem;
      border-radius: 6px;
      margin: 2rem;
    }
    .alert-success {
      background: #d1fae5;
      color: #065f46;
      border: 1px solid #6ee7b7;
    }
  </style>
  <script>
    function openEditModal(id, name, email, course, startDate, endDate, paymentStatus, amountPaid, totalAmount, phone, address, emergency, notes, lessons) {
      document.getElementById('editModal').classList.add('show');
      document.getElementById('studentId').value = id;
      document.getElementById('studentName').textContent = name + ' (' + email + ')';
      document.getElementById('courseType').value = course;
      document.getElementById('startDate').value = startDate || '';
      document.getElementById('endDate').value = endDate || '';
      document.getElementById('paymentStatus').value = paymentStatus || 'Pending';
      document.getElementById('amountPaid').value = amountPaid || '0';
      document.getElementById('totalAmount').value = totalAmount || '0';
      document.getElementById('phone').value = phone || '';
      document.getElementById('address').value = address || '';
      document.getElementById('emergencyContact').value = emergency || '';
      document.getElementById('studentNotes').value = notes || '';
      document.getElementById('totalLessons').value = lessons || '0';
    }

    function closeEditModal() {
      document.getElementById('editModal').classList.remove('show');
    }

    function searchStudents() {
      const query = document.getElementById('searchInput').value.toLowerCase();
      document.querySelectorAll('.student-card').forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(query) ? '' : 'none';
      });
    }

    function filterByPayment(status) {
      document.querySelectorAll('.student-card').forEach(card => {
        if (status === 'All' || card.getAttribute('data-payment') === status) {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });
    }
  </script>
</head>
<body class="registered-body">

<header class="registered-header">
  <h1>👥 Registered Students Management</h1>
  <div style="display: flex; gap: 1rem; align-items: center;">
    <span>Total Registered: <strong><?= $total_registered ?></strong></span>
    <a href="dashboard.php" class="btn small" style="background: white; color: #2c5282;">← Dashboard</a>
  </div>
</header>

<?php if (isset($_GET['success'])): ?>
  <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<div class="stats-container">
  <div class="stat-card">
    <h3><?= $total_registered ?></h3>
    <p>Total Students</p>
  </div>
  <div class="stat-card">
    <h3><?= $total_paid ?></h3>
    <p>Payments Complete</p>
  </div>
  <div class="stat-card">
    <h3><?= $total_pending ?></h3>
    <p>Pending Payment</p>
  </div>
</div>

<div class="filters">
  <input type="text" id="searchInput" placeholder="Search by name or email..." onkeyup="searchStudents()" style="flex: 1; max-width: 400px;">
  <select onchange="filterByPayment(this.value)" style="width: auto;">
    <option value="All">All Payment Status</option>
    <option value="Paid">Paid</option>
    <option value="Pending">Pending</option>
    <option value="Partial">Partial</option>
  </select>
  <a href="pending_payments.php" class="btn small" style="background: #ef4444; color: white;">💳 View Pending Payments</a>
</div>

<div class="student-grid">
  <?php while($row = $result->fetch_assoc()): 
    $payment_class = strtolower($row['payment_status'] ?? 'Pending');
    if ($row['amount_paid'] > 0 && $row['amount_paid'] < $row['total_amount']) {
      $payment_class = 'partial';
    }
  ?>
  <div class="student-card" data-payment="<?= $row['payment_status'] ?? 'Pending' ?>" onclick="openEditModal(
    <?= $row['id'] ?>,
    '<?= htmlspecialchars(addslashes($row['name'])) ?>',
    '<?= htmlspecialchars(addslashes($row['email'])) ?>',
    '<?= htmlspecialchars(addslashes($row['course'])) ?>',
    '<?= $row['start_date'] ?>',
    '<?= $row['end_date'] ?>',
    '<?= htmlspecialchars($row['payment_status'] ?? 'Pending') ?>',
    '<?= $row['amount_paid'] ?? 0 ?>',
    '<?= $row['total_amount'] ?? 0 ?>',
    '<?= htmlspecialchars(addslashes($row['phone'] ?? '')) ?>',
    '<?= htmlspecialchars(addslashes($row['address'] ?? '')) ?>',
    '<?= htmlspecialchars(addslashes($row['emergency_contact'] ?? '')) ?>',
    `<?= htmlspecialchars($row['notes'] ?? '') ?>`,
    '<?= $row['total_lessons'] ?? 0 ?>'
  ); return false;" style="cursor: pointer;">
    <h3>
      <?= htmlspecialchars($row['name']) ?>
    </h3>
    <div class="student-info"><strong>Email:</strong> <?= htmlspecialchars($row['email']) ?></div>
    <div class="student-info"><strong>Course:</strong> <?= htmlspecialchars($row['course']) ?></div>
    <div class="student-info"><strong>Phone:</strong> <?= htmlspecialchars($row['phone'] ?? 'N/A') ?></div>
    <div class="student-info"><strong>Lessons:</strong> <?= intval($row['total_lessons'] ?? 0) ?> 📚</div>
    <div class="student-info"><strong>Start Date:</strong> <?= $row['start_date'] ? date('d-m-Y', strtotime($row['start_date'])) : 'Not set' ?></div>
    <div class="student-info"><strong>End Date:</strong> <?= $row['end_date'] ? date('d-m-Y', strtotime($row['end_date'])) : 'Not set' ?></div>
    <div class="student-info"><strong>Duration:</strong> <?= htmlspecialchars($row['preferred_time'] ?? 'N/A') ?></div>
    <div class="student-info"><strong>Payment:</strong> €<?= number_format($row['amount_paid'] ?? 0, 2) ?> / €<?= number_format($row['total_amount'] ?? 0, 2) ?></div>
    <span class="payment-status <?= $payment_class ?>"><?= htmlspecialchars($row['payment_status'] ?? 'Pending') ?></span>
    
    <div class="card-actions" onclick="event.stopPropagation();">
      <a href="?delete=<?= $row['id'] ?>" class="btn-small btn-danger-small" onclick="return confirm('Delete this student record?')">Delete</a>
    </div>
  </div>
  <?php endwhile; ?>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal-overlay" onclick="if(event.target === this) closeEditModal()">
  <div class="modal-content">
    <h2>Edit Student Information</h2>
    <p style="margin: 0 0 1.5rem 0;"><strong id="studentName"></strong></p>
    
    <form method="POST" action="">
      <input type="hidden" id="studentId" name="id">
      
      <div class="form-row">
        <div class="form-group">
          <label>Start Date *</label>
          <input type="date" id="startDate" name="start_date" required>
        </div>
        <div class="form-group">
          <label>End Date</label>
          <input type="date" id="endDate" name="end_date">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Payment Status *</label>
          <select id="paymentStatus" name="payment_status" required>
            <option value="Pending">Pending</option>
            <option value="Partial">Partial Payment</option>
            <option value="Paid">Paid</option>
          </select>
        </div>
        <div class="form-group">
          <label>Total Lessons</label>
          <input type="number" id="totalLessons" name="total_lessons" min="0" value="0">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Amount Paid (€)</label>
          <input type="number" id="amountPaid" name="amount_paid" step="0.01" value="0">
        </div>
        <div class="form-group">
          <label>Total Amount (€)</label>
          <input type="number" id="totalAmount" name="total_amount" step="0.01" value="0">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Phone Number</label>
          <input type="tel" id="phone" name="phone">
        </div>
        <div class="form-group">
          <label>Course Type</label>
          <input type="text" id="courseType" name="course_type" readonly>
        </div>
      </div>

      <div class="form-group">
        <label>Address</label>
        <input type="text" id="address" name="address">
      </div>

      <div class="form-group">
        <label>Emergency Contact</label>
        <input type="text" id="emergencyContact" name="emergency_contact">
      </div>

      <div class="form-group">
        <label>Additional Notes</label>
        <textarea id="studentNotes" name="notes"></textarea>
      </div>

      <div class="modal-buttons">
        <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
        <button type="submit" name="update_student" class="btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

</body>
</html>
