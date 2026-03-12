<?php
// Simulated student data
$student = [
    'name' => 'ISAYA JAMES JUMBE',
    'reg_no' => '0304',
    'class' => 'Kidato Cha III',
    'term' => 'Muhula wa I',
    'year' => 2025,
    'position' => 358,
    'total_students' => 409
];

// Simulated subject performance
$subjects = [
    ['name' => 'URAIA', 'test' => 4, 'exam' => 5],
    ['name' => 'HISTORIA', 'test' => 8, 'exam' => 4],
    ['name' => 'GEOGRAFIA', 'test' => 7, 'exam' => 6],
    ['name' => 'KISWAHILI', 'test' => 9, 'exam' => 5],
    ['name' => 'KIINGEREZA', 'test' => 5, 'exam' => 4],
    ['name' => 'FIZIKIA', 'test' => 3, 'exam' => 2],
    ['name' => 'BIOLOJIA', 'test' => 6, 'exam' => 3],
    ['name' => 'HISABATI', 'test' => 4, 'exam' => 5],
    ['name' => 'BIASHARA', 'test' => 0, 'exam' => 0], // not taken
];

function getGrade($avg) {
    if ($avg >= 75) return ['A', 'UFAULU MZURI'];
    if ($avg >= 65) return ['B', 'VIZURI'];
    if ($avg >= 45) return ['C', 'WASTANI'];
    if ($avg >= 30) return ['D', 'HAFIFU'];
    return ['F', 'AMEFELI'];
}

function getDivisionF1toF4($points) {
    if ($points >= 7 && $points <= 17) return 1;
    if ($points <= 21) return 2;
    if ($points <= 25) return 3;
    if ($points <= 33) return 4;
    return 0;
}

function simulateBehavior($average) {
    if ($average >= 65) return ['A', 'B', 'A', 'B', 'A'];
    if ($average >= 45) return ['B', 'C', 'B', 'C', 'B'];
    return ['C', 'D', 'D', 'C', 'D'];
}

function getFinalComments($average) {
    if ($average >= 75) {
        return [
            'teacher' => 'Hongera sana kwa kupata matokeo bora. Endelea hivyo!',
            'headteacher' => 'Mwanafunzi ameonyesha ufanisi mkubwa. Ameshinda changamoto zote.'
        ];
    } elseif ($average >= 45) {
        return [
            'teacher' => 'Endelea kujaribu zaidi na kuboresha matokeo yako.',
            'headteacher' => 'Mwanafunzi anaweza kufaulu kwa bidii zaidi.'
        ];
    } else {
        return [
            'teacher' => 'Mtupe bidii zaidi ili kufikia malengo yako.',
            'headteacher' => 'Mwanafunzi anahitaji msaada wa ziada na juhudi kubwa.'
        ];
    }
}

// Calculate totals and averages
$totalMarks = 0;
$totalSubjects = 0;

foreach ($subjects as $s) {
    $sum = $s['test'] + $s['exam'];
    if ($sum == 0) continue;
    $totalMarks += $sum;
    $totalSubjects++;
}

$average = $totalSubjects ? round($totalMarks / $totalSubjects, 2) : 0;
[$finalGrade, $finalRemark] = getGrade($average);

$points = 36; // simulated points
$division = getDivisionF1toF4($points);

