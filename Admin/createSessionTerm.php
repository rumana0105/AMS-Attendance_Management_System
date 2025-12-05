<?php
// -----------------------------------------------------------------
// createSessionTerm.php   (final with termId fix)
// -----------------------------------------------------------------
error_reporting(E_ALL);           // <- turn this off in production
ini_set('display_errors', 1);

include '../Includes/dbcon.php';
include '../Includes/session.php';

$statusMsg = "";

/* ----------------------------------------------------------------
   SAVE
---------------------------------------------------------------- */
if (isset($_POST['save'])) {

    $sessionName = trim($_POST['sessionName'] ?? '');
    $termId      = trim($_POST['termId']      ?? '');
    $dateCreated = date("Y-m-d");

    if ($sessionName === '' || $termId === '') {
        $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>Please enter a session and select a term.</div>";
    } else {

        $stmt = $conn->prepare(
            "SELECT 1 FROM tblsessionterm WHERE sessionName = ? AND termId = ? LIMIT 1"
        );
        $stmt->bind_param("si", $sessionName, $termId);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows) {
            $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>This Session and Term already exist!</div>";
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO tblsessionterm (sessionName, termId, isActive, dateCreated)
                 VALUES (?, ?, 0, ?)"
            );
            $stmt->bind_param("sis", $sessionName, $termId, $dateCreated);

            if ($stmt->execute()) {
                $statusMsg = "<div class='alert alert-success' style='margin-right:700px;'>Created successfully!</div>";
            } else {
                $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error occurred!</div>";
            }
        }
        $stmt->close();
    }
}

/* ----------------------------------------------------------------
   EDIT / UPDATE
---------------------------------------------------------------- */
if (isset($_GET['Id'], $_GET['action']) && $_GET['action'] === "edit") {

    $Id   = (int)$_GET['Id'];
    $row  = $conn->query("SELECT * FROM tblsessionterm WHERE Id = $Id")->fetch_assoc();

    if (isset($_POST['update'])) {

        $sessionName = trim($_POST['sessionName'] ?? '');
        $termId      = trim($_POST['termId']      ?? '');

        if ($sessionName === '' || $termId === '') {
            $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>Please enter a session and select a term.</div>";
        } else {
            $stmt = $conn->prepare(
                "UPDATE tblsessionterm
                 SET sessionName = ?, termId = ?, isActive = 0
                 WHERE Id = ?"
            );
            $stmt->bind_param("sii", $sessionName, $termId, $Id);

            if ($stmt->execute()) {
                echo "<script>window.location = 'createSessionTerm.php';</script>";
                exit;
            } else {
                $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error occurred!</div>";
            }
            $stmt->close();
        }
    }
}

/* ----------------------------------------------------------------
   DELETE
---------------------------------------------------------------- */
if (isset($_GET['Id'], $_GET['action']) && $_GET['action'] === "delete") {

    $Id = (int)$_GET['Id'];
    if ($conn->query("DELETE FROM tblsessionterm WHERE Id = $Id")) {
        echo "<script>window.location = 'createSessionTerm.php';</script>";
        exit;
    } else {
        $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error occurred!</div>";
    }
}

