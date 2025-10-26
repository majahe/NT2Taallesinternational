<?php
if (!isset($_SESSION['student_id'])) {
    return;
}
?>
<header class="student-header">
    <nav class="student-navbar">
        <div class="navbar-brand">
            <a href="/student/dashboard/dashboard.php">NT2 Taalles International</a>
        </div>
        <div class="navbar-menu">
            <a href="/student/dashboard/dashboard.php" class="nav-link">Dashboard</a>
            <a href="/student/dashboard/my_courses.php" class="nav-link">My Courses</a>
            <a href="/student/progress/my_progress.php" class="nav-link">Progress</a>
            <div class="nav-user">
                <span><?= htmlspecialchars($_SESSION['student_name']) ?></span>
                <a href="/student/auth/logout.php" class="nav-link logout">Logout</a>
            </div>
        </div>
    </nav>
</header>

