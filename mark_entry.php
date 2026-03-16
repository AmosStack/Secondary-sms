<?php include('includes/header.php'); include('includes/db.php'); ?>

<h4 class="mt-3">Mark Entry Portal</h4>

<form id="markForm" class="mt-4">
  <div class="row">
    <div class="col-md-4">
      <label>Select Class:</label>
      <select id="class_id" name="class_id" class="form-control" required>
        <option value="">--Select Class--</option>
        <?php
        $classes = $conn->query("SELECT * FROM classes");
        while($row = $classes->fetch_assoc()){
          echo "<option value='{$row['class_id']}'>" . htmlspecialchars($row['class_level'] . ' - ' . $row['stream']) . "</option>";
        }
        ?>
      </select>
    </div>

    <div class="col-md-4">
      <label>Select Stream:</label>
      <select id="stream" name="stream" class="form-control" required>
        <option value="">--Select Stream--</option>
      </select>
    </div>

    <div class="col-md-4">
      <label>Select Subject:</label>
      <select id="subject" name="subject" class="form-control" required>
        <option value="">--Select Subject--</option>
      </select>
    </div>
  </div>

  <div class="mt-4 d-flex gap-3">
    <a href="#" id="manualEntry" class="btn btn-primary">For Manual</a>
    <a href="#" id="bulkEntry" class="btn btn-success">For Bulk Upload</a>
  </div>
</form>

<div id="resultSection" class="mt-4"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#class_id').on('change', function() {
  const classId = $(this).val();
  if (classId) {
    // Get stream from same class row
    $.post('endpoints/get_stream_subjects.php', { class_id: classId }, function(data) {
      const res = JSON.parse(data);
      $('#stream').html(`<option value="${res.stream}">${res.stream}</option>`);
      $('#subject').html(res.subjects.map(s => `<option value="${s.subject_id}">${s.name}</option>`));
    });
  }
});

$('#manualEntry').on('click', function(e) {
  e.preventDefault();
  const data = $('#markForm').serialize();
  $.get('manual_entry.php', data, function(html) {
    $('#resultSection').html(html);
  });
});

$('#bulkEntry').on('click', function(e) {
  e.preventDefault();
  const data = $('#markForm').serialize();
  $.get('bulk_upload.php', data, function(html) {
    $('#resultSection').html(html);
  });
});
</script>

<?php include('includes/footer.php'); ?>
