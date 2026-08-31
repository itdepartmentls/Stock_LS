  <?php
    require_once __DIR__ . '/../includes/conn.php';
    @session_start();
    if ($_SESSION["user"] == "" or $_SESSION["iduser"] == "") {
        echo "<script>window.location = 'index.php';</script>";
        exit;
    }

    ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
      <meta charset="utf-8" />
      <link rel="shortcut icon" href="image/logoETL.jpg">

      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
      <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha256-OFRAJNoaD8L3Br5lglV7VyLRf0itmoBzWUoM+Sji4/8=" crossorigin="anonymous"></script>
      <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
      <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
      <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>

      <link rel="stylesheet" href="js/pro.min.js">
      <link rel="stylesheet" href="css/all.min.css">
      <title>ການດຳເນີນການ</title>

      <!-- Demo stylesheet -->

  </head>
  <style type="text/css">
      body,
      td,
      th {
          font-family: "Phetsarath OT";
      }

      .right {
          float: right;
      }
  </style>
  <?php
    if (isset($_POST["TMDUPDate"])) {
        date_default_timezone_set("Asia/Bangkok");
        $date_time = date('20y-m-d G:i:s');

        $sql = "UPDATE followequment SET Remark_TMD='" . $_POST['TMDRemark'] . "',Date_TMD='$date_time',Team_fixed='" . $_POST['section'] . "'
		 WHERE id='" . $_POST["id"] . "'";
        if ($conn->query($sql) === TRUE) { ?>
          <script>
              window.top.location = "follow.php";
          </script>

  <?php }
    } ?>



  <?php
    if (isset($_POST["CheckUPDate"])) {
        date_default_timezone_set("Asia/Bangkok");
        $date_time = date('20y-m-d G:i:s');

        if ($_POST['stt'] == 'ໃຊ້ໄດ້ປົກກະຕິ') {
            $sql = "UPDATE followequment SET Remark_Fix='" . $_POST['fixRemark'] . "',Date_Fix='$date_time',STT_Fix='" . $_POST['stt'] . "' WHERE id='" . $_POST["id"] . "'";
            @mysqli_set_charset(@$conn, "utf8");
        } elseif ($_POST['stt'] == 'ຕາຍ') {
            @mysqli_set_charset(@$conn, "utf8");
            $sql = "UPDATE followequment SET Remark_Fix='" . $_POST['fixRemark'] . "',Date_Fix='$date_time',STT_Fix='" . $_POST['stt'] . "',Go_Province='ສະສາງ',Date_Goto='" . $_SESSION["user"] . " $date_time'
	WHERE id='" . $_POST["id"] . "'";

            $sql4488 = "SELECT*FROM followequment
WHERE followequment.id = '" . $_POST["id"] . "'";
            @mysqli_set_charset(@$conn, "utf8");
            $result4488 = $conn->query($sql4488);
            $rowinsert88 = $result4488->fetch_assoc();


            $sql11241 = "INSERT INTO stocklose (Name,
Number,
from_Pro,
To_Stock,
Date,
STT,
User,address_Use,comment)
VALUES ('" . $rowinsert88["Name_Come"] . "','" . $rowinsert88["NumBer_S"] . "','" . $rowinsert88["Cat_Province"] . "','ສະສາງ','$date_time','" . $_POST['stt'] . "','" . $_SESSION["user"] . " $date_time','" . $rowinsert88["address"] . "','" . $_POST['fixRemark'] . "')";
            $conn->query($sql11241);
            @mysqli_set_charset(@$conn, "utf8");
        }

        if ($conn->query($sql) === TRUE) { ?>



      <?php }
        ?>

      <script>
          window.top.location = "center.php";
      </script>
  <?php } ?>

  <?php
    if (isset($_POST["stockUPDate"])) {
        date_default_timezone_set("Asia/Bangkok");
        $date_time = date('20y-m-d G:i:s');

        $sql12112 = "UPDATE followequment
 SET Go_Province = '" . $_POST["provinceee"] . "',Date_Goto='" . $_SESSION["user"] . " $date_time'
 WHERE followequment.id LIKE '" . $_POST["id"] . "' ";
        $conn->query($sql12112);
        @mysqli_set_charset(@$conn, "utf8");





        if ($_POST['provinceee'] <> 'TMD' and $_POST['provinceee'] <> 'ສູນກາງ') {

            $sqll = "SELECT*FROM stock_province
WHERE stock_province.Items = '" . $_POST["Name_Come"] . "' and stock_province.Provinces = '" . $_POST["provinceee"] . "'";
            $result = $conn->query($sqll);


            if ($result->num_rows > 0) {

                $sql1 = "UPDATE stock_province
 SET Unit = Unit + '1',User_Stock = '" . $_SESSION["user"] . "',Date_Pro = '" . $date_time . "'
 WHERE stock_province.Items LIKE '" . $_POST["Name_Come"] . "' and stock_province.Provinces LIKE '" . $_POST["provinceee"] . "'";
                $conn->query($sql1);




                $sql44 = "SELECT*FROM stock
WHERE stock.Items = '" . $_POST["Name_Come"] . "'";
                $result44 = $conn->query($sql44);
                $rowinsert = $result44->fetch_assoc();

                $sql1124 = "INSERT INTO stock_provinceinput (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,User_Stock,Provinces,Date_Pro,Remarkk,from_pro)
  
VALUES ('" . $rowinsert["Items"] . "','" . $rowinsert["Item_code"] . "','" . $rowinsert["Vendor"] . "','" . $rowinsert["Use_For"] . "','" . $rowinsert["Type"] . "','1','" . $rowinsert["Groupp"] . "','" . $rowinsert["picture"] . "','" . $rowinsert["Section"] . "','" . $_SESSION["user"] . "','" . $_POST['provinceee'] . "','" . $date_time . "','ການສ້ອມແປງ','" . $_POST["formpro"] . "')";
                $conn->query($sql1124);

                @mysqli_set_charset(@$conn, "utf8");
            } else {

                $sql44 = "SELECT*FROM stock
WHERE stock.Items = '" . $_POST["Name_Come"] . "'";
                $result44 = $conn->query($sql44);
                $rowinsert = $result44->fetch_assoc();

                $sql11 = "INSERT INTO stock_province (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,User_Stock,Provinces,Date_Pro)
  
VALUES ('" . $rowinsert["Items"] . "','" . $rowinsert["Item_code"] . "','" . $rowinsert["Vendor"] . "','" . $rowinsert["Use_For"] . "','" . $rowinsert["Type"] . "','1','" . $rowinsert["Groupp"] . "','" . $rowinsert["picture"] . "','" . $rowinsert["Section"] . "','" . $_SESSION["user"] . "','" . $_POST['provinceee'] . "','" . $date_time . "')";
                $conn->query($sql11);

                $sql1124 = "INSERT INTO stock_provinceinput (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,User_Stock,Provinces,Date_Pro,Remarkk,from_pro)
  
VALUES ('" . $rowinsert["Items"] . "','" . $rowinsert["Item_code"] . "','" . $rowinsert["Vendor"] . "','" . $rowinsert["Use_For"] . "','" . $rowinsert["Type"] . "','1','" . $rowinsert["Groupp"] . "','" . $rowinsert["picture"] . "','" . $rowinsert["Section"] . "','" . $_SESSION["user"] . "','" . $_POST['provinceee'] . "','" . $date_time . "','ການສ້ອມແປງ','" . $_POST["formpro"] . "')";
                $conn->query($sql1124);


                @mysqli_set_charset(@$conn, "utf8");
            }
        }
        if ($_POST['provinceee'] == 'TMD') {








            $sql1212123 = "UPDATE stock
 SET stock.Stock_TMD = Stock_TMD + '1',User_Stock = '" . $_SESSION["user"] . "'
 WHERE stock.Items = '" . $_POST["Name_Come"] . "' ";
            $conn->query($sql1212123);




            $sql4455 = "SELECT*FROM stock
WHERE stock.Items = '" . $_POST["Name_Come"] . "'";
            $result4455 = $conn->query($sql4455);
            $rowinserttmd = $result4455->fetch_assoc();

            $sql112455 = "INSERT INTO stockinput (Items,Item_code,Vendor,Use_For,Type,Stock_TMD,Groupp,picture,Section,Date,User,Remarkk,from_pro)
  
VALUES ('" . $rowinserttmd["Items"] . "','" . $rowinserttmd["Item_code"] . "','" . $rowinserttmd["Vendor"] . "','" . $rowinserttmd["Use_For"] . "','" . $rowinserttmd["Type"] . "','1','" . $rowinserttmd["Groupp"] . "','" . $rowinserttmd["picture"] . "','" . $rowinserttmd["Section"] . "','" . $date_time . "','" . $_SESSION["user"] . "','ການສ້ອມແປງ','" . $_POST["formpro"] . "')";
            $conn->query($sql112455);

            @mysqli_set_charset(@$conn, "utf8");
        }
        if ($_POST['provinceee'] == 'ສູນກາງ') {








            $sql1212123 = "UPDATE stock
 SET stock.Unit = Unit + '1',User_Stock = '" . $_SESSION["user"] . "'
 WHERE stock.Items = '" . $_POST["Name_Come"] . "' ";
            $conn->query($sql1212123);




            $sql4455 = "SELECT*FROM stock
WHERE stock.Items = '" . $_POST["Name_Come"] . "'";
            $result4455 = $conn->query($sql4455);
            $rowinserttmd = $result4455->fetch_assoc();

            $sql112455 = "INSERT INTO stockinput (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,Date,User,Remarkk,from_pro)
  
VALUES ('" . $rowinserttmd["Items"] . "','" . $rowinserttmd["Item_code"] . "','" . $rowinserttmd["Vendor"] . "','" . $rowinserttmd["Use_For"] . "','" . $rowinserttmd["Type"] . "','1','" . $rowinserttmd["Groupp"] . "','" . $rowinserttmd["picture"] . "','" . $rowinserttmd["Section"] . "','" . $date_time . "','" . $_SESSION["user"] . "','ການສ້ອມແປງ','" . $_POST["formpro"] . "')";
            $conn->query($sql112455);

            @mysqli_set_charset(@$conn, "utf8");
        }



    ?>
      <script>
          window.location = "follow.php";
      </script>

  <?php } ?>



  <?php
    if (isset($_POST["stockUPDatemove"])) {
        date_default_timezone_set("Asia/Bangkok");
        $date_time = date('20y-m-d G:i:s');

        $sql12112 = "UPDATE followequment
 SET Go_Province = '" . $_POST["provinceee"] . "',Date_Goto='$date_time',Remark_TMD='" . $_POST['TMDRemark'] . "'
 WHERE followequment.id LIKE '" . $_POST["id"] . "' ";
        $conn->query($sql12112);
        @mysqli_set_charset(@$conn, "utf8");





        if ($_POST['provinceee'] <> 'TMD' and $_POST['provinceee'] <> 'ສູນກາງ') {

            $sqll = "SELECT*FROM stock_province
WHERE stock_province.Items = '" . $_POST["Name_Come"] . "' and stock_province.Provinces = '" . $_POST["provinceee"] . "'";
            $result = $conn->query($sqll);


            if ($result->num_rows > 0) {

                $sql1 = "UPDATE stock_province
 SET Unit = Unit + '1',User_Stock = '" . $_SESSION["user"] . "',Date_Pro = '" . $date_time . "'
 WHERE stock_province.Items LIKE '" . $_POST["Name_Come"] . "' and stock_province.Provinces LIKE '" . $_POST["provinceee"] . "'";
                $conn->query($sql1);

               // $sql12 = "UPDATE stock_province
 //SET Unit = Unit - '1',User_Stock = '" . $_SESSION["user"] . "',Date_Pro = '" . $date_time . "'
 //WHERE stock_province.Items LIKE '" . $_POST["Name_Come"] . "' and stock_province.Provinces LIKE '" . $_POST["formpro"] . "'";
               // $conn->query($sql12);




                $sql44 = "SELECT*FROM stock
WHERE stock.Items = '" . $_POST["Name_Come"] . "'";
                $result44 = $conn->query($sql44);
                $rowinsert = $result44->fetch_assoc();

                $sql1124 = "INSERT INTO stock_provinceinput (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,User_Stock,Provinces,Date_Pro,Remarkk)
  
VALUES ('" . $rowinsert["Items"] . "','" . $rowinsert["Item_code"] . "','" . $rowinsert["Vendor"] . "','" . $rowinsert["Use_For"] . "','" . $rowinsert["Type"] . "','1','" . $rowinsert["Groupp"] . "','" . $rowinsert["picture"] . "','" . $rowinsert["Section"] . "','" . $_SESSION["user"] . "','" . $_POST['provinceee'] . "','" . $date_time . "','ຍົກຍ້າຍ')";
                $conn->query($sql1124);

                @mysqli_set_charset(@$conn, "utf8");
            } else {

                $sql44 = "SELECT*FROM stock
WHERE stock.Items = '" . $_POST["Name_Come"] . "'";
                $result44 = $conn->query($sql44);
                $rowinsert = $result44->fetch_assoc();

                $sql11 = "INSERT INTO stock_province (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,User_Stock,Provinces,Date_Pro)
  
VALUES ('" . $rowinsert["Items"] . "','" . $rowinsert["Item_code"] . "','" . $rowinsert["Vendor"] . "','" . $rowinsert["Use_For"] . "','" . $rowinsert["Type"] . "','1','" . $rowinsert["Groupp"] . "','" . $rowinsert["picture"] . "','" . $rowinsert["Section"] . "','" . $_SESSION["user"] . "','" . $_POST['provinceee'] . "','" . $date_time . "')";
                $conn->query($sql11);

                $sql1124 = "INSERT INTO stock_provinceinput (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,User_Stock,Provinces,Date_Pro,Remarkk)
  
VALUES ('" . $rowinsert["Items"] . "','" . $rowinsert["Item_code"] . "','" . $rowinsert["Vendor"] . "','" . $rowinsert["Use_For"] . "','" . $rowinsert["Type"] . "','1','" . $rowinsert["Groupp"] . "','" . $rowinsert["picture"] . "','" . $rowinsert["Section"] . "','" . $_SESSION["user"] . "','" . $_POST['provinceee'] . "','" . $date_time . "','ຍົກຍ້າຍ')";
                $conn->query($sql1124);

               // $sql121 = "UPDATE stock_province
 //SET Unit = Unit - '1',User_Stock = '" . $_SESSION["user"] . "',Date_Pro = '" . $date_time . "'
 //WHERE stock_province.Items LIKE '" . $_POST["Name_Come"] . "' and stock_province.Provinces LIKE '" . $_POST["formpro"] . "'";
               // $conn->query($sql121);


                @mysqli_set_charset(@$conn, "utf8");
            }
        }
        if ($_POST['provinceee'] == 'TMD') {








            $sql1212123 = "UPDATE stock
 SET stock.Stock_TMD = Stock_TMD + '1',User_Stock = '" . $_SESSION["user"] . "'
 WHERE stock.Items = '" . $_POST["Name_Come"] . "' ";
            $conn->query($sql1212123);




            $sql4455 = "SELECT*FROM stock
WHERE stock.Items = '" . $_POST["Name_Come"] . "'";
            $result4455 = $conn->query($sql4455);
            $rowinserttmd = $result4455->fetch_assoc();

            $sql112455 = "INSERT INTO stockinput (Items,Item_code,Vendor,Use_For,Type,Stock_TMD,Groupp,picture,Section,Date,User,Remarkk)
  
VALUES ('" . $rowinserttmd["Items"] . "','" . $rowinserttmd["Item_code"] . "','" . $rowinserttmd["Vendor"] . "','" . $rowinserttmd["Use_For"] . "','" . $rowinserttmd["Type"] . "','1','" . $rowinserttmd["Groupp"] . "','" . $rowinserttmd["picture"] . "','" . $rowinserttmd["Section"] . "','" . $date_time . "','" . $_SESSION["user"] . "','ຍົກຍ້າຍ')";
            $conn->query($sql112455);

           // $sql121 = "UPDATE stock_province
 //SET Unit = Unit - '1',User_Stock = '" . $_SESSION["user"] . "',Date_Pro = '" . $date_time . "'
 //WHERE stock_province.Items LIKE '" . $_POST["Name_Come"] . "' and stock_province.Provinces LIKE '" . $_POST["formpro"] . "'";
           // $conn->query($sql121);

            @mysqli_set_charset(@$conn, "utf8");
        }
        if ($_POST['provinceee'] == 'ສູນກາງ') {








            $sql1212123 = "UPDATE stock
 SET stock.Unit = Unit + '1',User_Stock = '" . $_SESSION["user"] . "'
 WHERE stock.Items = '" . $_POST["Name_Come"] . "' ";
            $conn->query($sql1212123);




            $sql4455 = "SELECT*FROM stock
WHERE stock.Items = '" . $_POST["Name_Come"] . "'";
            $result4455 = $conn->query($sql4455);
            $rowinserttmd = $result4455->fetch_assoc();

            $sql112455 = "INSERT INTO stockinput (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,Date,User,Remarkk)
  
VALUES ('" . $rowinserttmd["Items"] . "','" . $rowinserttmd["Item_code"] . "','" . $rowinserttmd["Vendor"] . "','" . $rowinserttmd["Use_For"] . "','" . $rowinserttmd["Type"] . "','1','" . $rowinserttmd["Groupp"] . "','" . $rowinserttmd["picture"] . "','" . $rowinserttmd["Section"] . "','" . $date_time . "','" . $_SESSION["user"] . "','ຍົກຍ້າຍ')";
            $conn->query($sql112455);

           // $sql121 = "UPDATE stock_province
 //SET Unit = Unit - '1',User_Stock = '" . $_SESSION["user"] . "',Date_Pro = '" . $date_time . "'
 //WHERE stock_province.Items LIKE '" . $_POST["Name_Come"] . "' and stock_province.Provinces LIKE '" . $_POST["formpro"] . "'";
           // $conn->query($sql121);

            @mysqli_set_charset(@$conn, "utf8");
        }



    ?>
      <script>
          window.location = "follow.php";
      </script>

  <?php } ?>


  <?php
    if (isset($_POST["stockUPDaterestore"])) {
        date_default_timezone_set("Asia/Bangkok");
        $date_time = date('20y-m-d G:i:s');

        $sql12112 = "UPDATE followequment
 SET Go_Province = '" . $_POST["provinceee"] . "',Date_Goto='$date_time',Remark_TMD='" . $_POST['TMDRemark'] . "'
 WHERE followequment.id LIKE '" . $_POST["id"] . "' ";
        $conn->query($sql12112);
        @mysqli_set_charset(@$conn, "utf8");





        if ($_POST['provinceee'] <> 'TMD' and $_POST['provinceee'] <> 'ສູນກາງ') {

            $sqll = "SELECT*FROM stock_province
WHERE stock_province.Items = '" . $_POST["Name_Come"] . "' and stock_province.Provinces = '" . $_POST["provinceee"] . "'";
            $result = $conn->query($sqll);


            if ($result->num_rows > 0) {

                $sql1 = "UPDATE stock_province
 SET Unit = Unit + '1',User_Stock = '" . $_SESSION["user"] . "',Date_Pro = '" . $date_time . "'
 WHERE stock_province.Items LIKE '" . $_POST["Name_Come"] . "' and stock_province.Provinces LIKE '" . $_POST["provinceee"] . "'";
                $conn->query($sql1);




                $sql44 = "SELECT*FROM stock
WHERE stock.Items = '" . $_POST["Name_Come"] . "'";
                $result44 = $conn->query($sql44);
                $rowinsert = $result44->fetch_assoc();

                $sql1124 = "INSERT INTO stock_provinceinput (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,User_Stock,Provinces,Date_Pro,Remarkk)
  
VALUES ('" . $rowinsert["Items"] . "','" . $rowinsert["Item_code"] . "','" . $rowinsert["Vendor"] . "','" . $rowinsert["Use_For"] . "','" . $rowinsert["Type"] . "','1','" . $rowinsert["Groupp"] . "','" . $rowinsert["picture"] . "','" . $rowinsert["Section"] . "','" . $_SESSION["user"] . "','" . $_POST['provinceee'] . "','" . $date_time . "','ຖອນ')";
                $conn->query($sql1124);

                @mysqli_set_charset(@$conn, "utf8");
            } else {

                $sql44 = "SELECT*FROM stock
WHERE stock.Items = '" . $_POST["Name_Come"] . "'";
                $result44 = $conn->query($sql44);
                $rowinsert = $result44->fetch_assoc();

                $sql11 = "INSERT INTO stock_province (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,User_Stock,Provinces,Date_Pro)
  
VALUES ('" . $rowinsert["Items"] . "','" . $rowinsert["Item_code"] . "','" . $rowinsert["Vendor"] . "','" . $rowinsert["Use_For"] . "','" . $rowinsert["Type"] . "','1','" . $rowinsert["Groupp"] . "','" . $rowinsert["picture"] . "','" . $rowinsert["Section"] . "','" . $_SESSION["user"] . "','" . $_POST['provinceee'] . "','" . $date_time . "')";
                $conn->query($sql11);

                $sql1124 = "INSERT INTO stock_provinceinput (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,User_Stock,Provinces,Date_Pro,Remarkk)
  
VALUES ('" . $rowinsert["Items"] . "','" . $rowinsert["Item_code"] . "','" . $rowinsert["Vendor"] . "','" . $rowinsert["Use_For"] . "','" . $rowinsert["Type"] . "','1','" . $rowinsert["Groupp"] . "','" . $rowinsert["picture"] . "','" . $rowinsert["Section"] . "','" . $_SESSION["user"] . "','" . $_POST['provinceee'] . "','" . $date_time . "','ຖອນ')";
                $conn->query($sql1124);


                @mysqli_set_charset(@$conn, "utf8");
            }
        }
        if ($_POST['provinceee'] == 'TMD') {








            $sql1212123 = "UPDATE stock
 SET stock.Stock_TMD = Stock_TMD + '1',User_Stock = '" . $_SESSION["user"] . "'
 WHERE stock.Items = '" . $_POST["Name_Come"] . "' ";
            $conn->query($sql1212123);




            $sql4455 = "SELECT*FROM stock
WHERE stock.Items = '" . $_POST["Name_Come"] . "'";
            $result4455 = $conn->query($sql4455);
            $rowinserttmd = $result4455->fetch_assoc();

            $sql112455 = "INSERT INTO stockinput (Items,Item_code,Vendor,Use_For,Type,Stock_TMD,Groupp,picture,Section,Date,User,Remarkk,from)
  
VALUES ('" . $rowinserttmd["Items"] . "','" . $rowinserttmd["Item_code"] . "','" . $rowinserttmd["Vendor"] . "','" . $rowinserttmd["Use_For"] . "','" . $rowinserttmd["Type"] . "','1','" . $rowinserttmd["Groupp"] . "','" . $rowinserttmd["picture"] . "','" . $rowinserttmd["Section"] . "','" . $date_time . "','" . $_SESSION["user"] . "','ຖອນ','" . $_POST["form"] . "')";
            $conn->query($sql112455);

            @mysqli_set_charset(@$conn, "utf8");
        }
        if ($_POST['provinceee'] == 'ສູນກາງ') {








            $sql1212123 = "UPDATE stock
 SET stock.Unit = Unit + '1',User_Stock = '" . $_SESSION["user"] . "'
 WHERE stock.Items = '" . $_POST["Name_Come"] . "' ";
            $conn->query($sql1212123);




            $sql4455 = "SELECT*FROM stock
WHERE stock.Items = '" . $_POST["Name_Come"] . "'";
            $result4455 = $conn->query($sql4455);
            $rowinserttmd = $result4455->fetch_assoc();

            $sql112455 = "INSERT INTO stockinput (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,Date,User,Remarkk)
  
VALUES ('" . $rowinserttmd["Items"] . "','" . $rowinserttmd["Item_code"] . "','" . $rowinserttmd["Vendor"] . "','" . $rowinserttmd["Use_For"] . "','" . $rowinserttmd["Type"] . "','1','" . $rowinserttmd["Groupp"] . "','" . $rowinserttmd["picture"] . "','" . $rowinserttmd["Section"] . "','" . $date_time . "','" . $_SESSION["user"] . "','ຖອນ')";
            $conn->query($sql112455);

            @mysqli_set_charset(@$conn, "utf8");
        }



    ?>
      <script>
          window.location = "follow.php";
      </script>

  <?php } ?>