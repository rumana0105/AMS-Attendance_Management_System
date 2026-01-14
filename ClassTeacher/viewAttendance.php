<?php 
error_reporting(E_ALL); // Enable error reporting
ini_set('display_errors', 1); // Display errors

include '../Includes/dbcon.php';
include '../Includes/session.php';

// Ensure session variables are set
if (!isset($_SESSION['userId']) || !isset($_SESSION['classId']) || !isset($_SESSION['classArmId'])) {
    die("Session variables are not set.");
}

// Fetch class and arm information
$query = "SELECT tblclass.className, tblclassarms.classArmName 
FROM tblclassteacher
INNER JOIN tblclass ON tblclass.Id = tblclassteacher.classId
INNER JOIN tblclassarms ON tblclassarms.Id = tblclassteacher.classArmId
WHERE tblclassteacher.Id = '$_SESSION[userId]'";

$rs = $conn->query($query);
if (!$rs) {
    die("Query failed: " . $conn->error);
}
$rrw = $rs->fetch_assoc();

// Session and Term
/* ---- OLD ----  */
// $querey = mysqli_query($conn,"SELECT * FROM tblterm WHERE isActive ='1'");

/* ---- NEW ----  */
$querey = mysqli_query($conn,"SELECT * FROM tblterm ORDER BY termOrder LIMIT 1");
$rwws = mysqli_fetch_array($querey);
//$sessionTermId = $rwws['Id'];

$dateTaken = date("Y-m-d");

// Fetch attendance records for today
$qurty = mysqli_query($conn, "SELECT * FROM tblattendance WHERE classId = '$_SESSION[classId]' AND classArmId = '$_SESSION[classArmId]' AND dateTimeTaken='$dateTaken'");
$attendanceRecords = [];
while ($row = mysqli_fetch_assoc($qurty)) {
    $attendanceRecords[] = $row;
}

$statusMsg = ""; // Initialize status message

if (isset($_POST['save'])) {
    // Initialize variables to avoid undefined index warnings
    $admissionNo = isset($_POST['admissionNo']) ? $_POST['admissionNo'] : [];
    $check = isset($_POST['check']) ? $_POST['check'] : [];
    $N = count($admissionNo);

    // Check if the attendance has not been taken (i.e., if no record has a status of 1)
    $qurty = mysqli_query($conn, "SELECT * FROM tblattendance WHERE classId = '$_SESSION[classId]' AND classArmId = '$_SESSION[classArmId]' AND dateTimeTaken='$dateTaken' AND status = '1'");
    $count = mysqli_num_rows($qurty);

    if ($count > 0) {
        $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>Attendance has been taken for today!</div>";
    } else { // Update the status to 1 for the checked checkboxes
        foreach ($check as $admissionNumber) {
            $qquery = mysqli_query($conn, "UPDATE tblattendance SET status='1' WHERE admissionNo = '$admissionNumber'");

            if ($qquery) {
                $statusMsg = "<div class='alert alert-success' style='margin-right:700px;'>Attendance Taken Successfully!</div>";
            } else {
                $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error occurred!</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <link href="img/logo/attnlg.jpg" rel="icon">
  <title>Dashboard</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">

  <script>
    function classArmDropdown(str) {
      if (str == "") {
          document.getElementById("txtHint").innerHTML = "";
          return;
      } else { 
          if (window.XMLHttpRequest) {
              xmlhttp = new XMLHttpRequest();
          } else {
              xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
          }
          xmlhttp.onreadystatechange = function() {
              if (this.readyState == 4 && this.status == 200) {
                  document.getElementById("txtHint").innerHTML = this.responseText;
              }
          };
          xmlhttp.open("GET", "ajaxClassArms2.php?cid=" + str, true);
          xmlhttp.send();
      }
    }
  </script>
</head>

<body id="page-top">
  <div id="wrapper">
    <!-- Sidebar -->
    <?php include "Includes/sidebar.php";?>
    <!-- Sidebar -->
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <!-- TopBar -->
        <?php include "Includes/topbar.php";?>
        <!-- Topbar -->

        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">View Attendance (Today's Date: <?php echo date("m-d-Y");?>)</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Attendance Records</li>
            </ol>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Attendance Records for (<?php echo $rrw['className'].' - '.$rrw['classArmName'];?>) Class</h6>
                </div>
                <div class="table-responsive p-3">
                  <?php echo $statusMsg; ?>
                  <table class="table align-items-center table-flush table-hover">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>Admission No</th>
                        <th>Status</th>
                        <th>Date Taken</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      if (count($attendanceRecords) > 0) {
                          $sn = 0;
                          foreach ($attendanceRecords as $record) {
                              $sn++;
                              echo "
                              <tr>
                                <td>".$sn."</td>
                                <td>".$record['admissionNo']."</td>
                                <td>".($record['status'] == 1 ? 'Present' : 'Absent')."</td>
                                <td>".$record['dateTimeTaken']."</td>
                              </tr>";
                          }
                      } else {
                          echo "<tr><td colspan='4' class='text-center'>No Attendance Records Found!</td></tr>";
                      }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <!--Row-->
        </div>
        <!---Container Fluid-->
      </div>
      <!-- Footer -->
      <?php include "Includes/footer.php";?>
      <!-- Footer -->
    </div>
  </div>

  <!-- Scroll to top -->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
  <!-- Page level plugins -->
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

  <!-- Page level custom scripts -->
  <script>
    $(document).ready(function () {
      $('#dataTableHover').DataTable(); // ID From dataTable with Hover
    });
  </script>
</body>

</html>