<?php
include('functions.php');

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Get today's date and establish database connection
$today = date('Y-m-d');
$connection = getConnection();
if (!$connection) {
    die('Database connection failed.');
}

// Fetch all records from the birthdays table
$sql = "SELECT * FROM " . TABLE_BIRTHDAYS;
$result = mysqli_query($connection, $sql);

if (!$result) {
    die('Database query failed: ' . mysqli_error($connection));
}

$updated = 0;
$emails_sent = 0;
$errors = [];
$notification_email = "rahber@nevdo.com";

// Loop through each record
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
    $id = isset($row['id']) ? intval($row['id']) : 0;
    $birth_date = $row['birth_date'] ?? '';
    $years = isset($row['years']) ? intval($row['years']) : 1;
    $login_name = $row['login_name'] ?? '';
    $mobile = $row['mobile'] ?? '';
    $days_left = isset($row['days_left']) ? intval($row['days_left']) : 0;

    // Data validation
    if ($id <= 0 || !$birth_date) {
        $errors[] = "Invalid record data (ID/Birthdate missing) for: " . htmlspecialchars($login_name, ENT_QUOTES, 'UTF-8');
        continue;
    }

    // Calculate the next birthday date and difference
    $nextBirthdayDate = addYearsToDate($birth_date, $years);
    $days = calculateDaysDifference($today, $nextBirthdayDate);

    if ($days < -1) {
        // Birthday passed, increment cycle
        $newYears = incrementYear($years);
        $newBirthdayDate = addYearsToDate($birth_date, $newYears);
        $newDays = calculateDaysDifference($today, $newBirthdayDate);

        $stmt = mysqli_prepare($connection, "UPDATE " . TABLE_BIRTHDAYS . " SET years=?, days_left=? WHERE id=?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iii", $newYears, $newDays, $id);
            if (mysqli_stmt_execute($stmt)) {
                $updated++;
            } else {
                $errors[] = "Failed to update years/days for: " . htmlspecialchars($login_name, ENT_QUOTES, 'UTF-8');
            }
            mysqli_stmt_close($stmt);
        } else {
            $errors[] = "Prepare failed for update (years/days) for: " . htmlspecialchars($login_name, ENT_QUOTES, 'UTF-8');
        }
    } else {
        // Just update days_left
        $stmt = mysqli_prepare($connection, "UPDATE " . TABLE_BIRTHDAYS . " SET days_left=? WHERE id=?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $days, $id);
            if (mysqli_stmt_execute($stmt)) {
                $updated++;
            } else {
                $errors[] = "Failed to update days_left for: " . htmlspecialchars($login_name, ENT_QUOTES, 'UTF-8');
            }
            mysqli_stmt_close($stmt);
        } else {
            $errors[] = "Prepare failed for update (days_left) for: " . htmlspecialchars($login_name, ENT_QUOTES, 'UTF-8');
        }

        // Send reminder email when birthday is within the next 2 days (0 or 1)
        if ($days < 2 && $days >= 0) {
            // Compose email (DRY: variable extraction)
            $safe_login_name = htmlspecialchars($login_name, ENT_QUOTES, 'UTF-8');
            $safe_mobile = htmlspecialchars($mobile, ENT_QUOTES, 'UTF-8');
            $safe_days_left = htmlspecialchars((string)$days, ENT_QUOTES, 'UTF-8');
            $safe_birth_date = htmlspecialchars($birth_date, ENT_QUOTES, 'UTF-8');
            $safe_birth_date_m = htmlspecialchars(date('F j', strtotime($birth_date)), ENT_QUOTES, 'UTF-8');

            $email_body = <<<HTML
<html>
<head>
  <style>
    body { font-family: Arial, sans-serif; background-color: #f9f9f9; color: #333; }
    .email-container { background: #fff; padding: 28px 32px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); width: 420px; margin: 0 auto; }
    .email-header { font-size: 22px; font-weight: bold; color: #0a5fab; margin-bottom: 18px; }
    .email-section { margin-bottom: 16px; }
    .user-table { border-collapse: collapse; width: 100%; font-size: 15px; }
    .user-table td { padding: 7px 12px 7px 0; }
    .user-table td.label { font-weight: bold; width: 125px; color: #444; }
  </style>
</head>
<body>
  <div class="email-container">
    <div class="email-header">🎉 Birthday Reminder</div>
    <div class="email-section">Hello,</div>
    <div class="email-section">
      This is a reminder that <strong>the following user has a birthday within the next two days</strong>:
    </div>
    <table class="user-table">
      <tr>
        <td class="label">Name:</td>
        <td>{$safe_login_name}</td>
      </tr>
      <tr>
        <td class="label">Mobile Number:</td>
        <td>{$safe_mobile}</td>
      </tr>
      <tr>
        <td class="label">Days Left:</td>
        <td>{$safe_days_left}</td>
      </tr>
      <tr>
        <td class="label">Birthday:</td>
        <td>{$safe_birth_date_m}</td>
      </tr>
    </table>
    <div class="email-section" style="margin-top: 24px;">
      <em>Thank you,<br />Birthday Reminder System</em>
    </div>
  </div>
</body>
</html>
HTML;

            // Fix: Don't add "To:" header here, let the mail() function's first argument handle it
            $contact_subject = date("F jS", strtotime($birth_date)) . ' - Birthday of ' . $safe_login_name;
            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: Birthdays <birthdays@rahber.net>\r\n";
            // $headers .= "To: $notification_email\r\n"; // DO NOT include To: in headers for PHP mail()

            // Use mail() with validated params
            if (filter_var($notification_email, FILTER_VALIDATE_EMAIL)) {
                if (mail($notification_email, $contact_subject, $email_body, $headers)) {
                    $emails_sent++;
                } else {
                    $errors[] = "Failed to send email for: " . $safe_login_name . " (" . error_get_last()['message'] . ")";
                }
            } else {
                $errors[] = "Invalid notification email address: " . htmlspecialchars($notification_email, ENT_QUOTES, 'UTF-8');
            }
        }
    }
}

// Clean up db connection
mysqli_free_result($result);
mysqli_close($connection);
?>

<div class="card">
    <div class="card-header">
        <h2>Cron Job Execution Results</h2>
    </div>
    
    <div class="alert alert-success">
        <strong>Fields Updated:</strong> <?php echo intval($updated); ?> records processed successfully.
    </div>
    
    <?php if ($emails_sent > 0): ?>
        <div class="alert alert-info">
            <strong>Emails Sent:</strong> <?php echo intval($emails_sent); ?> reminder email(s) sent.
        </div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <strong>Errors:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
   
</div>

<?php include('footer.php'); ?>
