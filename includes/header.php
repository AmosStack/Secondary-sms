<?php
declare(strict_types=1);

// Get the current page filename
$currentPage = basename($_SERVER['PHP_SELF']);
$isNotDashboard = ($currentPage !== 'dashboard.php');

// Fetch notifications if database connection exists
$notifications = [];
$totneworder = 0;
$admin = null;

if (isset($conn)) {
    try {
        $notificationQuery = $conn->prepare('SELECT ApplicationID, Dateofapply FROM applications WHERE Status IS NULL OR Status = ""');
        $notificationQuery->execute();
        $notifications = $notificationQuery->fetchAll();
        $totneworder = count($notifications);
    } catch (Exception $e) {
        error_log("Notification query error: " . $e->getMessage());
    }

    // Get admin info from session if available
    if (isset($_SESSION['admin_id'])) {
        try {
            $adminQuery = $conn->prepare('SELECT id, name, email FROM users WHERE id = ? LIMIT 1');
            $adminQuery->execute([$_SESSION['admin_id']]);
            $admin = $adminQuery->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Admin query error: " . $e->getMessage());
        }
    }
}

$adminName = ($admin && isset($admin['name'])) ? htmlspecialchars($admin['name']) : 'Admin';
?>
<div class="content-inner-all">
    <div class="header-top-area">
        <div class="fixed-header-top">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-1 col-md-6 col-sm-6 col-xs-12">
                        <button type="button" id="sidebarCollapse" class="btn bar-button-pro header-drl-controller-btn btn-info navbar-btn">
                            <i class="fa fa-bars"></i>
                        </button>
                        <div class="admin-logo logo-wrap-pro">
                            <a href="dashboard.php"><img src="assets/img/logo.png" alt="Logo" />
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-1 col-sm-1 col-xs-12">
                        <div class="header-top-menu tabl-d-n">
                            <ul class="nav navbar-nav mai-top-nav">
                                <li class="nav-item"><a href="dashboard.php" class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">Home</a></li>
                                <li class="nav-item"><a href="view_classes.php" class="nav-link <?php echo $currentPage === 'view_classes.php' ? 'active' : ''; ?>">Classes</a></li>
                                <li class="nav-item"><a href="view_students.php" class="nav-link <?php echo $currentPage === 'view_students.php' ? 'active' : ''; ?>">Students</a></li>
                                <li class="nav-item"><a href="view_marks.php" class="nav-link <?php echo $currentPage === 'view_marks.php' ? 'active' : ''; ?>">Marks</a></li>
                                <?php if ($isNotDashboard): ?>
                                    <li class="nav-item"><a href="dashboard.php" class="nav-link btn-back-dashboard"><i class="fa fa-arrow-left"></i> Back to Dashboard</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-5 col-md-5 col-sm-6 col-xs-12">
                        <div class="header-right-info">
                            <ul class="nav navbar-nav mai-top-nav header-right-menu">
                                <!-- Notifications -->
                                <li class="nav-item"><a href="#" data-toggle="dropdown" role="button" aria-expanded="false" class="nav-link dropdown-toggle">
                                    <i class="fa fa-bell-o" aria-hidden="true"></i>
                                    <?php if ($totneworder > 0): ?>
                                        <span class="badge badge-danger"><?php echo $totneworder; ?></span>
                                    <?php endif; ?>
                                </a>
                                    <div role="menu" class="notification-author dropdown-menu animated flipInX">
                                        <div class="notification-single-top">
                                            <h1>Notifications <span class="badge badge-info"><?php echo $totneworder; ?></span></h1>
                                        </div>
                                        <ul class="notification-menu">
                                            <?php if (count($notifications) > 0): ?>
                                                <?php foreach ($notifications as $row): ?>
                                                    <li>
                                                        <a href="view_applications.php">
                                                            <div class="notification-content">
                                                                <h2><?php echo htmlspecialchars($row['ApplicationID'] ?? 'N/A'); ?></h2>
                                                                <p><?php echo htmlspecialchars($row['Dateofapply'] ?? 'N/A'); ?></p>
                                                            </div>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <li class="text-center p-3">
                                                    <p class="text-muted">No notifications</p>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                        <div class="notification-view">
                                            <a href="view_applications.php">View All</a>
                                        </div>
                                    </div>
                                </li>

                                <!-- User Dropdown -->
                                <li class="nav-item">
                                    <a href="#" data-toggle="dropdown" role="button" aria-expanded="false" class="nav-link dropdown-toggle">
                                        <span class="adminpro-icon adminpro-user-rounded header-riht-inf"></span>
                                        <span class="admin-name"><?php echo $adminName; ?></span>
                                        <span class="author-project-icon adminpro-icon adminpro-down-arrow"></span>
                                    </a>
                                    <ul role="menu" class="dropdown-header-top author-log dropdown-menu animated flipInX">
                                        <li><a href="profile.php"><span class="adminpro-icon adminpro-user-rounded author-log-ic"></span>My Profile</a></li>
                                        <li><a href="settings.php"><span class="adminpro-icon adminpro-settings author-log-ic"></span>Settings</a></li>
                                        <li><a href="logout.php"><span class="adminpro-icon adminpro-locked author-log-ic"></span>Log Out</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
