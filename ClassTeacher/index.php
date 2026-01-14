<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../Includes/dbcon.php';
include '../Includes/session.php';

$className = "";
$classArmName = "";

// Get class and arm name for teacher
$query = "SELECT class.Id AS classId, class.className, arm.classArmName 
          FROM tblclassteacher AS teacher
          INNER JOIN tblclass AS class ON class.Id = teacher.classId
          INNER JOIN tblclassarms AS arm ON arm.Id = teacher.classArmId
          WHERE teacher.Id = '" . $_SESSION['userId'] . "'";

$rs = $conn->query($query);

if ($rs && $rs->num_rows > 0) {
    $rrw = $rs->fetch_assoc();
    $className = $rrw['className'];
    $classArmName = $rrw['classArmName'];
    $_SESSION['classId'] = $rrw['classId'];
    $_SESSION['classArmId'] = getClassArmId($conn, $_SESSION['userId']);
} else {
    $className = "Not Assigned";
    $classArmName = "";
}

function getClassArmId($conn, $userId) {
    $query = "SELECT classArmId FROM tblclassteacher WHERE Id = '$userId'";
    $res = $conn->query($query);
    $row = $res->fetch_assoc();
    return $row['classArmId'] ?? null;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="img/logo/attnlg.jpg" rel="icon">
  <title>Class Teacher Dashboard</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
</head>

<body id="page-top">
  <div id="wrapper">
    <?php include "Includes/sidebar.php";?>
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <?php include "Includes/topbar.php";?>

        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Class Teacher Dashboard (<?php echo $className . ' - ' . $classArmName; ?>)</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
          </div>

          <div class="row mb-3">
            <!-- Students Count -->
            <?php 
            $query1 = mysqli_query($conn, "SELECT * FROM tblstudents WHERE classId = '" . $_SESSION['classId'] . "' AND classArmId = '" . $_SESSION['classArmId'] . "'");
            $students = mysqli_num_rows($query1);
            ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Students</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $students; ?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-users fa-2x text-info"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Attendance Count -->
            <?php 
            $query2 = mysqli_query($conn, "SELECT * FROM tblattendance WHERE classId = '" . $_SESSION['classId'] . "' AND classArmId = '" . $_SESSION['classArmId'] . "'");
            $attendance = mysqli_num_rows($query2);
            ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Total Attendance</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $attendance; ?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-calendar-check fa-2x text-success"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
      <?php include 'Includes/footer.php'; ?>
    </div>
  </div>

  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
</body>

</html>
