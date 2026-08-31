<?php
require_once __DIR__ . '/../includes/conn.php';
@session_start();
if ($_SESSION["user"] == "") {
    echo "<script>window.location = 'index.php';</script>";
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="shortcut icon" href="image/logoETL.jpg">
    <title>ຕິດຕາມອຸປະກອນ</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="js/pro.min.js">
    <link rel="stylesheet" href="css/all.min.css">
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
date_default_timezone_set("Asia/Bangkok");
$idd = $_REQUEST['id'] ?? '';
$Name_Come = $_REQUEST['Name_Come'] ?? '';
$S_Status = $_REQUEST['S_Status'] ?? '';

mysqli_set_charset(@$conn, "utf8");

$sql = "SELECT * FROM followequment WHERE followequment.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $idd);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc() ?? [];
$stmt->close();

$sql2 = "SELECT * FROM stock WHERE stock.Items = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("s", $Name_Come);
$stmt2->execute();
$result2 = $stmt2->get_result();
$row2 = $result2->fetch_assoc() ?? [];
$stmt2->close();

if (($row['User_TMD'] ?? '') <> '') {
    $theme = "btn-primary";
} else {

    $theme = "btn-secondary";
}
if (($row['STT_Fix'] ?? '') == 'ໃຊ້ໄດ້ປົກກະຕິ') {
    $themefix = "btn-primary";
} elseif (($row['STT_Fix'] ?? '') == 'ຕາຍ') {

    $themefix = "btn-danger";
} elseif (($row['STT_Fix'] ?? '') == '') {

    $themefix = "btn-secondary";
}


?>

<?php
if (($row['User_TMD'] ?? '') == '' and ($_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793")) {
    date_default_timezone_set("Asia/Bangkok");
    $date_time = date('20y-m-d G:i:s');
    $userTmdValue = $_SESSION["user"] . " " . $date_time;

    $stmtUpd = $conn->prepare("UPDATE followequment SET User_TMD = ? WHERE id = ?");
    $stmtUpd->bind_param("ss", $userTmdValue, $idd);
    $stmtUpd->execute();
    $stmtUpd->close();
    $row['User_TMD'] = $userTmdValue;
} ?>
<?php
if (($row['Team_FIx'] ?? '') == '' and ($_SESSION["iduser"] == '51' or $_SESSION["iduser"] == '41' or $_SESSION["iduser"] == '378' or $_SESSION["iduser"] == '387' or $_SESSION["iduser"] == '514' or $_SESSION["iduser"] == '423' or $_SESSION["iduser"] == '166' or $_SESSION["iduser"] == '167' or $_SESSION["iduser"] == '168' or $_SESSION["iduser"] == '624' or $_SESSION["iduser"] == '190' or $_SESSION["iduser"] == '197' or $_SESSION["iduser"] == '816')) {
    date_default_timezone_set("Asia/Bangkok");
    $date_time = date('20y-m-d G:i:s');
    $teamFixValue = $_SESSION["user"] . " " . $date_time;

    $stmtUpd2 = $conn->prepare("UPDATE followequment SET Team_FIx = ? WHERE id = ?");
    $stmtUpd2->bind_param("ss", $teamFixValue, $idd);
    $stmtUpd2->execute();
    $stmtUpd2->close();
    $row['Team_FIx'] = $teamFixValue;
} ?>

<body>

    <div class="container col-lg">
        <h2><b><i class="fa-sharp fa-regular fa-file-invoice"></i> ເອກະສານ: <?= htmlspecialchars($S_Status) ?></b></h2>




        <?php if ($S_Status == 'ສ້ອມແປງ') { ?>
            <div class="card">
                <div class="card-header"> <b><i class="fa-solid fa-user-vneck-hair"></i> ຜູ້ເຮັດເອກະສານ: <?php echo htmlspecialchars($row['UserProvince'] ?? '') ?></b><br><b><i class="fa-duotone fa-earth-americas"></i> ຈາກສາຂາ: <?php echo htmlspecialchars($row['Cat_Province'] ?? '') ?></b></div>
                <div class="card-body"><a class="right" href="<?php echo htmlspecialchars($row2['picture'] ?? '') ?>" target="_blank"><img class="right" src="<?php echo htmlspecialchars($row2['picture'] ?? '') ?>" style="width:200px;height:120px;"></a>


                    <strong><i class="fa-duotone fa-server"></i> ຊື່ອຸປະກອນ :</strong> <?php echo htmlspecialchars($row['Name_Come'] ?? '') ?><br>
                    <strong><i class="fa-light fa-keyboard"></i> ຊີລຽວນາມເບີ :</strong> <?php echo htmlspecialchars($row['NumBer_S'] ?? '') ?><br>
                    <strong><i class="fa-light fa-map-location-dot"></i> ໃຊ້ຢູ່ສະຖານທີ່ :</strong> <?php echo htmlspecialchars($row['address'] ?? '') ?><br>
                    <strong><i class="fa-light fa-sensor-triangle-exclamation"></i> ໝາຍເຫດ :</strong> <?php echo htmlspecialchars($row['Remark_Pro'] ?? '') ?>

                </div>
                <?php if (($row['Remark_TMD'] ?? '') == '' and ($_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793")) { ?>
                    <div class="card-header"><b><i class="fa-duotone fa-user-headset"></i> ສາງພະແນກສາງສຳນັກງານໃຫຍ່</b></div>
                    <div class="card-body">


                        <h6><i class="fa-solid fa-user-vneck-hair"></i> <strong>ຜູ້ກວດສອບ: <?php echo htmlspecialchars($row['User_TMD'] ?? '') ?></strong></h6>
                        <strong>ຄວາມຄິດເຫັນຜູ້ຮັບເຄຶ່ອງ</strong>:<br>
                        <form action="action.php" name="form3" method="post">
                            <input hidden="" type="text" name="id" value="<?php echo htmlspecialchars($row['id'] ?? '') ?>">
                            <input type="text" name="TMDRemark" class="form-control" required>
                            <strong>ພາກສ່ວນນຳເຄຶອງໄປແປງ</strong>:
                            <select class="form-control" required name="section">
                                <option value="">****ເລືອກພາກສ່ວນນຳເຄຶອງໄປແປງ****</option>

                                <option value="IT">ພະແນກ IT</option>
                                <option value="ສາງອາໄຫຼ່">ສາງອາໄຫຼ່</option>
                            </select><br>

                            <button type="submit" class="btn btn-info" name="TMDUPDate" style="width: 200px;"><i class="fa-sharp fa-solid fa-forward"></i> Submit</button>

                        </form> <?php } elseif (($row['Remark_TMD'] ?? '') <> '') {
                                ?>
                        <div class="card-header"><b><i class="fa-duotone fa-user-headset"></i>ສາງສຳນັກງານໃຫຍ່</b></div>
                        <div class="card-body">
                            <h6><i class="fa-solid fa-user-vneck-hair"></i> <strong>ຜູ້ກວດສອບ: <?php echo htmlspecialchars($row['User_TMD'] ?? '') ?></strong></h6>
                            <strong><i class="fa-duotone fa-file-exclamation"></i> ຄວາມຄິດເຫັນຜູ້ຮັບເຄຶ່ອງ</strong>: <?= htmlspecialchars($row['Remark_TMD'] ?? '') ?><br>
                            <strong><i class="fa-duotone fa-calendar-days"></i> ວັນທີ່ສົ່ງກວດສອບ</strong>: <?= htmlspecialchars($row['Date_TMD'] ?? '') ?><br>
                            <strong><i class="fa-duotone fa-swords-laser"></i> ພາກສ່ວນນຳເຄຶອງໄປແປງ</strong>: <?= htmlspecialchars($row['Team_fixed'] ?? '') ?>

                        <?php } ?>




                        </div>

                        <?php if (($row['Team_fixed'] ?? '') <> '' and ($row['STT_Fix'] ?? '') == '') { ?>
                            <div class="card-header"><b><i class="fa-duotone fa-screwdriver-wrench"></i> ລາຍລະອຽດຜົນຂອງຮ້ານກວດສ້ອມແປງ</b></div>
                            <div class="card-body">
                                <?php if (($_SESSION["iduser"] == "51" or $_SESSION["iduser"] == '41' or $_SESSION["iduser"] == '378' or $_SESSION["iduser"] == '387' or $_SESSION["iduser"] == '514' or $_SESSION["iduser"] == '423' or $_SESSION["iduser"] == '166' or $_SESSION["iduser"] == '167' or $_SESSION["iduser"] == '168' or $_SESSION["iduser"] == '624' or $_SESSION["iduser"] == '190' or $_SESSION["iduser"] == '197' or $_SESSION["iduser"] == '816' or $_SESSION["iduser"] == '404' or $_SESSION["iduser"] == '718')) { ?>
                                    <h6><i class="fa-solid fa-user-vneck-hair"></i> <strong>ຜູ້ສ້ອມແປງ: <?php echo htmlspecialchars($row['Team_FIx'] ?? '') ?></strong></h6>


                                    <form action="action.php" name="form4" method="post">
                                        <input hidden="" type="text" name="id" value="<?php echo htmlspecialchars($row['id'] ?? '') ?>">

                                        <label><strong><i class="fa-thin fa-square-poll-vertical"></i> ຜົນການສ້ອມແປງ</strong><select name="stt" id="stt" class="form-control" required>
                                            <option value="ໃຊ້ໄດ້ປົກກະຕິ">ໃຊ້ໄດ້ປົກກະຕິ</option>
                                            <option value="ຕາຍ">ຕາຍ ບໍ່ສາມາດສ້ອມແປງໄດ້</option>


                                            </select>
                                        </label><br>
                                        <strong><i class="fa-light fa-sensor-triangle-exclamation"></i> ວີທີການສ້ອມແປງ</strong><input type="text" name="fixRemark" class="form-control" required><br>
                                        <button type="submit" class="btn btn-info" name="CheckUPDate" style="width: 200px;"><i class="fa-sharp fa-solid fa-forward"></i> Submit</button>




                                    </form> <?php } elseif (($row['Date_Fix'] ?? '') <> '' and ($row['STT_Fix'] ?? '') <> '') { ?>
                                    <strong><i class="fa-thin fa-square-poll-vertical"></i> ຜົນການສ້ອມແປງ: </strong> <?php echo htmlspecialchars($row['STT_Fix'] ?? '') ?><br>
                                    <strong><i class="fa-light fa-sensor-triangle-exclamation"></i> ວີທີການສ້ອມແປງ:</strong> <?php echo htmlspecialchars($row['Remark_Fix'] ?? '') ?><br>
                                    <strong><i class="fa-duotone fa-calendar-days"></i> ວັນທີ່ຢືນຢັນກວດສອບ:</strong> <?php echo htmlspecialchars($row['Date_Fix'] ?? '') ?><br>

                                <?php } ?>

                            </div>
                        <?php } ?>

                        <?php if (($row['STT_Fix'] ?? '') <> '') { ?>

                            <div class="card-header"><b><i class="fa-duotone fa-screwdriver-wrench"></i> ຂໍ້ມູນສ້ອມແປງ</b></div>
                            <div class="card-body">



                                <h6> <strong>ສະຖານະສ້ອມແປງ: <?= htmlspecialchars($row['STT_Fix'] ?? '') ?></strong></h6>
                                <h6><strong>ວີທີການສ້ອມແປງ: <?= htmlspecialchars($row['Remark_Fix'] ?? '') ?></strong></h6>

                                <h6><i class="fa-solid fa-user-vneck-hair"></i> <strong>ຜູ້ປະຕິບັດ: <?= htmlspecialchars($row['Team_FIx'] ?? '') ?></strong></h6>

                            </div>









                            <div class="card-header"><b><i class="fa-duotone fa-screwdriver-wrench"></i> ສາງພະແນກສາງຈັດສົ່ງອຸປະກອນ</b></div>
                            <div class="card-body">

                                <?php if (($_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793")  and ($row['Go_Province'] ?? '') == '') { ?>

                                    <form action="action.php" name="form56" method="post">
                                        <label><strong><i class="fa-sharp fa-solid fa-warehouse-full"></i> ສົ່ງໄປສາງ</strong>
                                            <input hidden="" type="text" name="Name_Come" value="<?php echo htmlspecialchars($row['Name_Come'] ?? '') ?>">
                                            <input hidden="" type="text" name="id" value="<?php echo htmlspecialchars($row['id'] ?? '') ?>">
                                            <input hidden="" type="text" name="formpro" value="<?php echo htmlspecialchars($row['Cat_Province'] ?? '') ?>">
                                            <select required name="provinceee" id="provinceee" class="form-control" style="width: 300px;">
                                            <option value="<?php echo htmlspecialchars($row['Cat_Province'] ?? '') ?>"><?php echo htmlspecialchars($row['Cat_Province'] ?? '') ?></option>
                                            <option value="HQ">ສາງພະແນກສາງ</option>
							                <option value="Sanakham">Stock sanakham</option>
							                <option value="MuangNan">Stock meuong nan</option>
                                            </select>
                                        </label>
                                        <label><button type="submit" class="btn btn-info" name="stockUPDate" style="width: 200px;"><i class="fa-solid fa-cloud-plus"></i> Submit / ນຳເຂົ້າສາງ</button></label>
                                    </form> <?php } elseif (($row['Go_Province'] ?? '') <> '') { ?>

                                    <strong><i class="fa-sharp fa-solid fa-warehouse-full"></i> ສົ່ງກັບສາງ:</strong> <?php echo htmlspecialchars($row['Go_Province'] ?? '') ?> <i class="fa-duotone fa-calendar-days"></i> <strong>ວັນທີ່:</strong> <?php echo htmlspecialchars($row['Date_Goto'] ?? '') ?><br>

                                <?php } ?>

                            </div>
                        <?php } ?>





                        <?php if (($row['Remark_TMD'] ?? '') == '' and ($row['Team_fixed'] ?? '') == '' and ($row['Go_Province'] ?? '') == '' and ($row['STT_Fix'] ?? '') == '') { ?>
                            <div class="card-footer"><b>ຜູ້ກຳລັງປະຕິບັດ: ສາງພະແນກສາງສຳນັກງານໃຫຍ່</b></div>

                        <?php } elseif (($row['STT_Fix'] ?? '') <> '' and ($row['Remark_TMD'] ?? '') <> '' and ($row['Team_fixed'] ?? '') <> '' and ($row['Go_Province'] ?? '') <> '') { ?>
                            <div class="card-footer bg-primary text-white">
                                <center> <b>ຂະບວນການ ສຳເລັດ / Success</b> </center>
                            </div>
                        <?php } ?>

                    </div>

                <?php } ?>






                <!--/////////////////////////////////////////////////////////////ຍົກຍ້າຍ//////////////////////////////////////////////////////////////////-->





                <?php if ($S_Status == 'ຍົກຍ້າຍ') { ?>
                    <div class="card">
                        <div class="card-header"> <b><i class="fa-solid fa-user-vneck-hair"></i> ຜູ້ເຮັດເອກະສານ: <?php echo htmlspecialchars($row['UserProvince'] ?? '') ?></b><br><b><i class="fa-duotone fa-earth-americas"></i> ຈາກສາຂາ: <?php echo htmlspecialchars($row['Cat_Province'] ?? '') ?></b></div>
                        <div class="card-body"><a class="right" href="<?php echo htmlspecialchars($row2['picture'] ?? '') ?>" target="_blank"><img class="right" src="<?php echo htmlspecialchars($row2['picture'] ?? '') ?>" style="width:200px;height:120px;"></a>


                            <strong><i class="fa-duotone fa-server"></i> ຊື່ອຸປະກອນ :</strong> <?php echo htmlspecialchars($row['Name_Come'] ?? '') ?><br>
                            <strong><i class="fa-light fa-keyboard"></i> ຊີລຽວນາມເບີ :</strong> <?php echo htmlspecialchars($row['NumBer_S'] ?? '') ?><br>
                            <strong><i class="fa-light fa-map-location-dot"></i> ໃຊ້ຢູ່ສະຖານທີ່ :</strong> <?php echo htmlspecialchars($row['address'] ?? '') ?><br>
                            <strong><i class="fa-light fa-sensor-triangle-exclamation"></i> ໝາຍເຫດ :</strong> <?php echo htmlspecialchars($row['Remark_Pro'] ?? '') ?>

                        </div>
                        <?php if (($row['Remark_TMD'] ?? '') == '' and ($_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793")) { ?>
                            <div class="card-header"><b><i class="fa-duotone fa-user-headset"></i> ສາງພະແນກສາງສຳນັກງານໃຫຍ່</b></div>
                            <div class="card-body">


                                <h6><i class="fa-solid fa-user-vneck-hair"></i> <strong>ຜູ້ກວດສອບ: <?php echo htmlspecialchars($row['User_TMD'] ?? '') ?></strong></h6>
                                <strong>ຄວາມຄິດເຫັນສາງພະແນກສາງສຳນັກງານໃຫຍ່</strong>:<br>
                                <form action="action.php" name="form56" method="post">
                                    <input type="text" name="TMDRemark" class="form-control" required>

                                    <label><strong><i class="fa-sharp fa-solid fa-warehouse-full"></i> ສົ່ງໄປສາງ</strong>
                                        <input hidden="" type="text" name="Name_Come" value="<?php echo htmlspecialchars($row['Name_Come'] ?? '') ?>">
                                        <input hidden="" type="text" name="id" value="<?php echo htmlspecialchars($row['id'] ?? '') ?>">
                                        <input hidden="" type="text" name="formpro" value="<?php echo htmlspecialchars($row['Cat_Province'] ?? '') ?>">
                                        <select required name="provinceee" id="provinceee" class="form-control" style="width: 300px;">
                                        <option value="<?php echo htmlspecialchars($row['Cat_Province'] ?? '') ?>"><?php echo htmlspecialchars($row['Cat_Province'] ?? '') ?></option>
                                        <option value="HQ">ສາງພະແນກສາງ</option>
							            <option value="Sanakham">Stock sanakham</option>
							            <option value="MuangNan">Stock meuong nan</option>
                                        </select>
                                    </label>
                                    <label><button type="submit" class="btn btn-info" name="stockUPDatemove" style="width: 200px;"><i class="fa-solid fa-cloud-plus"></i> Submit / ນຳເຂົ້າສາງ</button></label>
                                </form> <?php } elseif (($row['Remark_TMD'] ?? '') <> '') {
                                        ?>
                                <div class="card-header"><b><i class="fa-duotone fa-user-headset"></i> ສາງພະແນກສາງສຳນັກງານໃຫຍ່</b></div>
                                <div class="card-body">
                                    <h6><i class="fa-solid fa-user-vneck-hair"></i> <strong>ຜູ້ກວດສອບ: <?php echo htmlspecialchars($row['User_TMD'] ?? '') ?></strong></h6>
                                    <strong><i class="fa-duotone fa-file-exclamation"></i> ຄວາມຄິດເຫັນສາງພະແນກສາງສຳນັກງານໃຫຍ່</strong>: <?= htmlspecialchars($row['Remark_TMD'] ?? '') ?><br>
                                    <strong><i class="fa-duotone fa-calendar-days"></i> ວັນທີ່ສົ່ງ</strong>: <?= htmlspecialchars($row['Date_Goto'] ?? '') ?><br>
                                    <strong><i class="fa-sharp fa-solid fa-warehouse-full"></i> ເຂົ້າສາງ</strong>: <?= htmlspecialchars($row['Go_Province'] ?? '') ?>

                                <?php } ?>




                                </div>









                                <?php if (($row['Remark_TMD'] ?? '') == '' and ($row['STT_Fix'] ?? '') == '') { ?>
                                    <div class="card-footer"><b>ຜູ້ກຳລັງປະຕິບັດ: ສາງພະແນກສາງສຳນັກງານໃຫຍ່</b></div>
                                <?php } elseif (($row['Remark_TMD'] ?? '') <> '' and ($row['Go_Province'] ?? '') == '') { ?>
                                    <div class="card-footer"><b>ຜູ້ກຳລັງປະຕິບັດ: ສາງພະແນກສາງສຳນັກງານໃຫຍ່</b></div>
                                <?php } elseif (($row['Remark_TMD'] ?? '') <> '' and ($row['Go_Province'] ?? '') <> '') { ?>
                                    <div class="card-footer bg-primary text-white">
                                        <center> <b>ຂະບວນການ ສຳເລັດ / Success</b> </center>
                                    </div>
                                <?php } ?>
                            </div>

                        <?php } ?>



                        <!--////////////////////////////////////////////////////////ຖອນ//////////////////////////////////-->

                        <?php if ($S_Status == 'ຖອນ') { ?>
                            <div class="card">
                                <div class="card-header"> <b><i class="fa-solid fa-user-vneck-hair"></i> ຜູ້ເຮັດເອກະສານ: <?php echo htmlspecialchars($row['UserProvince'] ?? '') ?></b><br><b><i class="fa-duotone fa-earth-americas"></i> ຈາກສາຂາ: <?php echo htmlspecialchars($row['Cat_Province'] ?? '') ?></b></div>
                                <div class="card-body"><a class="right" href="<?php echo htmlspecialchars($row2['picture'] ?? '') ?>" target="_blank"><img class="right" src="<?php echo htmlspecialchars($row2['picture'] ?? '') ?>" style="width:200px;height:120px;"></a>


                                    <strong><i class="fa-duotone fa-server"></i> ຊື່ອຸປະກອນ :</strong> <?php echo htmlspecialchars($row['Name_Come'] ?? '') ?><br>
                                    <strong><i class="fa-light fa-keyboard"></i> ຊີລຽວນາມເບີ :</strong> <?php echo htmlspecialchars($row['NumBer_S'] ?? '') ?><br>
                                    <strong><i class="fa-light fa-map-location-dot"></i> ໃຊ້ຢູ່ສະຖານທີ່ :</strong> <?php echo htmlspecialchars($row['address'] ?? '') ?><br>
                                    <strong><i class="fa-light fa-sensor-triangle-exclamation"></i> ໝາຍເຫດ :</strong> <?php echo htmlspecialchars($row['Remark_Pro'] ?? '') ?>

                                </div>
                                <?php if (($row['Remark_TMD'] ?? '') == '' and ($_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793")) { ?>
                                    <div class="card-header"><b><i class="fa-duotone fa-user-headset"></i> ສາງພະແນກສາງສຳນັກງານໃຫຍ່</b></div>
                                    <div class="card-body">


                                        <h6><i class="fa-solid fa-user-vneck-hair"></i> <strong>ຜູ້ກວດສອບ: <?php echo htmlspecialchars($row['User_TMD'] ?? '') ?></strong></h6>
                                        <strong>ຄວາມຄິດເຫັນສາງພະແນກສາງສຳນັກງານໃຫຍ່</strong>:<br>
                                        <form action="action.php" name="form56" method="post">
                                            <input type="text" name="TMDRemark" class="form-control" required>

                                            <label><strong><i class="fa-sharp fa-solid fa-warehouse-full"></i> ສົ່ງໄປສາງ</strong>
                                                <input hidden="" type="text" name="Name_Come" value="<?php echo htmlspecialchars($row['Name_Come'] ?? '') ?>">
                                                <input hidden="" type="text" name="id" value="<?php echo htmlspecialchars($row['id'] ?? '') ?>">
                                                <input hidden="" type="text" name="formpro" value="<?php echo htmlspecialchars($row['Cat_Province'] ?? '') ?>">
                                                <select required name="provinceee" id="provinceee" class="form-control" style="width: 300px;">
                                                <option value="<?php echo htmlspecialchars($row['Cat_Province'] ?? '') ?>"><?php echo htmlspecialchars($row['Cat_Province'] ?? '') ?></option>





                                                </select>
                                            </label>
                                            <label><button type="submit" class="btn btn-info" name="stockUPDaterestore" style="width: 500px;"><i class="fa-solid fa-cloud-plus"></i> Submit / ນຳເຂົ້າສາງ <?php echo htmlspecialchars($row['Cat_Province'] ?? '') ?></button></label>
                                        </form> <?php } elseif (($row['Remark_TMD'] ?? '') <> '') {
                                                ?>
                                        <div class="card-header"><b><i class="fa-duotone fa-user-headset"></i> ສາງພະແນກສາງສຳນັກງານໃຫຍ່</b></div>
                                        <div class="card-body">
                                            <h6><i class="fa-solid fa-user-vneck-hair"></i> <strong>ຜູ້ກວດສອບ: <?php echo htmlspecialchars($row['User_TMD'] ?? '') ?></strong></h6>
                                            <strong><i class="fa-duotone fa-file-exclamation"></i> ຄວາມຄິດເຫັນສາງພະແນກສາງສຳນັກງານໃຫຍ່</strong>: <?= htmlspecialchars($row['Remark_TMD'] ?? '') ?><br>
                                            <strong><i class="fa-duotone fa-calendar-days"></i> ວັນທີ່ສົ່ງ</strong>: <?= htmlspecialchars($row['Date_Goto'] ?? '') ?><br>
                                            <strong><i class="fa-sharp fa-solid fa-warehouse-full"></i> ເຂົ້າສາງ</strong>: <?= htmlspecialchars($row['Go_Province'] ?? '') ?>

                                        <?php } ?>




                                        </div>









                                        <?php if (($row['Remark_TMD'] ?? '') == '' and ($row['STT_Fix'] ?? '') == '') { ?>
                                            <div class="card-footer"><b>ຜູ້ກຳລັງປະຕິບັດ: ສາງພະແນກສາງສຳນັກງານໃຫຍ່</b></div>
                                        <?php } elseif (($row['Remark_TMD'] ?? '') <> '' and ($row['Go_Province'] ?? '') == '') { ?>
                                            <div class="card-footer"><b>ຜູ້ກຳລັງປະຕິບັດ: ສາງພະແນກສາງສຳນັກງານໃຫຍ່</b></div>
                                        <?php } elseif (($row['Remark_TMD'] ?? '') <> '' and ($row['Go_Province'] ?? '') <> '') { ?>
                                            <div class="card-footer bg-primary text-white">
                                                <center> <b>ຂະບວນການ ສຳເລັດ / Success</b> </center>
                                            </div>
                                        <?php } ?>
                                    </div>

                                <?php } ?>


                            </div>

</body>

</html>