$behavior = simulateBehavior($average);
$finalComments = getFinalComments($average);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Ripoti ya Mwanafunzi</title>
<style>
  body {
    font-family: "Times New Roman", serif;
    background: #fff;
    margin: 20px auto;
    padding: 0;
    max-width: 800px;
    color: #000;
  }

  #report {
    padding: 10px 30px 30px 30px;
    border: 1px solid #333;
    background: #fff;
  }

  h3 {
    text-align: center;
    margin-bottom: 15px;
    text-transform: uppercase;
    letter-spacing: 1px;
  }
  h4 {
    text-align: center;
    margin-bottom: 15px;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  /* Top summary: 3 columns */
  .top-summary {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    font-weight: bold;
    font-size: 16px;
    margin-bottom: 25px;
  }

  /* Academic table */
  table {
    border-collapse: collapse;
    width: 100%;
    margin: 20px 0 30px 0;
    font-size: 16px;
  }

  th, td {
    border: 1px solid #333;
    padding: 8px;
    text-align: center;
  }

  th {
    background-color: #ddd;
    font-weight: bold;
  }

  /* Summary section with 3 columns */
  .summary {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    font-weight: bold;
    font-size: 16px;
    margin-bottom: 20px;
  }
  /* Last line spans all columns */
  .summary .full-span {
    grid-column: span 3;
  }

  /* Behavior table */
  .behavior-table {
    margin-bottom: 30px;
  }

  /* Buttons */
  .buttons {
    text-align: center;
    margin: 20px 0 40px 0;
  }

  button {
    font-size: 16px;
    padding: 10px 20px;
    margin: 0 15px;
    cursor: pointer;
    border: none;
    border-radius: 4px;
    background-color: #007bff;
    color: white;
    transition: background-color 0.3s;
  }

  button:hover {
    background-color: #0056b3;
  }

  /* Print styles */
  @media print {
    body, #report {
      margin: 0;
      padding: 0;
      box-shadow: none;
      max-width: none;
      width: 100%;
    }
    .buttons {
      display: none;
    }
  }

  /* Final comments section */
  .final-comments > div {
    margin-bottom: 40px;
  }
  .final-comments .comment-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .final-comments .comment-text {
    max-width: 70%;
    font-weight: bold;
  }
  .final-comments .signature {
    border-top: 1px solid #000;
    width: 25%;
    font-size: 14px;
    padding-top: 5px;
    text-align: center;
  }
  .final-comments .headteacher-right {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-left: 20px;
  }
  .final-comments .mold-box {
    border: 2px solid #000;
    width: 100px;
    height: 60px;
    margin-top: 10px;
    font-weight: bold;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .final-comments .parent-comments-box {
    border: 1px solid #333;
    height: 80px;
    margin-top: 8px;
    padding: 5px;
    font-style: italic;
  }
</style>
</head>
<body>

<div id="report">
  <h3>OFISI YA RAISI</h3>
  <h3>TAWALA ZA MIKOA NA SERIKARI ZA MITAA</h3>
  <h3>SHULE YA SEKONDARI MAFIGA</h3>
  <h3>RIPORT YA MAENDELEO YA MWANAFUNZI KWA MZAZI/MLEZI</h3>

  <h4>TAARIFA YA MAENDELEO YA KITAALUMA</h4>

  <!-- Top summary with 3 columns -->
  <div class="top-summary">
    <div><strong>Jina:</strong> <?php echo htmlspecialchars($student['name']); ?></div>
    <div><strong>Kidato:</strong> <?php echo htmlspecialchars($student['class']); ?></div>
    <div><strong>Muhula:</strong> <?php echo htmlspecialchars($student['term']) . " " . htmlspecialchars($student['year']); ?></div>
  </div>

  <!-- Academic performance table -->
  <table>
    <tr>
      <th>SOMO</th>
      <th>MAZOEZI</th>
      <th>MTIHANI</th>
      <th>JUMLA</th>
      <th>WASTANI</th>
      <th>DARAJA</th>
      <th>MAONI</th>
    </tr>
    <?php
      foreach ($subjects as $s) {
          $sum = $s['test'] + $s['exam'];
          if ($sum == 0) {
              echo "<tr><td>{$s['name']}</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td></tr>";
              continue;
          }

          $avg = $sum;
          [$grade, $remark] = getGrade($avg);

          echo "<tr>
              <td>{$s['name']}</td>
              <td>{$s['test']}</td>
              <td>{$s['exam']}</td>
              <td>{$sum}</td>
              <td>{$avg}</td>
              <td>{$grade}</td>
              <td>{$remark}</td>
          </tr>";
      }
    ?>
  </table>

  <!-- Summary section with 3 rows in columns -->
  <div class="summary">
    <div><strong>JUMLA YA ALAMA:</strong> <?php echo $totalMarks; ?></div>
    <div><strong>WASTANI:</strong> <?php echo $average; ?></div>
    <div><strong>DARAJA:</strong> <?php echo $finalGrade; ?></div>
    <div><strong>POINTS:</strong> <?php echo $points; ?></div>
    <div><strong>DIVISION:</strong> <?php echo $division; ?></div>
    <div><strong>MAONI YA JUMLA:</strong> <?php echo $finalRemark; ?></div>
    <div class="full-span"><strong>AMEKUWA MWANAFUNZI WA:</strong> <?php echo $student['position'] . " KATI YA " . $student['total_students']; ?></div>
  </div>

  <!-- Behavior table -->
  <h4>TAARIFA YA TABIA YA MWANAFUNZI</h4>
  <table class="behavior-table">
    <tr>
      <th>KUTIMIZA MAJUKUMU</th>
      <th>USHIRIKIANO</th>
      <th>MICHEZO</th>
      <th>HESHIMA</th>
      <th>USAFI</th>
    </tr>
    <tr>
      <td><?php echo $behavior[0]; ?></td>
      <td><?php echo $behavior[1]; ?></td>
      <td><?php echo $behavior[2]; ?></td>
      <td><?php echo $behavior[3]; ?></td>
      <td><?php echo $behavior[4]; ?></td>
    </tr>
  </table>

  <!-- Final comments section -->
  <div class="final-comments">

    <!-- Class Teacher Comment -->
    <div class="comment-flex">
      <div class="comment-text">
        <p><strong>Mwalimu wa Darasa:</strong></p>
        <p style="font-weight: normal; margin-top: 5px;"><?php echo htmlspecialchars($finalComments['teacher']); ?></p>
      </div>
      <div class="signature">
        Sahihi ya Mwalimu wa Darasa
      </div>
    </div>

    <!-- Head Teacher Comment -->
    <div class="comment-flex">
      <div class="comment-text">
        <p><strong>Mkuu wa Shule:</strong></p>
        <p style="font-weight: normal; margin-top: 5px;"><?php echo htmlspecialchars($finalComments['headteacher']); ?></p>
      </div>
      <div class="headteacher-right">
        <div class="signature" style="width: 100%; border-top: 1px solid #000; padding-top: 5px;">Sahihi ya Mkuu wa Shule</div>
        <div class="mold-box">MOLD</div>
      </div>
    </div>

    <!-- Parent Comments Box -->
    <div>
      <strong>Maoni ya Mzazi/Walimu wa Nyumba:</strong>
      <div class="parent-comments-box"></div>
    </div>

  </div>

</div>

<!-- Buttons for print and PDF download -->
<div class="buttons">
  <button onclick="printReport()">Print Report</button>
  <button id="downloadPdfBtn">Download PDF</button>
</div>

<!-- jsPDF and html2canvas for PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
  function printReport() {
    const printContents = document.getElementById('report').innerHTML;
    const originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload();
  }

  document.getElementById('downloadPdfBtn').addEventListener('click', () => {
    const { jsPDF } = window.jspdf;
    const report = document.getElementById('report');

    html2canvas(report, { scale: 2 }).then(canvas => {
      const imgData = canvas.toDataURL('image/png');
      const pdf = new jsPDF('p', 'mm', 'a4');
      const imgProps = pdf.getImageProperties(imgData);
      const pdfWidth = pdf.internal.pageSize.getWidth();
      const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

      pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
      pdf.save('ripoti_ya_mwanafunzi.pdf');
    });
  });
</script>

</body>
</html>
