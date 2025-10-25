<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: index.php");
  exit;
}

include '../includes/db_connect.php';

// Handle payment status update
if (isset($_POST['mark_as_paid'])) {
    $id = intval($_POST['id']);
    $amount = floatval($_POST['amount'] ?? 0);
    $conn->query("UPDATE registrations SET payment_status='Paid', amount_paid='$amount' WHERE id=$id");
    header("Location: pending_payments.php?success=Payment marked as received!");
    exit;
}

if (isset($_POST['mark_as_partial'])) {
    $id = intval($_POST['id']);
    $amount = floatval($_POST['amount'] ?? 0);
    $conn->query("UPDATE registrations SET payment_status='Partial', amount_paid='$amount' WHERE id=$id");
    header("Location: pending_payments.php?success=Partial payment recorded!");
    exit;
}

// Get pending payments (Pending and Partial combined)
$result = $conn->query("SELECT * FROM registrations WHERE status='Registered' AND (payment_status='Pending' OR payment_status='Partial') ORDER BY created_at DESC");

// Statistics
$total_pending = $conn->query("SELECT COUNT(*) AS c FROM registrations WHERE status='Registered' AND payment_status='Pending'")->fetch_assoc()['c'];
$total_partial = $conn->query("SELECT COUNT(*) AS c FROM registrations WHERE status='Registered' AND payment_status='Partial'")->fetch_assoc()['c'];
$outstanding = $conn->query("SELECT SUM(total_amount - amount_paid) AS c FROM registrations WHERE status='Registered' AND (payment_status='Pending' OR payment_status='Partial')")->fetch_assoc()['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <title>Pending Payments - Payment System</title>
  <link rel="stylesheet" href="../assets/css/style.css?v=<?= time() ?>">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding: 2rem 0;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 1rem;
    }

    .header {
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      margin-bottom: 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header h1 {
      color: #1a365d;
      font-size: 2.5rem;
      margin: 0;
    }

    .header-actions {
      display: flex;
      gap: 1rem;
      align-items: center;
    }

    .btn-back {
      background: #e5e7eb;
      color: #1a365d;
      padding: 0.7rem 1.5rem;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .btn-back:hover {
      background: #d1d5db;
      transform: translateX(-2px);
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .stat-box {
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      border-left: 5px solid;
      transition: all 0.3s ease;
    }

    .stat-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }

    .stat-box.pending {
      border-left-color: #f59e0b;
    }

    .stat-box.partial {
      border-left-color: #3b82f6;
    }

    .stat-box.outstanding {
      border-left-color: #ef4444;
    }

    .stat-value {
      font-size: 2.5rem;
      font-weight: 700;
      color: #1a365d;
      margin-bottom: 0.5rem;
    }

    .stat-label {
      font-size: 0.9rem;
      color: #666;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .search-bar {
      background: white;
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      margin-bottom: 2rem;
      display: flex;
      gap: 1rem;
      align-items: center;
    }

    .search-bar input,
    .search-bar select {
      flex: 1;
      padding: 0.8rem 1.2rem;
      border: 2px solid #e5e7eb;
      border-radius: 8px;
      font-size: 1rem;
      transition: all 0.3s ease;
    }

    .search-bar input:focus,
    .search-bar select:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .payments-list {
      background: white;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      overflow: hidden;
    }

    .payment-item {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr 1fr auto;
      gap: 1.5rem;
      padding: 1.5rem 2rem;
      border-bottom: 1px solid #e5e7eb;
      align-items: center;
      transition: all 0.3s ease;
    }

    .payment-item:hover {
      background: #f9fafb;
    }

    .payment-item:last-child {
      border-bottom: none;
    }

    .payment-name {
      font-weight: 600;
      color: #1a365d;
      font-size: 1rem;
    }

    .payment-email {
      font-size: 0.85rem;
      color: #666;
    }

    .payment-course {
      color: #667eea;
      font-weight: 500;
      font-size: 0.95rem;
    }

    .payment-amount {
      font-size: 1.1rem;
      font-weight: 700;
      color: #1a365d;
    }

    .payment-status {
      display: inline-block;
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
    }

    .status-pending {
      background: #fef3c7;
      color: #92400e;
    }

    .status-partial {
      background: #dbeafe;
      color: #0c4a6e;
    }

    .payment-actions {
      display: flex;
      gap: 0.5rem;
    }

    .action-btn {
      background: #667eea;
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.85rem;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .action-btn:hover {
      background: #5568d3;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }

    .action-btn.secondary {
      background: #64748b;
    }

    .action-btn.secondary:hover {
      background: #475569;
    }

    .header-row {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr 1fr auto;
      gap: 1.5rem;
      padding: 1.5rem 2rem;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      font-weight: 600;
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-radius: 12px 12px 0 0;
    }

    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
      color: #666;
    }

    .empty-state-icon {
      font-size: 4rem;
      margin-bottom: 1rem;
    }

    .empty-state h2 {
      color: #1a365d;
      margin-bottom: 0.5rem;
    }

    .alert {
      padding: 1.5rem;
      border-radius: 12px;
      margin-bottom: 2rem;
      font-weight: 500;
    }

    .alert-success {
      background: #d1fae5;
      color: #065f46;
      border: 2px solid #6ee7b7;
    }

    .modal-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.6);
      z-index: 1000;
      justify-content: center;
      align-items: center;
    }

    .modal-overlay.show {
      display: flex;
    }

    .modal-content {
      background: white;
      padding: 2.5rem;
      border-radius: 12px;
      max-width: 400px;
      width: 90%;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }

    .modal-content h2 {
      color: #1a365d;
      margin-bottom: 1.5rem;
      font-size: 1.5rem;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: #333;
    }

    .form-group input {
      width: 100%;
      padding: 0.8rem;
      border: 2px solid #e5e7eb;
      border-radius: 6px;
      font-size: 1rem;
    }

    .form-group input:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .modal-buttons {
      display: flex;
      gap: 1rem;
      justify-content: flex-end;
      margin-top: 2rem;
    }

    .btn-modal {
      padding: 0.8rem 1.5rem;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-primary-modal {
      background: #667eea;
      color: white;
    }

    .btn-primary-modal:hover {
      background: #5568d3;
    }

    .btn-cancel-modal {
      background: #e5e7eb;
      color: #333;
    }

    .btn-cancel-modal:hover {
      background: #d1d5db;
    }

    @media (max-width: 768px) {
      .header {
        flex-direction: column;
        gap: 1.5rem;
      }

      .payment-item,
      .header-row {
        grid-template-columns: 1fr;
      }

      .payment-item {
        gap: 0.5rem;
      }

      .header-row {
        display: none;
      }

      .payment-item {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        margin-bottom: 1rem;
      }

      .payment-item::before {
        content: '';
        display: block;
      }
    }

    .print-options {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .print-option {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .print-option input[type="radio"] {
      cursor: pointer;
      width: 20px;
      height: 20px;
    }

    .print-option label {
      cursor: pointer;
      flex: 1;
      margin: 0;
      font-weight: 500;
      color: #333;
    }

    .btn-print {
      background: #10b981;
      color: white;
      padding: 0.7rem 1.5rem;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .btn-print:hover {
      background: #059669;
      transform: translateY(-2px);
    }

    @media print {
      body {
        background: white;
        padding: 0;
      }

      .container {
        max-width: 100%;
      }

      .header,
      .search-bar,
      .header-actions,
      .btn-back,
      .btn-print,
      .empty-state {
        display: none !important;
      }

      .stats-grid {
        margin-bottom: 1rem;
      }

      .stat-box {
        break-inside: avoid;
        page-break-inside: avoid;
      }

      .payments-list {
        box-shadow: none;
        border: 1px solid #ccc;
      }

      .payment-item {
        page-break-inside: avoid;
        break-inside: avoid;
      }

      .payment-item:hover {
        background: white !important;
      }

      .header-row {
        background: #667eea !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        color: white !important;
      }
    }
  </style>
  <script>
    let currentPaymentId = null;
    let currentAmount = 0;

    function openPaymentModal(id, name, email, course, totalAmount, amountPaid, status) {
      currentPaymentId = id;
      currentAmount = totalAmount - amountPaid;
      document.getElementById('paymentModal').classList.add('show');
      document.getElementById('modalStudentName').textContent = name + ' (' + email + ')';
      document.getElementById('modalCourse').textContent = course;
      document.getElementById('modalDue').textContent = '€' + parseFloat(currentAmount).toFixed(2);
      document.getElementById('paymentAmount').value = parseFloat(currentAmount).toFixed(2);
      document.getElementById('paymentAmount').max = parseFloat(currentAmount).toFixed(2);
    }

    function closePaymentModal() {
      document.getElementById('paymentModal').classList.remove('show');
    }

    function submitPaymentFull() {
      const form = document.createElement('form');
      form.method = 'POST';
      form.innerHTML = `
        <input type="hidden" name="mark_as_paid" value="1">
        <input type="hidden" name="id" value="${currentPaymentId}">
        <input type="hidden" name="amount" value="${document.getElementById('paymentAmount').value}">
      `;
      document.body.appendChild(form);
      form.submit();
    }

    function submitPaymentPartial() {
      const amount = prompt('Enter partial payment amount (€):');
      if (amount && !isNaN(amount) && parseFloat(amount) > 0) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
          <input type="hidden" name="mark_as_partial" value="1">
          <input type="hidden" name="id" value="${currentPaymentId}">
          <input type="hidden" name="amount" value="${parseFloat(amount).toFixed(2)}">
        `;
        document.body.appendChild(form);
        form.submit();
      }
    }

    function searchPayments() {
      const query = document.getElementById('searchInput').value.toLowerCase();
      document.querySelectorAll('.payment-item').forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(query) ? '' : 'none';
      });
    }

    function filterByStatus(status) {
      document.querySelectorAll('.payment-item').forEach(item => {
        if (status === 'All' || item.getAttribute('data-status') === status) {
          item.style.display = '';
        } else {
          item.style.display = 'none';
        }
      });
    }

    function openPrintOptionsModal() {
      document.getElementById('printOptionsModal').style.display = 'flex';
    }

    function closePrintOptionsModal() {
      document.getElementById('printOptionsModal').style.display = 'none';
    }
  </script>
</head>
<body>

<div class="container">
  <div class="header">
    <h1>💳 Pending Payments</h1>
    <div class="header-actions">
      <a href="registered_students.php" class="btn-back">← Back to Students</a>
      <button class="btn-print" onclick="openPrintOptionsModal()">Print</button>
    </div>
  </div>

  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($_GET['success']) ?></div>
  <?php endif; ?>

  <div class="stats-grid">
    <div class="stat-box pending">
      <div class="stat-value"><?= $total_pending ?></div>
      <div class="stat-label">Unpaid Students</div>
    </div>
    <div class="stat-box partial">
      <div class="stat-value"><?= $total_partial ?></div>
      <div class="stat-label">Partial Payments</div>
    </div>
    <div class="stat-box outstanding">
      <div class="stat-value">€<?= number_format($outstanding, 2) ?></div>
      <div class="stat-label">Outstanding Amount</div>
    </div>
  </div>

  <div class="search-bar">
    <input type="text" id="searchInput" placeholder="🔍 Search by name or email..." onkeyup="searchPayments()" style="flex: 2;">
    <select onchange="filterByStatus(this.value)" style="flex: 1;">
      <option value="All">All Status</option>
      <option value="Pending">Pending Only</option>
      <option value="Partial">Partial Only</option>
    </select>
  </div>

  <div class="payments-list">
    <?php $count = 0; while($row = $result->fetch_assoc()): $count++; ?>
      <div class="payment-item" data-status="<?= $row['payment_status'] ?>">
        <div>
          <div class="payment-name"><?= htmlspecialchars($row['name']) ?></div>
          <div class="payment-email"><?= htmlspecialchars($row['email']) ?></div>
        </div>
        <div class="payment-course"><?= htmlspecialchars($row['course']) ?></div>
        <div class="payment-amount" style="color: #667eea; font-weight: 600;">📚 <?= intval($row['total_lessons'] ?? 0) ?></div>
        <div class="payment-amount">€<?= number_format($row['total_amount'] ?? 0, 2) ?></div>
        <div class="payment-amount" style="color: #667eea;">€<?= number_format($row['amount_paid'] ?? 0, 2) ?></div>
        <div class="payment-amount" style="color: #ef4444;">€<?= number_format(($row['total_amount'] - $row['amount_paid']) ?? 0, 2) ?></div>
        <div>
          <span class="payment-status <?= ($row['payment_status'] === 'Partial' ? 'status-partial' : 'status-pending') ?>">
            <?= htmlspecialchars($row['payment_status'] ?? 'Pending') ?>
          </span>
        </div>
        <div class="payment-actions">
          <button class="action-btn" onclick="openPaymentModal(
            <?= $row['id'] ?>,
            '<?= htmlspecialchars(addslashes($row['name'])) ?>',
            '<?= htmlspecialchars(addslashes($row['email'])) ?>',
            '<?= htmlspecialchars(addslashes($row['course'])) ?>',
            <?= $row['total_amount'] ?? 0 ?>,
            <?= $row['amount_paid'] ?? 0 ?>,
            '<?= $row['payment_status'] ?>'
          )">Record Payment</button>
        </div>
      </div>
    <?php endwhile; ?>

    <?php if ($count === 0): ?>
      <div class="empty-state">
        <div class="empty-state-icon">✨</div>
        <h2>All Payments Collected!</h2>
        <p>There are no pending payments at the moment.</p>
      </div>
    <?php endif; ?>
  </div>

  <?php if ($count > 0): ?>
    <div style="text-align: center; margin-top: 2rem; color: white;">
      <p>Showing <?= $count ?> student(s) with pending payments</p>
    </div>
  <?php endif; ?>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="modal-overlay" onclick="if(event.target === this) closePaymentModal()">
  <div class="modal-content">
    <h2>Record Payment</h2>
    <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
      <p style="margin: 0 0 0.5rem 0; color: #666;"><strong>Student:</strong> <span id="modalStudentName"></span></p>
      <p style="margin: 0 0 0.5rem 0; color: #666;"><strong>Course:</strong> <span id="modalCourse"></span></p>
      <p style="margin: 0; color: #ef4444; font-size: 1.2rem;"><strong>Amount Due:</strong> <span id="modalDue"></span></p>
    </div>

    <div class="form-group">
      <label>Payment Amount (€)</label>
      <input type="number" id="paymentAmount" step="0.01" min="0">
    </div>

    <div class="modal-buttons">
      <button class="btn-modal btn-cancel-modal" onclick="closePaymentModal()">Cancel</button>
      <button class="btn-modal btn-primary-modal" onclick="submitPaymentFull()">Mark as Paid</button>
      <button class="btn-modal secondary action-btn" onclick="submitPaymentPartial()" style="border: none; padding: 0.8rem 1.5rem;">Record Partial</button>
    </div>
  </div>
</div>

<!-- Print Options Modal -->
<div id="printOptionsModal" class="modal-overlay" style="display: none;" onclick="if(event.target === this) closePrintOptionsModal()">
  <div class="modal-content">
    <h2>Select Print Options</h2>
    <form method="POST" action="print_pending_payments.php">
      <div class="print-options">
        <div class="print-option">
          <input type="radio" id="printAll" name="print_type" value="All" checked>
          <label for="printAll">All Pending and Partial Payments</label>
        </div>
        <div class="print-option">
          <input type="radio" id="printPending" name="print_type" value="Pending">
          <label for="printPending">Only Pending Payments</label>
        </div>
        <div class="print-option">
          <input type="radio" id="printPartial" name="print_type" value="Partial">
          <label for="printPartial">Only Partial Payments</label>
        </div>
      </div>
      <div class="modal-buttons">
        <button type="button" class="btn-modal btn-cancel-modal" onclick="closePrintOptionsModal()">Cancel</button>
        <button type="submit" class="btn-modal btn-primary-modal">Print</button>
      </div>
    </form>
  </div>
</div>

</body>
</html>
