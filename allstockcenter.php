<?php
require_once __DIR__ . '/includes/conn.php';
@session_start();
if ($_SESSION["user"] == "" or $_SESSION["iduser"] == "") {
    echo "<script>window.location = 'index.php';</script>";
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <link rel="shortcut icon" href="image/logoETL.jpg">

    <meta charset="utf-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="js/pro.min.js">
    <link rel="stylesheet" href="css/all.css">
    <link rel="stylesheet" href="css/all.min.css">

    <title>ສາງອຸປະກອນ</title>
    <!-- Favicon-->

    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="css/styles.css" rel="stylesheet" />
    <style type="text/css">
        body,
        td,
        th {
            font-family: "Phetsarath OT";
        }

        .navbarr {


            position: fixed;

        }

        .buttonright {
            float: right;
        }

        .buttonleft {
            float: left;
        }

        .cooloorr {
            background-image: linear-gradient(to top, #accbee 0%, #e7f0fd 100%);
        }

        .right {
            float: right;
        }

        html {
            scroll-behavior: smooth;
        }
    </style>


</head>

<body>

    <!-- Sidebar-->

    <!-- Page content wrapper-->

    <!-- Top navigation-->

    <!-- Page content-->

            <table class="table table-bordered  ">
            <thead>
                <tr class="text-primary cooloorr" style="font-size: 15px;">
                    <th width="10%">Item Code</th>
                    <th width="20%">Items</th>
                    <!-- <th>ພາກສ່ວນ</th>-->
                    <th width="10%" data-translate="store_info">ຂໍ້ມູນຮ້ານທີ່ຈັດຊື້</th>

                    <th width="20%" data-translate="stock_quantity">ຈຳນວນໃນ Stoc</th>
                    <th width="20%" data-translate="hq_stock_quantity">ຈຳນວນສາງສຳນັກງານໃຫຍ່</th>

                    <th width="20%" data-translate="equipment_image">ຮູບອູປະກອນ</th>


                </tr>
            </thead>

            <tbody>
                <?php
                $conns = $conn;
                $input = $_POST["input"] ?? '';
                $prostock = $_POST['province'] ?? '';
                $Section = $_POST["Section"] ?? '';
                $resutl = $_POST["resutl"] ?? '';
                $prostock_province = $_POST['province'] ?? '';
                if ($resutl == 'all') {
                    if ($prostock == 'TMD' and  $Section <> 'ທຸກພາກສ່ວນ' and $input == '') {
                        $sql2 = "SELECT*FROM stock
		where stock.Section='$Section' ";
                    } elseif ($prostock == 'TMD' and  $Section <> 'ທຸກພາກສ່ວນ' and $input <> '') {
                        $sql2 = "SELECT*FROM stock
		where stock.Section='$Section' and stock.Items like '$input%'";
                    } elseif ($prostock == 'TMD' and  $Section == 'ທຸກພາກສ່ວນ' and $input <> '') {
                        $sql2 = "SELECT*FROM stock
		where  stock.Items like '%$input%'";
                    } elseif ($prostock == 'TMD' and  $Section == 'ທຸກພາກສ່ວນ' and $input == '') {
                        $sql2 = "SELECT*FROM stock
";
                    }
                } else {

                    if ($prostock == 'TMD' and  $Section <> 'ທຸກພາກສ່ວນ' and $input == '') {
                        $sql2 = "SELECT*FROM stock
		where stock.Section='$Section' and stock.Unit $resutl  ";
                    } elseif ($prostock == 'TMD' and  $Section <> 'ທຸກພາກສ່ວນ' and $input <> '') {
                        $sql2 = "SELECT*FROM stock
		where stock.Section='$Section' and stock.Items like '$input%' and stock.Unit $resutl ";
                    } elseif ($prostock == 'TMD' and  $Section == 'ທຸກພາກສ່ວນ' and $input <> '') {
                        $sql2 = "SELECT*FROM stock
		where  stock.Items like '%$input%' and stock.Unit $resutl";
                    } elseif ($prostock == 'TMD' and  $Section == 'ທຸກພາກສ່ວນ' and $input == '') {
                        $sql2 = "SELECT*FROM stock 
		where stock.Unit $resutl ";
                    }
                }

                if ($resutl == 'all') {
                    if ($prostock_province <> 'TMD' and  $Section <> 'ທຸກພາກສ່ວນ' and $input == '') {
                        $sql2 = "SELECT*FROM stock_province
		where stock_province.Section='$Section' and Provinces ='$prostock_province' ";
                    } elseif ($prostock_province <> 'TMD' and  $Section <> 'ທຸກພາກສ່ວນ' and $input <> '') {
                        $sql2 = "SELECT*FROM stock_province
		where stock_province.Section='$Section' and stock_province.Items like '$input%' and Provinces ='$prostock_province'";
                    } elseif ($prostock_province <> 'TMD' and  $Section == 'ທຸກພາກສ່ວນ' and $input <> '') {
                        $sql2 = "SELECT*FROM stock_province
		where  stock_province.Items like '%$input%' and Provinces ='$prostock_province'";
                    } elseif ($prostock_province <> 'TMD' and  $Section == 'ທຸກພາກສ່ວນ' and $input == '') {
                        $sql2 = "SELECT*FROM stock_province 
		where Provinces ='$prostock_province'
";
                    }
                } else {

                    if ($prostock_province <> 'TMD' and  $Section <> 'ທຸກພາກສ່ວນ' and $input == '') {
                        $sql2 = "SELECT*FROM stock_province
		where stock_province.Section='$Section' and stock_province.Unit $resutl and Provinces ='$prostock_province'  ";
                    } elseif ($prostock_province <> 'TMD' and  $Section <> 'ທຸກພາກສ່ວນ' and $input <> '') {
                        $sql2 = "SELECT*FROM stock_province
		where stock_province.Section='$Section' and stock_province.Items like '$input%' and stock_province.Unit $resutl and Provinces ='$prostock_province' ";
                    } elseif ($prostock_province <> 'TMD' and  $Section == 'ທຸກພາກສ່ວນ' and $input <> '') {
                        $sql2 = "SELECT*FROM stock_province
		where  stock_province.Items like '%$input%' and stock_province.Unit $resutl and Provinces ='$prostock_province'";
                    } elseif ($prostock_province <> 'TMD' and  $Section == 'ທຸກພາກສ່ວນ' and $input == '') {
                        $sql2 = "SELECT*FROM stock_province 
		where stock_province.Unit $resutl and Provinces ='$prostock_province' ";
                    }
                }

                mysqli_set_charset($conns, "utf8");
                @$result23 = $conns->query($sql2);
                while (@$row23 = $result23->fetch_assoc()) {
                    $Itemms = $row23['Items'];
                    $id = $row23['id'];

                    if ($row23['Unit'] <= '5' and   ($row23['Stock_TMD'] ?? 0) <= '5') {

                        $bgcolor = "#F0C0C1";
                        $cl = "red";
                    } else {
                        $bgcolor = "#FFFFFF";
                        $cl = "black";
                    }

                ?>
                    <tr bgcolor="<?php echo $bgcolor ?>" style="font-size: 13px;">
                        <td width="10%"><strong><?php echo $row23['Item_code'] ?></strong></td>
                        <td width="20%">
                            <p>
                                    <strong><?php echo $row23['Items'] ?></p><i class="fa-solid fa-network-wired"></i> ອຸປະກອນ: <?php echo $row23['Use_For'] ?><br><i class="fa-duotone fa-layer-group"></i> ໃຊ້ກັບກຸ່ມ: <?php echo $row23['Groupp'] ?></strong>
                        </td>
                        <!--<td><?php echo $row23['Section'] ?></td>-->
                        <td width="10%"><strong><?php echo $row23['Vendor'] ?></strong></td>



                        <td width="20%" align="center" style="color: <?php echo $cl ?>"><strong><?php echo $row23['Unit'] ?> <?php echo $row23['Type'] ?></strong></td>
                        <td width="20%" align="center" style="color: <?php echo $cl ?>"><strong><?php echo $row23['Stock_TMD'] ?? '' ?> <?php echo $row23['Type'] ?></strong></td>


                        <td width="20%"><a href="<?php echo $row23['picture'] ?>" target="_blank"><img src="<?php echo $row23['picture'] ?>" style="width:128px;height:100px;"></a></td>

                    </tr>
            </tbody>
        <?php } ?>
        </table>


   









    <!-- Bootstrap core JS-->

    <!-- Core theme JS-->
    <script src="js/scripts.js"></script>

    <!-- Translate -->
    <script src="translate/lang.js"></script>
</body>

</html>