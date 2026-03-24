<?php
require_once 'tcpdf/tcpdf.php';  // adjust path to tcpdf.php
include 'includes/db.php';  // your DB connection
require_once 'endpoints/division_calculation.php';

if (!isset($_GET['student_id'])) {
    die("Student ID missing.");
}

$student_id = intval($_GET['student_id']);

// Fetch student details
$stmt = $conn->prepare("
    SELECT s.full_name, c.class_level, c.stream 
    FROM students s 
    JOIN classes c ON s.class_id = c.class_id 
    WHERE s.student_id = ?
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    die("Student not found.");
}

// Static or dynamic term/year
$term = "Muhula wa I";
$year = date("Y");

// Fetch subjects and marks
$stmt = $conn->prepare("
    SELECT sub.name as subject_name, m.marks
    FROM marks m
    JOIN subjects sub ON m.subject_id = sub.subject_id
    WHERE m.student_id = ?
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$results = $stmt->get_result();

$subjects = [];
while ($row = $results->fetch_assoc()) {
    $subjects[] = [
        'name' => $row['subject_name'],
        'marks' => (int)$row['marks'],
    ];
}
$stmt->close();

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

// Calculate totals, average
$totalMarks = 0;
$totalSubjects = 0;
foreach ($subjects as $s) {
    if ($s['marks'] == 0) continue;
    $totalMarks += $s['marks'];
    $totalSubjects++;
}
$average = $totalSubjects ? round($totalMarks / $totalSubjects, 2) : 0;
$form = $student['class_level'];
[$finalGrade, $finalRemark] = getGradeAndCommentByForm($form, $average, 'sw');

$subjectAverages = [];
foreach ($subjects as $s) {
    if ((int)$s['marks'] > 0) {
        $subjectAverages[$s['name']] = (float)$s['marks'];
    }
}
$divisionResult = calculateDivisionResult($form, $subjectAverages);
$points = $divisionResult['valid'] ? $divisionResult['total_points'] : 0;
$division = $divisionResult['division'];
$behavior = simulateBehavior($average);
$finalComments = getFinalComments($average);

// Generate PDF with TCPDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Mafiga SMS');
$pdf->SetAuthor('Mafiga School');
$pdf->SetTitle('Ripoti ya Mwanafunzi - ' . $student['full_name']);
$pdf->SetMargins(15, 20, 15);
$pdf->AddPage();

$html = <<<EOD
<style>
    body { font-family: "times", serif; }
    h3, h4 { text-align: center; text-transform: uppercase; margin-bottom: 8px; }
    table { border-collapse: collapse; width: 100%; margin: 15px 0; font-size: 12pt; }
    th, td { border: 1px solid #333; padding: 5px; text-align: center; }
    th { background-color: #eee; }
    .summary { margin-top: 20px; font-weight: bold; }
    .summary div { margin-bottom: 6px; }
    .behavior-table { margin-top: 20px; }
    .comments { margin-top: 40px; }
    .comment-block { margin-bottom: 30px; }
    .signature { border-top: 1px solid #000; width: 200px; margin-top: 5px; text-align: center; font-size: 10pt; }
</style>

<h3>OFISI YA RAISI</h3>
<h3>TAWALA ZA MIKOA NA SERIKARI ZA MITAA</h3>
<h3>SHULE YA SEKONDARI MAFIGA</h3>
<h3>RIPOTI YA MAENDELEO YA MWANAFUNZI KWA MZAZI/MLEZI</h3>

<h4>TAARIFA YA MAENDELEO YA KITAALUMA</h4>

<table>
    <tr>
        <td><strong>Jina:</strong> {$student['full_name']}</td>
        <td><strong>Kidato:</strong> Kidato Cha {$student['class_level']}</td>
        <td><strong>Muhula:</strong> {$term} {$year}</td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th>SOMO</th>
            <th>ALAMA</th>
            <th>WASTANI</th>
            <th>DARAJA</th>
            <th>MAONI</th>
        </tr>
    </thead>
    <tbody>
EOD;

foreach ($subjects as $subj) {
    $sum = $subj['marks'];
    if ($sum == 0) {
        $html .= "<tr><td>{$subj['name']}</td><td>-</td><td>-</td><td>-</td><td>-</td></tr>";
        continue;
    }
    $avg = $sum;
    [$grade, $remark] = getGradeAndCommentByForm($form, $avg, 'sw');

    $html .= "<tr>
        <td>{$subj['name']}</td>
        <td>{$sum}</td>
        <td>{$avg}</td>
        <td>{$grade}</td>
        <td>{$remark}</td>
    </tr>";
}

$html .= "</tbody></table>";

$html .= <<<EOD
<div class="summary">
    <div>JUMLA YA ALAMA: {$totalMarks}</div>
    <div>WASTANI: {$average}</div>
    <div>DARAJA: {$finalGrade}</div>
    <div>POINTS: {$points}</div>
    <div>DIVISION: {$division}</div>
    <div>MAONI YA JUMLA: {$finalRemark}</div>
</div>

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
        <td>{$behavior[0]}</td>
        <td>{$behavior[1]}</td>
        <td>{$behavior[2]}</td>
        <td>{$behavior[3]}</td>
        <td>{$behavior[4]}</td>
    </tr>
</table>

<div class="comments">

<div class="comment-block">
    <strong>Mwalimu wa Darasa:</strong><br/>
    {$finalComments['teacher']}
    <div class="signature">Sahihi ya Mwalimu wa Darasa</div>
</div>

<div class="comment-block">
    <strong>Mkuu wa Shule:</strong><br/>
    {$finalComments['headteacher']}
    <div class="signature">Sahihi ya Mkuu wa Shule</div>
</div>

</div>
EOD;

$pdf->writeHTML($html, true, false, true, false, '');

$filename = preg_replace('/\s+/', '_', strtoupper($student['full_name'])) . '.pdf';

$pdf->Output($filename, 'D'); // 'D' forces download

exit;
?>
