<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Request Manager - SNK</title>
    <link rel="stylesheet" href="css/pages/pr-manager.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>🛒 Purchase Request Manager</h1>
            <p class="subtitle">SNK Product Purchase Management System</p>
        </header>

        <?php
        // Include the existing connection configuration
        require_once __DIR__ . '/../includes/conn.php';

        // Fetch purchase requests from database (using 'request' table)
        $prData = [];
        $sql = "SELECT id, Items AS pr_number, dateRe AS pr_date, Vendor AS from_dept,
                       Use_For AS to_dept, Picture AS product_img, Item_code AS item_code,
                       Unit AS unit, Give AS quantity, OA_Out AS price, S_Status AS status,
                       Remark AS notes
                FROM request ORDER BY dateRe DESC, id DESC LIMIT 10";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $prData[] = $row;
            }
        } else {
            // Default sample data if no records
            $prData = [
                [
                    'pr_number' => 'GR-16072026-0009',
                    'pr_date' => '2026-07-16',
                    'from_dept' => 'HQ',
                    'to_dept' => 'MMN',
                    'product_img' => '',
                    'item_code' => '',
                    'unit' => '5kg/roll',
                    'quantity' => '149',
                    'price' => '150',
                    'status' => 'pending',
                    'notes' => '',
                    'id' => 0
                ]
            ];
        }
        $conn->close();

        // Calculate summary statistics
        $totalPRs = count($prData);
        $pendingCount = 0;
        $approvedCount = 0;
        $rejectedCount = 0;

        foreach ($prData as $pr) {
            $status = isset($pr['status']) ? $pr['status'] : 'pending';
            if ($status == '1' || $status == 'pending' || $status == 'ສ້ອມແປງ') $pendingCount++;
            if ($status == 'ຖ້ອນ' || $status == 'ຕ້ອງຮັບ') $approvedCount++;
            if ($status == 'ຖອນ') $rejectedCount++;
        }
        ?>

        <div class="card">
            <h2>📋 Create New Purchase Request</h2>
            <form id="prForm" class="pr-form" method="POST" action="pr-manager.php">
                <div class="form-group">
                    <label for="prNumber">PR Number</label>
                    <input type="text" id="prNumber" name="prNumber" placeholder="e.g., GR-16072026-0009" required>
                </div>

                <div class="form-group">
                    <label for="prDate">PR Date</label>
                    <input type="date" id="prDate" name="prDate" required>
                </div>

                <div class="form-group">
                    <label for="from">From (Department)</label>
                    <input type="text" id="from" name="from" value="HQ" required>
                </div>

                <div class="form-group">
                    <label for="to">To</label>
                    <input type="text" id="to" name="to" value="MMN" required>
                </div>

                <div class="form-group">
                    <label for="senderName">Sender Name</label>
                    <input type="text" id="senderName" name="senderName" placeholder="Nikkee" required>
                </div>

                <div class="form-group">
                    <label for="senderCompany">Sender Company</label>
                    <input type="text" id="senderCompany" name="senderCompany" placeholder="WB" required>
                </div>

                <div class="form-group">
                    <label for="senderPhone">Sender Phone</label>
                    <input type="tel" id="senderPhone" name="senderPhone" placeholder="+856 20 29 611 111" required>
                </div>

                <div class="form-group">
                    <label for="senderEmail">Sender Email</label>
                    <input type="email" id="senderEmail" name="senderEmail" placeholder="ls.starch@lslao.com.la" required>
                </div>

                <div class="form-group full-width">
                    <label for="productName">Product Name</label>
                    <input type="text" id="productName" name="productName" placeholder="Product description" required>
                </div>

                <div class="form-group">
                    <label for="productUnit">Unit</label>
                    <input type="text" id="productUnit" name="productUnit" value="5kg/roll" required>
                </div>

                <div class="form-group">
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" placeholder="149" required>
                </div>

                <div class="form-group">
                    <label for="price">Price (RMB)</label>
                    <input type="number" id="price" name="price" placeholder="150" step="0.01" required>
                </div>

                <div class="form-group full-width">
                    <label for="notes">Notes / Image References</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Additional notes, special instructions..."></textarea>
                </div>

                <div class="form-group full-width">
                    <button type="submit" class="btn btn-primary" name="action" value="submit">Submit Purchase Request</button>
                    <button type="reset" class="btn btn-secondary">Reset Form</button>
                </div>
            </form>

            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'submit') {
                $prNumber = $_POST['prNumber'];
                $prDate = $_POST['prDate'];
                $from = $_POST['from'];
                $to = $_POST['to'];
                $senderName = $_POST['senderName'];
                $senderCompany = $_POST['senderCompany'];
                $senderPhone = $_POST['senderPhone'];
                $senderEmail = $_POST['senderEmail'];
                $productName = $_POST['productName'];
                $productUnit = $_POST['productUnit'];
                $quantity = $_POST['quantity'];
                $price = $_POST['price'];
                $notes = $_POST['notes'];

                echo '<div class="card" style="background: #d4edda; border-color: #c3e6cb;">
                    <h2 style="color: #155724;">✅ Purchase Request Submitted Successfully!</h2>
                    <p><strong>PR Number:</strong> ' . htmlspecialchars($prNumber) . '</p>
                    <p><strong>Product:</strong> ' . htmlspecialchars($productName) . '</p>
                    <p><strong>Quantity:</strong> ' . htmlspecialchars($quantity) . '</p>
                    <p><strong>Total: </strong> ' . htmlspecialchars($price * $quantity) . ' RMB</p>
                </div>';
            }
            ?>
        </div>

        <div class="card">
            <h2>📊 Recent Purchase Requests</h2>
            <table class="pr-table">
                <thead>
                    <tr>
                        <th>PR Number</th>
                        <th>Date</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Product</th>
                        <th>Unit</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prData as $pr): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pr['pr_number']); ?></td>
                        <td><?php echo htmlspecialchars($pr['pr_date']); ?></td>
                        <td><?php echo htmlspecialchars($pr['from_dept']); ?></td>
                        <td><?php echo htmlspecialchars($pr['to_dept']); ?></td>
                        <td><?php echo htmlspecialchars($pr['Items']); ?></td>
                        <td><?php echo htmlspecialchars($pr['unit']); ?></td>
                        <td><?php echo htmlspecialchars($pr['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($pr['price']); ?> RMB</td>
                        <td>
                            <?php
                            $status = $pr['status'];
                            if ($status == '1' || $status == 'pending' || $status == 'ສ້ອມແປງ' || $status == '') {
                            ?>
                                <span class="status-badge status-pending">Pending</span>
                            <?php } elseif ($status == 'ຖ້ອນ' || $status == 'ຕ້ອງຮັບ' || $status == 'approved') { ?>
                                <span class="status-badge status-approved">Approved</span>
                            <?php } else { ?>
                                <span class="status-badge status-rejected">Rejected</span>
                            <?php } ?>
                        </td>
                        <td>
                            <form style="display:inline;" method="POST" action="pr-manager.php">
                                <button type="submit" name="action" value="view" class="btn btn-secondary" style="padding: 6px 12px; font-size: 14px;">View</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="summary-cards">
            <div class="summary-card">
                <div class="number"><?php echo $totalPRs; ?></div>
                <div class="label">Total PRs</div>
            </div>
            <div class="summary-card">
                <div class="number"><?php echo $pendingCount; ?></div>
                <div class="label">Pending Approval</div>
            </div>
            <div class="summary-card">
                <div class="number"><?php echo $approvedCount; ?></div>
                <div class="label">Approved</div>
            </div>
            <div class="summary-card">
                <div class="number"><?php echo $rejectedCount; ?></div>
                <div class="label">Rejected</div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('prDate').valueAsDate = new Date();
    </script>
</body>
</html>
