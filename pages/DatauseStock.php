<?php
require_once __DIR__ . '/../includes/conn.php';
@session_start();
if ($_SESSION["user"] == "" or  $_SESSION["Namepro"] == "" or $_SESSION["iduser"] == "") {
  echo "<script>window.location = 'index.php';</script>";
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="shortcut icon" href="image/logoETL.jpg">
  <link rel="stylesheet" href="css/vendor/bootstrap/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="js/vendor/bootstrap/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="css/vendor/fontawesome/all.min.css">
  <link rel="stylesheet" href="css/vendor/fontawesome/all.min.css">
  <title>Stock</title>
  <!-- Favicon-->

  <!-- Core theme CSS (includes Bootstrap)-->
  <link href="css/app.css" rel="stylesheet" />
  <script>
    $(".remove").click(function() {
      var id = $(this).parents("tr").attr("id");
      if (confirm('Are you sure to delete this record ?')) {
        $.ajax({
          url: '/delete.php',
          type: 'GET',
          data: {
            id: id
          },
          error: function() {
            alert('Something is wrong');
          },
          success: function(data) {
            $("#" + id).remove();
            alert("Record deleted successfully");
          }
        });
      }
    });
  </script>

  <link rel="stylesheet" href="css/pages/DatauseStock.css">
  <?php
  $Province = $_SESSION["Namepro"];


  $sql = "SELECT*FROM usestock a
WHERE a.Provinces LIKE '$Province'and (TIMESTAMPDIFF(day,a.Date,CURDATE())< 2)
ORDER BY a.Date asc ";





  mysqli_set_charset(@$conn, "utf8");
  $result = $conn->query($sql);
  if ($result->num_rows > 0) {
  ?>
    <div align="center">
      <table class="table table-hover circle  ">
        <thead>
          <tr class="trcolor" style="font-size: 15px;color:blue">

            <th width="50%">ນໍາໃຊ້ອຸປະກອນ</th>
            <th width="20%">ເອກະສານ</th>

            <th width="10%">ຈຳນວນທີ່ໃຊ້</th>

            <th align="right" width="20%" style="color: red">Remark</th>


          </tr>
        </thead>
        <tbody>
          <?php
          while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $showcolor = '#FFFFFF';

          ?>
            <tr bgcolor="<?= $showcolor; ?>" style="font-size: 13px;">
              <td width="30%">
                <strong>
                    <i class="fa-light fa-forklift fa-lg"></i> <?php echo $row['Items'] ?><br>
                    <i class="fa-light fa-network-wired fa-lg"></i> ພາກສ່ວນ: <?php echo $row['Section'] ?><br>
                    <i class="fa-regular fa-location-dot fa-lg"></i> ໃຊ້ຢູ່ສະຖານທີ່: <?php echo $row['Station'] ?><br>
                    <i class="fa-duotone fa-screwdriver-wrench"></i> ຜູ້ນໍາໃຊ້: <?php echo $row['Use_Name'] ?>
                </strong>
              </td>
              <td width="30%">
                <strong>
                  <i class="fa-light fa-keyboard"></i> serial number: <?php echo $row['Series_Number'] ?><br> 
                  <i class="fa-light fa-memo-circle-info fa-lg"></i> PR ເລກທີ: <?php echo $row['OA'] ?><?php if (!empty($row['planing_number'])): ?><br>
                  <i class="fa-regular fa-ticket"></i> ແຜນເລກທີ່: <?php echo htmlspecialchars($row['planing_number']); ?><?php endif; ?><br>
                  <i class="fa-regular fa-user-tie-hair fa-lg"></i> <?php echo $row['User'] ?><br><i class="fa-duotone fa-calendar-days fa-lg"></i> <?php echo $row['Date'] ?>
                </strong>
              </td>
              <td width="10%">
                <strong>
                  <?php echo $row['Unit'] ?> <?php echo $row['Type'] ?>
                </strong>
              </td>
              <td width="20%" align="center" style="color: red">
                <strong> 
                  <?php echo $row['Remark'] ?> 
                </strong>
              </td>
            </tr>
            <?php } ?>
        </tbody>

      <?php }

      ?>
      </table>
    </div>
