<!DOCTYPE html>
<html lang="en">
<head>
    <h1>EDIT INFORMATION:</h1>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EDIT INFORMATION</title>
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
        width: 150px;
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
    <?php
    function DB_Connection() {
        $host = "78.38.35.219";
        $dbname = "??";
        $user = "??";
        $password = "??";
        
        try {
            $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            return null;
        }
    }

    $pdo = DB_Connection();
    if ($pdo && isset($_GET['edit_id'])) {
        try {
            $edit_id = $_GET['edit_id'];
            $stmt = $pdo->prepare("SELECT * FROM \"patientFolder\".patient_info WHERE patient_id = :id");
            $stmt->execute([':id' => $edit_id]);
            $patient = $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo "Fetch error: " . $e->getMessage();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
        try {
            $edit_id = $_POST['edit_id'];
            $update_stmt = $pdo->prepare("UPDATE \"patientFolder\".patient_info SET 
                first_name = :first_name,
                last_name = :last_name,
                birthdate = :birthdate,
                file_creation_date = :file_creation_date,
                address = :address,
                has_cancer = :has_cancer,
                has_kidney = :has_kidney,
                has_pulmonary = :has_pulmonary,
                has_cardiovascular = :has_cardiovascular,
                has_neurology = :has_neurology,
                has_physicaldisability = :has_physicaldisability
                WHERE patient_id = :id");
            $update_stmt->execute([
                ':first_name' => $_POST['first_name'],
                ':last_name' => $_POST['last_name'],
                ':birthdate' => $_POST['birthdate'],
                ':file_creation_date' => $_POST['file_creation_date'],
                ':address' => $_POST['address'],
                ':has_cancer' => isset($_POST['has_cancer']) ? 1 : 0,
                ':has_kidney' => isset($_POST['has_kidney']) ? 1 : 0,
                ':has_pulmonary' => isset($_POST['has_pulmonary']) ? 1 : 0,
                ':has_cardiovascular' => isset($_POST['has_cardiovascular']) ? 1 : 0,
                ':has_neurology' => isset($_POST['has_neurology']) ? 1 : 0,
                ':has_physicaldisability' => isset($_POST['has_physicaldisability']) ? 1 : 0,
                ':id' => $edit_id,
            ]);
            echo "Patient information updated successfully.";
            header('Location: a.php'); 
        exit;
        } catch (PDOException $e) {
            echo "Update error: " . $e->getMessage();
        }
    }
    ?>

    <?php if (isset($patient)): ?>
        <form method="POST" action="">
            <input type="hidden" name="edit_id" value="<?= $patient->patient_id; ?>">
            <div>
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" value="<?= $patient->first_name; ?>" required>
            </div>
            <div>
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" value="<?= $patient->last_name; ?>" required>
            </div>
            <div>
                <label for="birthdate">Birthdate:</label>
                <input type="date" id="birthdate" name="birthdate" value="<?= $patient->birthdate; ?>" required>
            </div>
            <div>
                <label for="file_creation_date">File Creation Date:</label>
                <input type="date" id="file_creation_date" name="file_creation_date" value="<?= $patient->file_creation_date; ?>" required>
            </div>
            <div>
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="<?= $patient->address; ?>" required>
            </div>
            <div>
                <label for="has_cancer">Has Cancer:</label>
                <input type="checkbox" id="has_cancer" name="has_cancer" value="1" <?= $patient->has_cancer ? 'checked' : ''; ?>>
            </div>
            <div>
                <label for="has_kidney">Has Kidney:</label>
                <input type="checkbox" id="has_kidney" name="has_kidney" value="1" <?= $patient->has_kidney ? 'checked' : ''; ?>>
            </div>
            <div>
                <label for="has_pulmonary">Has Pulmonary:</label>
                <input type="checkbox" id="has_pulmonary" name="has_pulmonary" value="1" <?= $patient->has_pulmonary ? 'checked' : ''; ?>>
            </div>
            <div>
                <label for="has_cardiovascular">Has Cardiovascular:</label>
                <input type="checkbox" id="has_cardiovascular" name="has_cardiovascular" value="1" <?= $patient->has_cardiovascular ? 'checked' : ''; ?>>
            </div>
            <div>
                <label for="has_neurology">Has Neurology:</label>
                <input type="checkbox" id="has_neurology" name="has_neurology" value="1" <?= $patient->has_neurology ? 'checked' : ''; ?>>
            </div>
            <div>
                <label for="has_physicaldisability">Has Physical Disability:</label>
                <input type="checkbox" id="has_physicaldisability" name="has_physicaldisability" value="1" <?= $patient->has_physicaldisability ? 'checked' : ''; ?>>
            </div>
            <button type="submit">Update</button>
        </form>
    <?php else: ?>
        <p>No patient found.</p>
    <?php endif; ?>
    <a href="a.php">Back to Patient List</a>
</body>
</html>
