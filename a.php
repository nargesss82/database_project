<!DOCTYPE html>
<html lang="en">

<head>
    <h1>PATIENTS LIST:</h1>
    <h4>Click on the patient_id to view the patient's echoes</h4>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PATIENTS LIST</title>
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

        th,
        td {
            border: 1px solid hwb(198 1% 64%);
            padding: 7px;
            text-align: center;
        }

        th {
            background-color: powderblue;
        }

        .delete-column {
            background-color: pink;
        }

        .edit-column {
            background-color: palegreen;
        }

        .id-column {
            background-color: mediumturquoise;
        }

        .phone-column {
            background-color: lavender;
        }

        .new-row {
            background-color: lightblue;
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
    <?php
    function DB_Connection()
    {
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
            if (isset($_POST['delete_id'])) {
                try {
                    $delete_id = $_POST['delete_id'];
                    $delete_patient_echo_stmt = $pdo->prepare("DELETE FROM \"patientFolder\".patient_echo WHERE patient_id = :id");
                    $delete_patient_echo_stmt->execute([':id' => $delete_id]);
                    $delete_echo_info_stmt = $pdo->prepare("DELETE FROM \"patientFolder\".echo_info WHERE echo_id IN (SELECT echo_id FROM \"patientFolder\".patient_echo WHERE patient_id = :id)");
                    $delete_echo_info_stmt->execute([':id' => $delete_id]);
                    $delete_stmt = $pdo->prepare("DELETE FROM \"patientFolder\".patient_info WHERE patient_id = :id");
                    $delete_stmt->execute([':id' => $delete_id]);
                } catch (PDOException $e) {
                    echo "Delete error: " . $e->getMessage();
                }
            } elseif (isset($_POST['delete_phone'])) {
                try {
                    $delete_phone = $_POST['delete_phone'];
                    $patient_id = $_POST['patient_id'];
                    $delete_phone_stmt = $pdo->prepare("DELETE FROM \"patientFolder\".patient_phone WHERE phone = :phone AND patient_id = :patient_id");
                    $delete_phone_stmt->execute([':phone' => $delete_phone, ':patient_id' => $patient_id]);
                } catch (PDOException $e) {
                    echo "Delete phone error: " . $e->getMessage();
                }
            } elseif (isset($_POST['new_phone'], $_POST['patient_id'])) {
                try {
                    $new_phone = $_POST['new_phone'];
                    $patient_id = $_POST['patient_id'];
                    $add_phone_stmt = $pdo->prepare("INSERT INTO \"patientFolder\".patient_phone (phone, patient_id) VALUES (:phone, :patient_id)");
                    $add_phone_stmt->execute([':phone' => $new_phone, ':patient_id' => $patient_id]);
                } catch (PDOException $e) {
                    echo "Add phone error: " . $e->getMessage();
                }
            } elseif (isset($_POST['new_patient'])) {
                try {
                    $new_patient_stmt = $pdo->prepare("INSERT INTO \"patientFolder\".patient_info (first_name, last_name, birthdate, file_creation_date, address, has_cancer, has_kidney, has_pulmonary, has_cardiovascular, has_neurology, has_physicaldisability) 
                        VALUES (:first_name, :last_name, :birthdate, :file_creation_date, :address, :has_cancer, :has_kidney, :has_pulmonary, :has_cardiovascular, :has_neurology, :has_physicaldisability)");
                    $new_patient_stmt->execute([
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
                    ]);
                } catch (PDOException $e) {
                    echo "Add patient error: " . $e->getMessage();
                }
            }
        }
    }

    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT p.*, 
                                    STRING_AGG(pp.phone, ', ') AS phone_numbers
                                   FROM \"patientFolder\".patient_info p 
                                   LEFT JOIN \"patientFolder\".patient_phone pp ON p.patient_id = pp.patient_id
                                   GROUP BY p.patient_id
                                   ORDER BY p.patient_id");
            $stmt->execute();
            $patients = $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo "Fetch error: " . $e->getMessage();
            $patients = [];
        }
    } else {
        $patients = [];
    }
    ?>

    <table>
        <tr>
            <th>patient_id</th>
            <th>Name</th>
            <th>birthdate</th>
            <th>file_creation_date</th>
            <th>address</th>
            <th>has_cancer</th>
            <th>has_kidney</th>
            <th>has_pulmonary</th>
            <th>has_cardiovascular</th>
            <th>has_neurology</th>
            <th>has_physicaldisability</th>
            <th>phone_numbers</th>
            <th>Delete</th>
            <th>Edit</th>
        </tr>
        <?php foreach ($patients as $patient) : ?>
            <tr>
                <td class="id-column">
                    <a href="details.php?patient_id=<?= $patient->patient_id; ?>"><?= $patient->patient_id; ?></a>
                </td>
                <td><?= $patient->first_name . ' ' . $patient->last_name; ?></td>
                <td><?= $patient->birthdate; ?></td>
                <td><?= $patient->file_creation_date; ?></td>
                <td><?= $patient->address; ?></td>
                <td><?= $patient->has_cancer ? 'Yes' : 'No'; ?></td>
                <td><?= $patient->has_kidney ? 'Yes' : 'No'; ?></td>
                <td><?= $patient->has_pulmonary ? 'Yes' : 'No'; ?></td>
                <td><?= $patient->has_cardiovascular ? 'Yes' : 'No'; ?></td>
                <td><?= $patient->has_neurology ? 'Yes' : 'No'; ?></td>
                <td><?= $patient->has_physicaldisability ? 'Yes' : 'No'; ?></td>
                <td class="phone-column">
                    <?php
                    $phone_numbers = explode(', ', $patient->phone_numbers);
                    foreach ($phone_numbers as $phone) : ?>
                        <div>
                            <?= $phone; ?>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="delete_phone" value="<?= $phone; ?>">
                                <input type="hidden" name="patient_id" value="<?= $patient->patient_id; ?>">
                                <button type="submit">Delete</button>
                            </form>
                            <form method="GET" action="edit_phone.php" style="display:inline;">
                                <input type="hidden" name="edit_phone" value="<?= $phone; ?>">
                                <input type="hidden" name="patient_id" value="<?= $patient->patient_id; ?>">
                                <button type="submit">Edit</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                    <form method="POST" action="" style="margin-top: 10px;">
                        <input type="hidden" name="patient_id" value="<?= $patient->patient_id; ?>">
                        <input type="text" name="new_phone" placeholder="Add new phone" required>
                        <button type="submit">Add</button>
                    </form>
                </td>
                <td class="delete-column">
                    <form method="POST" action="">
                        <input type="hidden" name="delete_id" value="<?= $patient->patient_id; ?>">
                        <button type="submit">Delete</button>
                    </form>
                </td>
                <td class="edit-column">
                    <form method="GET" action="edit.php">
                        <input type="hidden" name="edit_id" value="<?= $patient->patient_id; ?>">
                        <button type="submit">Edit</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <!-- Form for adding new patient -->
        <tr class="new-row">
            <form method="POST" action="">
                <td class="id-column">New</td>
                <td>
                    <input type="text" name="first_name" placeholder="First Name" required>
                    <input type="text" name="last_name" placeholder="Last Name" required>
                </td>
                <td><input type="date" name="birthdate" required></td>
                <td><input type="date" name="file_creation_date" required></td>
                <td><input type="text" name="address" placeholder="Address" required></td>
                <td><input type="checkbox" name="has_cancer" value="1"></td>
                <td><input type="checkbox" name="has_kidney" value="1"></td>
                <td><input type="checkbox" name="has_pulmonary" value="1"></td>
                <td><input type="checkbox" name="has_cardiovascular" value="1"></td>
                <td><input type="checkbox" name="has_neurology" value="1"></td>
                <td><input type="checkbox" name="has_physicaldisability" value="1"></td>
                <td colspan="3">
                    <button type="submit" name="new_patient">Add Patient</button>
                </td>
            </form>
        </tr>
    </table>
    <button class="print-button" onclick="printPage()">Print</button>
</body>

</html>