<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Request Manager - SNK</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .header h1 {
            color: #333;
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        .header .subtitle {
            color: #666;
            font-size: 1.1em;
        }
        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .card h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        .pr-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        .form-group input, .form-group select, .form-group textarea {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .full-width {
            grid-column: 1 / -1;
        }
        .btn {
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            background: #f0f0f0;
            color: #555;
        }
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        .pr-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .pr-table th, .pr-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .pr-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        .pr-table tr:hover {
            background: #f8f9fa;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .summary-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .summary-card .number {
            font-size: 2.5em;
            font-weight: 700;
            color: #667eea;
        }
        .summary-card .label {
            color: #666;
            margin-top: 10px;
        }
        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8em;
            }
            .pr-form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>🛒 Purchase Request Manager</h1>
            <p class="subtitle">SNK Product Purchase Management System</p>
        </header>

        <?php
        // Include the existing connection configuration
        require_once __DIR__ . '/conn.php';

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