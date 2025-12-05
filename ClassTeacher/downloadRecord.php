<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

$filename = "Attendance_List_" . date("Y-m-d") . ".xls";
$dateTaken = date("Y-m-d");

// Set headers before any output
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

echo "<table border='1'>";
echo "<thead>
<tr>
<th>#</th>
<th>First Name</th>
<th>Last Name</th>
<th>Admission No</th>
<th>Class</th>
<th>Class Arm</th>
<th>Session</th>
<th>Term</th>
<th>Status</th>
<th>Date</th>
</tr>
</thead>";

$query = "SELECT tblattendance.Id, tblattendance.status, tblattendance.dateTimeTaken, tblclass.className,
        tblclassarms.classArmName, tblsessionterm.sessionName, tblsessionterm.termId, tblterm.termName,
        tblstudents.firstName, tblstudents.lastName, tblstudents.admissionNumber
        FROM tblattendance
        INNER JOIN tblclass ON tblclass.Id = tblattendance.classId
        INNER JOIN tblclassarms ON tblclassarms.Id = tblattendance.classArmId
        INNER JOIN tblsessionterm ON tblsessionterm.Id = tblattendance.sessionTermId
        INNER JOIN tblterm ON tblterm.Id = tblsessionterm.termId
        INNER JOIN tblstudents ON tblstudents.admissionNumber = tblattendance.admissionNo
        WHERE tblattendance.dateTimeTaken = '$dateTaken'
        AND tblattendance.classId = '$_SESSION[classId]'
        AND tblattendance.classArmId = '$_SESSION[classArmId]'";

$result = mysqli_query($conn, $query);
$cnt = 1;

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $status = $row['status'] == '1' ? 'Present' : 'Absent';

        echo "<tr>
            <td>{$cnt}</td>
            <td>{$row['firstName']}</td>
            <td>{$row['lastName']}</td>
            <td>{$row['admissionNumber']}</td>
            <td>{$row['className']}</td>
            <td>{$row['classArmName']}</td>
            <td>{$row['sessionName']}</td>
            <td>{$row['termName']}</td>
            <td>{$status}</td>
            <td>{$row['dateTimeTaken']}</td>
        </tr>";
        $cnt++;
    }
} else {
    echo "<tr><td colspan='11'>No records found for today.</td></tr>";
}

echo "</table>";
?>
