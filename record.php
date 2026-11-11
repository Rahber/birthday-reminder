<?php
include('functions.php');

requireLogin();

$recordId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$action = isset($_GET['action']) ? sanitizeInput($_GET['action']) : '';
$isEditMode = $recordId > 0 && $action !== 'delete';
$csrfToken = generateCSRFToken();
$error = '';
$record = null;

// Handle delete action
if ($action === 'delete' && $recordId > 0) {
    $connection = getConnection();
    
    // First verify the record exists
    $checkStmt = mysqli_prepare($connection, "SELECT id FROM " . TABLE_BIRTHDAYS . " WHERE id=? LIMIT 1");
    if ($checkStmt) {
        mysqli_stmt_bind_param($checkStmt, "i", $recordId);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        $recordExists = mysqli_fetch_assoc($checkResult) !== null;
        mysqli_stmt_close($checkStmt);
        
        if (!$recordExists) {
            redirectToPage('index.php?error=Invalid record ID.');
            exit;
        }
    }
    
    // Delete the record
    $stmt = mysqli_prepare($connection, "DELETE FROM " . TABLE_BIRTHDAYS . " WHERE id=?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $recordId);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            redirectToPage('index.php?Deleted');
            exit;
        } else {
            mysqli_stmt_close($stmt);
            redirectToPage('index.php?error=Failed to delete record.');
            exit;
        }
    } else {
        redirectToPage('index.php?error=Failed to delete record.');
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid security token. Please try again.');
    }
    
    $name = sanitizeInput($_POST['name'] ?? '');
    $mobile = sanitizeInput($_POST['mobile'] ?? '');
    $birthDate = sanitizeInput($_POST['date'] ?? '');
    $years = intval($_POST['years'] ?? DEFAULT_YEARS);
    
    // Validate inputs
    if (empty($name) || empty($mobile) || empty($birthDate)) {
        $error = 'Please fill in all fields.';
    } elseif (!validateDate($birthDate)) {
        $error = 'Invalid date format.';
    } elseif ($years < MIN_YEARS || $years > MAX_YEARS) {
        $error = 'Invalid years value.';
    } else {
        // Process the record
        if ($isEditMode) {
            if (updateBirthdayRecord($name, $mobile, $birthDate, $years, $recordId)) {
                redirectToPage('index.php?Updated');
                exit;
            } else {
                $error = 'Failed to update record. Please try again.';
            }
        } else {
            if (insertBirthdayRecord($name, $mobile, $birthDate, $years)) {
                redirectToPage('index.php?Added');
                exit;
            } else {
                $error = 'Failed to add record. Please try again.';
            }
        }
    }
}

// Fetch record for editing
if ($isEditMode) {
    $connection = getConnection();
    $stmt = mysqli_prepare($connection, "SELECT * FROM " . TABLE_BIRTHDAYS . " WHERE id=? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $recordId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $record = mysqli_fetch_array($result, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
        
        if (!$record) {
            redirectToPage('index.php');
            exit;
        }
    } else {
        redirectToPage('index.php');
        exit;
    }
}

$currentYears = $isEditMode ? intval($record['years']) : DEFAULT_YEARS;
$pageTitle = $isEditMode ? 'Edit Birthday Record' : 'Add New Birthday Record';
$submitButtonText = $isEditMode ? 'Update' : 'Submit';
$formAction = $isEditMode ? "record.php?id={$recordId}" : 'record.php';
?>

<div class="card">
    <div class="card-header">
        <h2><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    
    <form id="birthday-form" name="birthday-form" method="post" action="<?php echo htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
        
        <div class="form-group">
            <label for="name">Name <span class="required">*</span></label>
            <input name="name" type="text" id="name" class="form-control" 
                   value="<?php echo $isEditMode ? htmlspecialchars($record['login_name'], ENT_QUOTES, 'UTF-8') : ''; ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="mobile">Mobile Number <span class="required">*</span></label>
            <input type="text" name="mobile" id="mobile" class="form-control" 
                   value="<?php echo $isEditMode ? htmlspecialchars($record['mobile'], ENT_QUOTES, 'UTF-8') : ''; ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="date">Birthday in Current Year <span class="required">*</span></label>
            <input name="date" type="date" id="date" class="form-control date-input" 
                   value="<?php echo $isEditMode ? htmlspecialchars($record['birth_date'], ENT_QUOTES, 'UTF-8') : ''; ?>" 
                   required>
            <small class="error-message" id="date-error" style="display: none;">Please select a valid date.</small>
        </div>
        
        <div class="form-group">
            <label for="years">Years <span class="required">*</span></label>
            <select name="years" id="years" class="form-control" required>
                <?php if (!$isEditMode): ?>
                    <?php for ($i = MIN_YEARS; $i <= 8; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo ($i == DEFAULT_YEARS) ? 'selected' : ''; ?>>
                            <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                <?php else: ?>
                    <option value="">Select</option>
                    <?php for ($i = MIN_YEARS; $i <= MAX_YEARS; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo ($currentYears == $i) ? 'selected' : ''; ?>>
                            <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                <?php endif; ?>
            </select>
        </div>
        
        <div class="form-group">
            <button type="submit" name="submit" id="submit" class="btn btn-primary"><?php echo htmlspecialchars($submitButtonText, ENT_QUOTES, 'UTF-8'); ?></button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
(function() {
    const dateInput = document.getElementById('date');
    const form = document.getElementById('birthday-form');
    const dateError = document.getElementById('date-error');

    if (dateInput) {
        // Remove error class when date is selected
        dateInput.addEventListener('change', function() {
            dateInput.classList.remove('error');
            if (dateError) dateError.style.display = 'none';
        });
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            const dateVal = dateInput ? dateInput.value : '';
            
            if (!dateVal) {
                e.preventDefault();
                if (dateInput) dateInput.classList.add('error');
                if (dateError) dateError.style.display = 'block';
                return false;
            }
            
            // Validate date format (YYYY-MM-DD)
            const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
            if (!dateRegex.test(dateVal)) {
                e.preventDefault();
                if (dateInput) dateInput.classList.add('error');
                if (dateError) dateError.style.display = 'block';
                return false;
            }
        });
    }
})();
</script>

<?php include('footer.php'); ?>

