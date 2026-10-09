<?php

// ==============================================================================
// 1. DATABASE CONNECTION (PDO)
// ==============================================================================
// Connect to MySQL server running locally with user 'root' and empty password
$host = 'localhost';
$port = '3306';
$dbname = 'myapp'; // Make sure you created 'myapp' database in phpMyAdmin or MySQL CLI
$username = 'root';
$password = '';

try {
    // 1. Connect to MySQL server (without specifying dbname first)
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 2. Automatically create the 'myapp' database if it does not exist yet
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // 3. Switch to the 'myapp' database
    $pdo->exec("USE `$dbname`");

    // 4. Automatically create the 'users' table if it does not exist yet
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        age INT NULL,
        city VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

} catch (PDOException $e) {
    // If connection fails, display a friendly and helpful error message
    die("<div style='font-family:sans-serif; padding:20px; color:#b91c1c; background:#fee2e2; border-radius:8px; margin:20px auto; max-width:600px;'>
            <h3>⚠️ Database Connection Error</h3>
            <p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
            <p>Please make sure:</p>
            <ul>
                <li>MySQL in XAMPP is running (Start button is clicked).</li>
                <li>Username is <code>root</code> and password is empty.</li>
            </ul>
         </div>");
}

// Array to hold validation error messages
$errors = [];

// Variables to hold old input values (to keep what the user typed in the input fields)
$name = '';
$age = '';
$city = '';

// Variable to hold success message details
$successUser = null;

// ==============================================================================
// 2. HANDLE FORM SUBMISSION (POST REQUEST)
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Retrieve and trim input values from $_POST
    $name = trim($_POST['name'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $city = trim($_POST['city'] ?? '');

    // --------------------------------------------------------------------------
    // TODO 1: Validate name and city. Put messages in $errors.
    // --------------------------------------------------------------------------
    if (empty($name)) {
        $errors[] = 'Name is required';
    }

    if (empty($city)) {
        $errors[] = 'City is required';
    }

    // --------------------------------------------------------------------------
    // TODO 2: If $errors is empty, INSERT the user with a prepared statement.
    // --------------------------------------------------------------------------
    if (empty($errors)) {
        // SQL query with placeholders (:name, :age, :city) to prevent SQL Injection
        $sql = "INSERT INTO users (name, age, city) VALUES (:name, :age, :city)";
        
        // Prepare the SQL statement
        $statement = $pdo->prepare($sql);
        
        // Execute the statement with sanitized parameters
        $statement->execute([
            ':name' => $name,
            ':age'  => $age !== '' ? (int)$age : null,
            ':city' => $city
        ]);

        // ----------------------------------------------------------------------
        // PRG Pattern (Post-Redirect-Get):
        // Redirect after insert so page refresh (F5) will NEVER prompt "Confirm Form Resubmission"
        // ----------------------------------------------------------------------
        header("Location: signup.php?success=1&name=" . urlencode($name) . "&city=" . urlencode($city));
        exit;
    }
}

// Read success message from redirect query parameters if available
if (isset($_GET['success']) && isset($_GET['name']) && isset($_GET['city'])) {
    $successUser = [
        'name' => $_GET['name'],
        'city' => $_GET['city']
    ];
}

// ------------------------------------------------------------------------------
// BONUS: Fetch all users from the database to display in a table
// ------------------------------------------------------------------------------
$usersQuery = $pdo->query("SELECT * FROM users ORDER BY id DESC");
$allUsers = $usersQuery->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | User Registration</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --primary-light: #eef2ff;
            --success-bg: #ecfdf5;
            --success-border: #a7f3d0;
            --success-text: #065f46;
            --error-bg: #fef2f2;
            --error-border: #fecaca;
            --error-text: #991b1b;
            --bg-page: #f8fafc;
            --card-bg: #ffffff;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-dark);
            min-height: 100vh;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container {
            width: 100%;
            max-width: 580px;
        }

        .card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 32px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            margin-bottom: 30px;
        }

        .header {
            text-align: center;
            margin-bottom: 24px;
        }

        .header h1 {
            font-size: 26px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 6px;
        }

        .header p {
            color: var(--text-muted);
            font-size: 14px;
        }

        /* Error Box (TODO 3) */
        .alert-error {
            background-color: var(--error-bg);
            border: 1px solid var(--error-border);
            color: var(--error-text);
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error ul {
            margin-left: 20px;
            margin-top: 4px;
        }

        /* Success Box (TODO 4) */
        .alert-success {
            background-color: var(--success-bg);
            border: 1px solid var(--success-border);
            color: var(--success-text);
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid var(--border-color);
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            color: var(--text-dark);
            background: #fff;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        }

        .btn-submit:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        /* Bonus: User List Section */
        .user-list-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 24px 32px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }

        .user-list-card h2 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .badge-count {
            background: var(--primary-light);
            color: var(--primary);
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 9999px;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            text-align: left;
        }

        th {
            background: #f8fafc;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 14px;
            border-bottom: 1px solid var(--border-color);
        }

        td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-dark);
        }

        tr:last-child td {
            border-bottom: none;
        }

        .empty-state {
            text-align: center;
            padding: 20px 0;
            color: var(--text-muted);
            font-size: 14px;
        }
    </style>
