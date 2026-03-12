<?php
session_start();
error_reporting(0);
include('includes/db.php');

if (!isset($_SESSION['admin_id'])) {
    header('location:logout.php');
    exit();
}
?>

<div class="left-sidebar-pro">
    <nav id="sidebar">
        <div class="sidebar-header text-center">
            <a href="#"><img src="img/avatar.png" alt="Admin" class="img-fluid rounded-circle" width="80" /></a>
            <?php
            $admin_id = $_SESSION['admin_id'];
            $stmt = $conn->prepare("SELECT name, email FROM admins WHERE id = ?");
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
            $stmt->bind_result($name, $email);
            if ($stmt->fetch()):
            ?>
                <h4 class="mt-2"><?php echo htmlspecialchars($name); ?></h4>
                <small class="text-muted"><?php echo htmlspecialchars($email); ?></small>
            <?php endif; $stmt->close(); ?>
        </div>

        <div class="left-custom-menu-adp-wrap mt-4">
            <ul class="nav navbar-nav left-sidebar-menu-pro">
                <li class="nav-item">
                    <a href="dashboard.php"><i class="fa fa-home"></i> <span class="mini-dn">Dashboard</span></a>
                </li>

                <li class="nav-item">
                    <a href="students.php"><i class="fa fa-user-graduate"></i> <span class="mini-dn">Manage Students</span></a>
                </li>

                <li class="nav-item">
                    <a href="classes.php"><i class="fa fa-layer-group"></i> <span class="mini-dn">Manage Classes</span></a>
                </li>

                <li class="nav-item">
                    <a href="enter_marks.php"><i class="fa fa-pen-nib"></i> <span class="mini-dn">Enter Marks</span></a>
                </li>

                <li class="nav-item">
                    <a href="view_reports.php"><i class="fa fa-file-alt"></i> <span class="mini-dn">Reports</span></a>
                </li>

                <li class="nav-item">
                    <a href="logout.php"><i class="fa fa-sign-out-alt"></i> <span class="mini-dn">Logout</span></a>
                </li>
            </ul>
        </div>
    </nav>
</div>
