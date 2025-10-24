<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: index.php");
  exit;
}

include '../includes/db_connect.php';

// Verwijderen van record
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $conn->query("DELETE FROM registrations WHERE id = $id");
  header("Location: dashboard.php");
  exit;
}

// Statusupdate via AJAX
if (isset($_POST['update_status'])) {
  $id = intval($_POST['id']);
  $status = $_POST['status'];
  $conn->query("UPDATE registrations SET status='$status' WHERE id=$id");
  echo "OK";
  exit;
}

// Alle data ophalen
$result = $conn->query("SELECT * FROM registrations ORDER BY created_at DESC");

// Statistieken
$total = $conn->query("SELECT COUNT(*) AS total FROM registrations")->fetch_assoc()['total'];
$new = $conn->query("SELECT COUNT(*) AS c FROM registrations WHERE status='New'")->fetch_assoc()['c'];
$pending = $conn->query("SELECT COUNT(*) AS c FROM registrations WHERE status='Pending'")->fetch_assoc()['c'];
$planned = $conn->query("SELECT COUNT(*) AS c FROM registrations WHERE status='Planned'")->fetch_assoc()['c'];
$scheduled = $conn->query("SELECT COUNT(*) AS c FROM registrations WHERE status='Scheduled'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <title>Admin Dashboard - Learn Dutch</title>
  <link rel="stylesheet" href="../assets/css/style.css?v=<?= time() ?>">
  <script>
  // AJAX status update
  function updateStatus(id, selectEl) {
    const status = selectEl.value;
    fetch('dashboard.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'update_status=1&id=' + id + '&status=' + status
    }).then(() => {
      selectEl.style.backgroundColor = '#e0f2fe';
      setTimeout(() => selectEl.style.backgroundColor = '', 700);
    });
  }

  // View popup
  function viewRegistration(name, email, course, spokenLanguage, preferredTime, message, date, status) {
    const modal = document.getElementById('viewModal');
    modal.classList.add('show');
    document.getElementById('modalName').textContent = name;
    document.getElementById('modalEmail').textContent = email;
    document.getElementById('modalCourse').textContent = course;
    document.getElementById('modalSpokenLanguage').textContent = spokenLanguage;
    document.getElementById('modalPreferredTime').textContent = preferredTime;
    document.getElementById('modalMessage').textContent = message || '(no message)';
    document.getElementById('modalDate').textContent = date;
    document.getElementById('modalStatus').textContent = status;
  }
  function closeModal() { 
    document.getElementById('viewModal').classList.remove('show'); 
  }

  // Filtering zonder herladen
  function filterRows(status) {
    const rows = document.querySelectorAll("tbody tr");
    rows.forEach(row => {
      const rowStatus = row.querySelector("select").value;
      if (status === "All" || rowStatus === status) {
        row.style.display = "";
      } else {
        row.style.display = "none";
      }
    });
    document.querySelectorAll(".filter-btn").forEach(btn => btn.classList.remove("active"));
    document.getElementById("btn-" + status).classList.add("active");
  }

  function searchTable() {
    const query = document.getElementById("searchInput").value.toLowerCase();
    document.querySelectorAll("tbody tr").forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(query) ? "" : "none";
    });
  }
  </script>
</head>
<body class="dashboard-body">

<header class="admin-header">
  <h1>📘 Admin Dashboard – Learn Dutch</h1>
  <div class="admin-controls">
    <span>Logged in as: <strong><?= $_SESSION['admin'] ?></strong></span>
    <a href="planning.php" class="btn small">📅 Course Planning</a>
    <a href="change_password.php" class="btn small">🔐 Change Password</a>
    <a href="../index.php" class="btn small">← Website</a>
    <a href="logout.php" class="btn danger small">Logout</a>
  </div>
</header>

<section class="stats-container">
  <div class="stat-card"><h2><?= $total ?></h2><p>Total</p></div>
  <div class="stat-card"><h2><?= $new ?></h2><p>New</p></div>
  <div class="stat-card"><h2><?= $pending ?></h2><p>Pending</p></div>
  <div class="stat-card"><h2><?= $planned ?></h2><p>Planned</p></div>
  <div class="stat-card"><h2><?= $scheduled ?></h2><p>Scheduled</p></div>
</section>

<section class="filters">
  <input type="text" id="searchInput" placeholder="Search by name or email..." onkeyup="searchTable()">
  <div class="filter-buttons">
    <button id="btn-All" class="btn filter-btn active" onclick="filterRows('All')">All</button>
    <button id="btn-New" class="btn filter-btn" onclick="filterRows('New')">New</button>
    <button id="btn-Pending" class="btn filter-btn" onclick="filterRows('Pending')">Pending</button>
    <button id="btn-Planned" class="btn filter-btn" onclick="filterRows('Planned')">Planned</button>
    <button id="btn-Scheduled" class="btn filter-btn" onclick="filterRows('Scheduled')">Scheduled</button>
  </div>
</section>

<section class="table-section">
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Course</th>
        <th>Language</th>
        <th>Time</th>
        <th>Status</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['course']) ?></td>
        <td><?= htmlspecialchars($row['spoken_language']) ?></td>
        <td><?= htmlspecialchars($row['preferred_time']) ?></td>
        <td>
          <select onchange="updateStatus(<?= $row['id'] ?>, this)">
            <option <?= $row['status']=='New'?'selected':'' ?>>New</option>
            <option <?= $row['status']=='Pending'?'selected':'' ?>>Pending</option>
            <option <?= $row['status']=='Planned'?'selected':'' ?>>Planned</option>
            <option <?= $row['status']=='Scheduled'?'selected':'' ?>>Scheduled</option>
            <option <?= $row['status']=='Completed'?'selected':'' ?>>Completed</option>
            <option <?= $row['status']=='Cancelled'?'selected':'' ?>>Cancelled</option>
          </select>
        </td>
        <td><?= $row['created_at'] ?></td>
        <td>
          <button class="btn small" onclick="viewRegistration(
            '<?= htmlspecialchars($row['name']) ?>',
            '<?= htmlspecialchars($row['email']) ?>',
            '<?= htmlspecialchars($row['course']) ?>',
            '<?= htmlspecialchars($row['spoken_language']) ?>',
            '<?= htmlspecialchars($row['preferred_time']) ?>',
            `<?= htmlspecialchars($row['message']) ?>`,
            '<?= $row['created_at'] ?>',
            '<?= $row['status'] ?>'
          )">View</button>
          <a href="?delete=<?= $row['id'] ?>" class="btn danger small" onclick="return confirm('Delete this registration?')">Delete</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</section>

<!-- Popup Modal -->
<div id="viewModal" class="modal-overlay" onclick="closeModal()">
  <div class="modal" onclick="event.stopPropagation()">
    <h2>Registration Details</h2>
    <p><strong>Name:</strong> <span id="modalName"></span></p>
    <p><strong>Email:</strong> <span id="modalEmail"></span></p>
    <p><strong>Course:</strong> <span id="modalCourse"></span></p>
    <p><strong>Native Language:</strong> <span id="modalSpokenLanguage"></span></p>
    <p><strong>Preferred Time:</strong> <span id="modalPreferredTime"></span></p>
    <p><strong>Message:</strong> <span id="modalMessage"></span></p>
    <p><strong>Date:</strong> <span id="modalDate"></span></p>
    <p><strong>Status:</strong> <span id="modalStatus"></span></p>
    <button class="btn" onclick="closeModal()">Close</button>
  </div>
</div>

</body>
</html>