</head>
<body>

    <div class="container">
        
        <!-- Registration Form Card -->
        <div class="card">
            <div class="header">
                <h1>Sign Up</h1>
                <p>Register a new user into MySQL database (myapp)</p>
            </div>

            <!-- ================================================================ -->
            <!-- TODO 3: Show the errors here                                     -->
            <!-- ================================================================ -->
            <?php if (!empty($errors)): ?>
                <div class="alert-error">
                    <strong>Please fix the following errors:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <!-- Always escape untrusted output with htmlspecialchars() -->
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- ================================================================ -->
            <!-- TODO 4: Show "Welcome ___ from ___!" after a successful sign up. -->
            <!-- ================================================================ -->
            <?php if ($successUser): ?>
                <div class="alert-success">
                    <span>🎉</span>
                    <span>
                        Welcome <?= htmlspecialchars($successUser['name']) ?> from <?= htmlspecialchars($successUser['city']) ?>!
                    </span>
                </div>
            <?php endif; ?>

            <!-- Registration Form -->
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Name <span style="color:#ef4444;">*</span></label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="form-control" 
                        placeholder="e.g. Sara" 
                        value="<?= htmlspecialchars($name) ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="age">Age</label>
                    <input 
                        type="number" 
                        id="age" 
                        name="age" 
                        class="form-control" 
                        placeholder="e.g. 24" 
                        value="<?= htmlspecialchars($age) ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="city">City <span style="color:#ef4444;">*</span></label>
                    <input 
                        type="text" 
                        id="city" 
                        name="city" 
                        class="form-control" 
                        placeholder="e.g. Dhaka" 
                        value="<?= htmlspecialchars($city) ?>"
                    >
                </div>

                <button type="submit" class="btn-submit">Sign Up</button>
            </form>
        </div>

        <!-- ==================================================================== -->
        <!-- BONUS: Live User List from Database                                  -->
        <!-- ==================================================================== -->
        <div class="user-list-card">
            <h2>
                <span>Registered Users</span>
                <span class="badge-count"><?= count($allUsers) ?> total</span>
            </h2>

            <?php if (empty($allUsers)): ?>
                <div class="empty-state">No users registered yet. Fill out the form above to add the first user!</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Name</th>
                            <th>Age</th>
                            <th>City</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allUsers as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['id']) ?></td>
                                <td><strong><?= htmlspecialchars($user['name']) ?></strong></td>
                                <td><?= $user['age'] ? htmlspecialchars($user['age']) : '<span style="color:#94a3b8;">N/A</span>' ?></td>
                                <td><?= htmlspecialchars($user['city']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>
