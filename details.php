<?php
function DB_Connection() {
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
$pdo = DB_Connection();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($pdo) {
        if (isset($_POST['edit_echo_id'])) {
            try {
                $edit_echo_id = $_POST['edit_echo_id'];
                $edit_stmt = $pdo->prepare("UPDATE \"patientFolder\".echo_info SET 
                    echo_date = :echo_date,
                    rate = :rate,
                    rhythm = :rhythm,
                    sbp = :sbp,
                    dbp = :dbp,
                    diagnose = :diagnose
                    WHERE echo_id = :id");
                $edit_stmt->execute([
                    ':echo_date' => $_POST['echo_date'],
                    ':rate' => $_POST['rate'],
                    ':rhythm' => $_POST['rhythm'],
                    ':sbp' => $_POST['sbp'],
                    ':dbp' => $_POST['dbp'],
                    ':diagnose' => $_POST['diagnose'],
                    ':id' => $edit_echo_id,
                ]);
            } catch (PDOException $e) {
                echo "Edit error: " . $e->getMessage();
            }
        } elseif (isset($_POST['new_echo'])) {
            try {
                $new_echo_stmt = $pdo->prepare("INSERT INTO \"patientFolder\".echo_info (echo_date, rate, rhythm, sbp, dbp, diagnose) 
                    VALUES (:echo_date, :rate, :rhythm, :sbp, :dbp, :diagnose) RETURNING echo_id");
                $new_echo_stmt->execute([
                    ':echo_date' => $_POST['echo_date'],
                    ':rate' => $_POST['rate'],
                    ':rhythm' => $_POST['rhythm'],
                    ':sbp' => $_POST['sbp'],
                    ':dbp' => $_POST['dbp'],
                    ':diagnose' => $_POST['diagnose'],
                ]);
                $new_echo_id = $new_echo_stmt->fetch(PDO::FETCH_OBJ)->echo_id;

                $new_patient_echo_stmt = $pdo->prepare("INSERT INTO \"patientFolder\".patient_echo (patient_id, echo_id) VALUES (:patient_id, :echo_id)");
                $new_patient_echo_stmt->execute([
                    ':patient_id' => $_POST['patient_id'],
                    ':echo_id' => $new_echo_id,
                ]);
            } catch (PDOException $e) {
                echo "Add echo error: " . $e->getMessage();
            }
        }
    }
}

if (isset($_GET['patient_id'])) {
    $patient_id = $_GET['patient_id'];
    if ($pdo) {
        try {
            // detail:
            $stmt = $pdo->prepare("
                SELECT ei.echo_id, ei.echo_date, ei.rate, ei.rhythm, ei.sbp, ei.dbp, ei.diagnose
                FROM \"patientFolder\".patient_info pi
                JOIN \"patientFolder\".patient_echo pe ON pi.patient_id = pe.patient_id
                JOIN \"patientFolder\".echo_info ei ON pe.echo_id = ei.echo_id
                WHERE pi.patient_id = :patient_id
            ");
            $stmt->execute([':patient_id' => $patient_id]);
            $echos = $stmt->fetchAll(PDO::FETCH_OBJ);

            // avg,min,max,count 
            $aggregate_stmt = $pdo->prepare("
                SELECT
                    AVG(rate) AS avg_rate,
                    MIN(rate) AS min_rate,
                    MAX(rate) AS max_rate,
                    COUNT(*) AS count_echoes
                FROM \"patientFolder\".patient_echo pe
                JOIN \"patientFolder\".echo_info e ON pe.echo_id = e.echo_id
                WHERE pe.patient_id = :patient_id
            ");
            $aggregate_stmt->execute([':patient_id' => $patient_id]);
            $aggregate = $aggregate_stmt->fetch(PDO::FETCH_OBJ);

        } catch (PDOException $e) {
            echo "Fetch error: " . $e->getMessage();
            $echos = [];
            $aggregate = null;
        }
    } else {
        $echos = [];
        $aggregate = null;
    }
} else {
    echo "No patient ID provided.";
    $echos = [];
    $aggregate = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PATIENT ECHOES</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            direction: ltr;
            text-align: left;
        }
        table {
            width: 80%;
            border-collapse: collapse;
            margin: 0 auto;
            font-size: 16px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: mediumaquamarine;
        }
        .edit-column {
            background-color: palegreen;
        }
        .id-column {
            background-color: lightcyan;
        }
        .new-row {
            background-color: lightpink; 
        }
        .stats {
            width: 80%;
            margin: 0 auto;
            border-collapse: collapse;
            font-size: 16px;
            margin-top: 20px;
        }
        .stats th, .stats td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        .stats th {
            background-color: darkseagreen;
        }
        .print-button {
            margin: 20px auto;
            display: block;
            width: 100px;
            padding: 10px;
            background-color: mediumvioletred;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }
        .print-button:hover {
            background-color: mediumvioletred;
        }
    </style>
    <script>
        function printPage() {
            window.print();
        }
    </script>
</head>
<body>
    <h1>PATIENT ECHOES:</h1>
    <h2>patient_id= <?= $patient_id?></h2>
    <table>
        <tr>
            <th>echo_id</th>
            <th>echo_date</th>
            <th>rate</th>
            <th>rhythm</th>
            <th>sbp</th>
            <th>dbp</th>
            <th>diagnose</th>
            <th>Edit</th>
        </tr>
        <?php foreach ($echos as $echo): ?>
            <tr>
                <td class="id-column"><?= $echo->echo_id; ?></td>
                <td><?= $echo->echo_date; ?></td>
                <td><?= $echo->rate; ?></td>
                <td><?= $echo->rhythm; ?></td>
                <td><?= $echo->sbp; ?></td>
                <td><?= $echo->dbp; ?></td>
                <td><?= $echo->diagnose; ?></td>
                <td class="edit-column">
                    <form method="POST" action="">
                        <input type="hidden" name="edit_echo_id" value="<?= $echo->echo_id; ?>">
                        <input type="hidden" name="patient_id" value="<?= $patient_id; ?>">
                        <input type="date" name="echo_date" value="<?= $echo->echo_date; ?>" required>
                        <input type="text" name="rate" value="<?= $echo->rate; ?>" required>
                        <input type="text" name="rhythm" value="<?= $echo->rhythm; ?>" required>
                        <input type="text" name="sbp" value="<?= $echo->sbp; ?>" required>
                        <input type="text" name="dbp" value="<?= $echo->dbp; ?>" required>
                        <input type="text" name="diagnose" value="<?= $echo->diagnose; ?>" required>
                        <button type="submit">Edit</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <!-- Form for adding new echo -->
        <tr class="new-row">
            <form method="POST" action="">
                <td>New</td>
                <td><input type="date" name="echo_date" required></td>
                <td><input type="text" name="rate" required></td>
                <td><input type="text" name="rhythm" required></td>
                <td><input type="text" name="sbp" required></td>
                <td><input type="text" name="dbp" required></td>
                <td><input type="text" name="diagnose" required></td>
                <td colspan="2">
                    <input type="hidden" name="patient_id" value="<?= $patient_id; ?>">
                    <button type="submit" name="new_echo">Add</button>
                </td>
            </form>
        </tr>
    </table>
    <!-- Table for aggregate -->
    <?php if ($aggregate): ?>
        <table class="stats">
            <tr>
                <th>Average Rate</th>
                <th>Minimum Rate</th>
                <th>Maximum Rate</th>
                <th>Total Echoes</th>
            </tr>
            <tr>
                <td><?= number_format($aggregate->avg_rate, 2); ?></td>
                <td><?= $aggregate->min_rate; ?></td>
                <td><?= $aggregate->max_rate; ?></td>
                <td><?= $aggregate->count_echoes; ?></td>
            </tr>
        </table>
    <?php endif; ?>
    <button class="print-button" onclick="printPage()">Print</button>
    <a href="a.php">Back to Patient List</a>
</body>
</html>
