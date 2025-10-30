<?php
require_once __DIR__ . '/../../includes/admin_auth.php';
require_admin_auth();

include '../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/database/QueryBuilder.php';
$db = new QueryBuilder($conn);

// Get the print type from the form
$print_type = isset($_POST['print_type']) ? $_POST['print_type'] : 'All';

// Build the query based on print type
if ($print_type === 'Pending') {
    $query = "SELECT * FROM registrations WHERE status='Registered' AND payment_status='Pending' ORDER BY created_at DESC";
    $title = "Pending Payments";
} elseif ($print_type === 'Partial') {
    $query = "SELECT * FROM registrations WHERE status='Registered' AND payment_status='Partial' ORDER BY created_at DESC";
    $title = "Partial Payments";
} else {
    $query = "SELECT * FROM registrations WHERE status='Registered' AND (payment_status='Pending' OR payment_status='Partial') ORDER BY created_at DESC";
    $title = "All Pending and Partial Payments";
}

$result = $conn->query($query);

// Get statistics
if ($print_type === 'Pending') {
    $total_count = $conn->query("SELECT COUNT(*) AS c FROM registrations WHERE status='Registered' AND payment_status='Pending'")->fetch_assoc()['c'];
    $total_outstanding = $conn->query("SELECT SUM(total_amount - amount_paid) AS c FROM registrations WHERE status='Registered' AND payment_status='Pending'")->fetch_assoc()['c'] ?? 0;
} elseif ($print_type === 'Partial') {
    $total_count = $conn->query("SELECT COUNT(*) AS c FROM registrations WHERE status='Registered' AND payment_status='Partial'")->fetch_assoc()['c'];
    $total_outstanding = $conn->query("SELECT SUM(total_amount - amount_paid) AS c FROM registrations WHERE status='Registered' AND payment_status='Partial'")->fetch_assoc()['c'] ?? 0;
} else {
    $total_count = $conn->query("SELECT COUNT(*) AS c FROM registrations WHERE status='Registered' AND (payment_status='Pending' OR payment_status='Partial')")->fetch_assoc()['c'];
    $total_outstanding = $conn->query("SELECT SUM(total_amount - amount_paid) AS c FROM registrations WHERE status='Registered' AND (payment_status='Pending' OR payment_status='Partial')")->fetch_assoc()['c'] ?? 0;
}

$printed_date = date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?> - Print</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      padding: 20px;
      color: #333;
    }

    .action-header {
      display: flex;
      gap: 1rem;
      margin-bottom: 30px;
      justify-content: center;
    }

    .btn-action {
      padding: 0.75rem 2rem;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      font-size: 1rem;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }

    .btn-print-action {
      background: #10b981;
      color: white;
    }

    .btn-print-action:hover {
      background: #059669;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
    }

    .btn-back-action {
      background: #6b7280;
      color: white;
    }

    .btn-back-action:hover {
      background: #4b5563;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(107, 114, 128, 0.3);
    }

    .print-header {
      text-align: center;
      margin-bottom: 30px;
      border-bottom: 3px solid #667eea;
      padding-bottom: 20px;
    }

    .print-header h1 {
      color: #1a365d;
      font-size: 28px;
      margin-bottom: 10px;
    }

    .print-header p {
      color: #666;
      font-size: 14px;
      margin: 5px 0;
    }

    .print-info {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-bottom: 30px;
    }

    .info-box {
      background: #f9fafb;
      padding: 15px;
      border-left: 4px solid #667eea;
      border-radius: 4px;
    }

    .info-label {
      font-size: 12px;
      color: #666;
      text-transform: uppercase;
      font-weight: 600;
      margin-bottom: 5px;
    }

    .info-value {
      font-size: 20px;
      color: #1a365d;
      font-weight: 700;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    thead {
      background: #667eea;
      color: white;
    }

    th {
      padding: 12px;
      text-align: left;
      font-weight: 600;
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-right: 1px solid rgba(255,255,255,0.2);
    }

    th:last-child {
      border-right: none;
    }

    td {
      padding: 12px;
      border-bottom: 1px solid #e5e7eb;
      font-size: 13px;
    }

    tbody tr:last-child td {
      border-bottom: none;
    }

    tbody tr:nth-child(even) {
      background: #f9fafb;
    }

    .status-pending {
      display: inline-block;
      background: #fef3c7;
      color: #92400e;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 600;
    }

    .status-partial {
      display: inline-block;
      background: #dbeafe;
      color: #0c4a6e;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 600;
    }

    .amount-due {
      color: #ef4444;
      font-weight: 600;
    }

    .footer {
      text-align: center;
      margin-top: 40px;
      padding-top: 20px;
      border-top: 1px solid #e5e7eb;
      font-size: 12px;
      color: #666;
    }

    .no-data {
      text-align: center;
      padding: 40px 20px;
      color: #666;
    }

    @media print {
      body {
        padding: 0;
      }

      .action-header {
        display: none !important;
      }

      .print-header {
        page-break-after: avoid;
      }

      table {
        page-break-inside: avoid;
      }

      tbody tr {
        page-break-inside: avoid;
      }
    }
  </style>
</head>
<body>

<div class="action-header">
  <button class="btn-action btn-print-action" onclick="window.print()">🖨️ Print</button>
  <button class="btn-action btn-back-action" onclick="window.history.back()">← Go Back</button>
</div>

<div class="print-header">
  <h1><?= htmlspecialchars($title) ?></h1>
  <p>Generated on: <?= $printed_date ?></p>
  <p>Printed by: <?= htmlspecialchars($_SESSION['admin'] ?? 'Admin') ?></p>
</div>

<div class="print-info">
  <div class="info-box">
    <div class="info-label">Total Records</div>
    <div class="info-value"><?= $total_count ?></div>
  </div>
  <div class="info-box">
    <div class="info-label">Outstanding Amount</div>
    <div class="info-value">€<?= number_format($total_outstanding, 2) ?></div>
  </div>
  <div class="info-box">
    <div class="info-label">Print Type</div>
    <div class="info-value"><?= htmlspecialchars($print_type) ?></div>
  </div>
</div>

<?php if ($result->num_rows > 0): ?>
<table>
  <thead>
    <tr>
      <th>Student Name</th>
      <th>Email</th>
      <th>Course</th>
      <th>Total Amount</th>
      <th>Amount Paid</th>
      <th>Amount Due</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
      <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
      <td><?= htmlspecialchars($row['email']) ?></td>
      <td><?= htmlspecialchars($row['course']) ?></td>
      <td>€<?= number_format($row['total_amount'] ?? 0, 2) ?></td>
      <td>€<?= number_format($row['amount_paid'] ?? 0, 2) ?></td>
      <td class="amount-due">€<?= number_format(($row['total_amount'] - $row['amount_paid']) ?? 0, 2) ?></td>
      <td>
        <span class="status-<?= strtolower($row['payment_status']) ?>">
          <?= htmlspecialchars($row['payment_status']) ?>
        </span>
      </td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>
<?php else: ?>
<div class="no-data">
  <p>No records found for printing.</p>
</div>
<?php endif; ?>

<div class="footer">
  <p>This is a confidential document. Please handle with care.</p>
  <p>For more information, contact the administration.</p>
</div>

</body>
</html>
