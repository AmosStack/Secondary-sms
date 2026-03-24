<?php
// index.php - Mafiga School System Landing Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Welcome to School Management System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap & Fonts -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Segoe UI', sans-serif;
    }
    .hero {
      background: linear-gradient(to right, #161f5e, #140492);
      color: white;
      padding: 80px 20px;
      text-align: center;
      border-radius: 0 0 30px 30px;
    }
    .hero h1 {
      font-size: 3rem;
      margin-bottom: 20px;
    }
    .hero p {
      font-size: 1.2rem;
    }
    .btn-custom {
      padding: 10px 30px;
      font-size: 1rem;
      margin: 10px;
    }
    footer {
      background-color: #343a40;
      color: white;
      text-align: center;
      padding: 15px;
      margin-top: 50px;
    }
  </style>
</head>
<body>

  <!-- Hero Section -->
  <section class="hero">
    <div class="container">
      <h1>Welcome to  School System</h1>
      <p>Efficient and Secure School Result Management</p>
      <a href="login.php" class="btn btn-outline-light btn-custom"><i class="fas fa-user-plus"></i> Get Started</a>
    </div>
  </section>

  <!-- Features -->
  <div class="container my-5">
    <div class="row text-center">
      <div class="col-md-4">
        <i class="fas fa-chalkboard-teacher fa-3x text-primary mb-3"></i>
        <h5>Staff Dashboard</h5>
        <p>Manage classes, students, results and performance with ease.</p>
      </div>
      <div class="col-md-4">
        <i class="fas fa-user-graduate fa-3x text-success mb-3"></i>
        <h5>Student Portal</h5>
        <p>Students can check results, progress, and performance history.</p>
      </div>
      <div class="col-md-4">
        <i class="fas fa-chart-line fa-3x text-info mb-3"></i>
        <h5>Reports & Analytics</h5>
        <p>Generate printable reports, visual graphs, and track trends.</p>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer>
    <p>&copy; <?= date('Y') ?> School Management System. All Rights Reserved.</p>
  </footer>

</body>
</html>
