<?php
function connectDB() {
    $host = "78.38.35.219";
    $dbname = "401463120";
    $user = "401463120";
    $password = "123456";
    
    try {
        $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
        return null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_phone'], $_POST['patient_id'], $_POST['new_phone'])) {
    $pdo = connectDB();
    if ($pdo) {
        try {
            $edit_phone = $_POST['edit_phone'];
            $patient_id = $_POST['patient_id'];
            $new_phone = $_POST['new_phone'];

            $update_stmt = $pdo->prepare("UPDATE \"patientFolder\".patient_phone SET phone = :new_phone WHERE phone = :edit_phone AND patient_id = :patient_id");
            $update_stmt->execute([':new_phone' => $new_phone, ':edit_phone' => $edit_phone, ':patient_id' => $patient_id]);

            echo "Phone number updated successfully.";
            header('Location: a.php'); 
        exit;
        } catch (PDOException $e) {
            echo "Update error: " . $e->getMessage();
        }
    }
} else {
    if (isset($_GET['edit_phone'], $_GET['patient_id'])) {
        $edit_phone = $_GET['edit_phone'];
        $patient_id = $_GET['patient_id'];
    } else {
        echo "Phone number not found.";
        exit;
    }
}
?>

<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Phone</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        direction: ltr;
        text-align: left;
    }
    form {
        width: 60%;
        margin: 0 auto;
        font-size: 16px;
        background-color: lightcyan; /* رنگ پس‌زمینه فرم */
        border: 1px solid mediumblue; /* حاشیه فرم */
        padding: 20px; /* فضای داخلی فرم */
        
    }
    div {
        margin-bottom: 15px; /* فاصله بین بخش‌ها */
    }
    label {
        display: inline-block;
        width: 160px;
        font-weight: bold;
        color: midnightblue; /* رنگ متن لیبل */
    }
    input[type="text"], input[type="date"] {
        width: calc(100% - 160px);
        padding: 10px;
        border: 1px solid #ccc;
    }
    input[type="checkbox"] {
        margin-left: 150px;
        accent-color: #48D1CC; /* رنگ چک باکس */
    }
    button {
        margin-left: 150px;
        padding: 10px 20px;
        background-color: steelblue; /* رنگ دکمه */
        color: white;
        border: none;
        cursor: pointer;
        font-size: 16px;
    }
    </style>
</head>
<body>
    <h1>Edit Phone Number</h1>
    <form method="POST" action="">
        <input type="hidden" name="edit_phone" value="<?= $edit_phone; ?>">
        <input type="hidden" name="patient_id" value="<?= $patient_id; ?>">
        <label for="new_phone">New Phone Number:</label>
        <input type="text" name="new_phone" id="new_phone" required>
        <button type="submit">Update</button>
    </form>
</body>
</html>