/* ----------------------------------------------------------------
   ACTIVATE
---------------------------------------------------------------- */
if (isset($_GET['Id'], $_GET['action']) && $_GET['action'] === "activate") {

    $Id = (int)$_GET['Id'];

    if ($conn->query("UPDATE tblsessionterm SET isActive = 0 WHERE isActive = 1")) {

        if ($conn->query("UPDATE tblsessionterm SET isActive = 1 WHERE Id = $Id")) {
            echo "<script>window.location = 'createSessionTerm.php';</script>";
            exit;
        }
    }
    $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>An error occurred!</div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http‑equiv="X‑UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="img/logo/attnlg.jpg" rel="icon">
  <?php include 'includes/title.php'; ?>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
</head>

<body id="page-top">
<div id="wrapper">
  <?php include "Includes/sidebar.php"; ?>
  <div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
      <?php include "Includes/topbar.php"; ?>

      <div class="container-fluid" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
          <h1 class="h3 mb-0 text-gray-800">Create Session and Term</h1>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="./">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create Session and Term</li>
          </ol>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <!-- Form -->
            <div class="card mb-4">
              <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Create Session and Term</h6>
                <?= $statusMsg; ?>
              </div>
              <div class="card-body">
                <form method="post">
                  <div class="form-group row mb-3">
                    <div class="col-xl-6">
                      <label class="form-control-label">Session Name<span class="text-danger ml-2">*</span></label>
                      <input type="text" class="form-control" name="sessionName"
                             value="<?= htmlspecialchars($row['sessionName'] ?? '') ?>" placeholder="Session" required>
                    </div>

                    <div class="col-xl-6">
                      <label class="form-control-label">Term<span class="text-danger ml-2">*</span></label>
                      <?php
$result = $conn->query("SELECT * FROM tblterm ORDER BY termId ASC");
echo '<select name="termId" class="form-control mb-3" required>';
                        echo '<option value="" disabled selected hidden>--Select Term--</option>';
                        while ($r = $result->fetch_assoc()) {
                            $selected = (isset($row['termId']) && $row['termId'] == $r['termId']) ? 'selected' : '';
                            echo "<option value='{$r['termId']}' $selected>{$r['termName']}</option>";
                        }
                        echo '</select>';
                      ?>
                    </div>
                  </div>

                  <?php if (isset($Id)) { ?>
                      <button type="submit" name="update" class="btn btn-warning">Update</button>
                  <?php } else { ?>
                      <button type="submit" name="save" class="btn btn-primary">Save</button>
                  <?php } ?>
                </form>
              </div>
            </div>

            <!-- Table -->
            <div class="card mb-4">
              <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">All Session and Term</h6>
              </div>
              <div class="table-responsive p-3">
                <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                  <thead class="thead-light">
                    <tr>
                      <th>#</th>
                      <th>Session</th>
                      <th>Term</th>
                      <th>Status</th>
                      <th>Date</th>
                      <th>Activate</th>
                      <th>Edit</th>
                      <th>Delete</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                    $query = "
                      SELECT  s.Id,
                              s.sessionName,
                              s.isActive,
                              s.dateCreated,
                              COALESCE(t.termName,'(term deleted)') AS termName
                      FROM    tblsessionterm AS s
                      LEFT JOIN tblterm AS t ON t.termId = s.termId
                      ORDER BY s.dateCreated DESC";

                    $rs  = $conn->query($query);
                    $sn  = 0;

                    while ($rows = $rs->fetch_assoc()) {
                        $status = $rows['isActive'] ? 'Active' : 'InActive';
                        echo "
                          <tr>
                            <td>".++$sn."</td>
                            <td>{$rows['sessionName']}</td>
                            <td>{$rows['termName']}</td>
                            <td>{$status}</td>
                            <td>{$rows['dateCreated']}</td>
                            <td><a href='?action=activate&Id={$rows['Id']}'><i class='fas fa-fw fa-check'></i></a></td>
                            <td><a href='?action=edit&Id={$rows['Id']}'><i class='fas fa-fw fa-edit'></i></a></td>
                            <td><a href='?action=delete&Id={$rows['Id']}' onclick=\"return confirm('Delete this record?');\"><i class='fas fa-fw fa-trash'></i></a></td>
                          </tr>";
                    }
                    if ($sn === 0) {
                        echo "<tr><td colspan='8' class='text-center'>No record found!</td></tr>";
                    }
                  ?>
                  </tbody>
                </table>
              </div>
            </div> <!-- /Table -->
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </div> <!-- /#content -->
    <?php include "Includes/footer.php"; ?>
  </div>
</div>

<a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/ruang-admin.min.js"></script>
<script src="../vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script>
  $(function () {
    $('#dataTableHover').DataTable();
  });
</script>
</body>
</html>
