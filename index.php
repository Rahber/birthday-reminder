<?php
include('functions.php');

/**
 * Security improvement: start session as soon as possible if not started, and enforce session cookie params
 */

/**
 * CSRF token functions for all POST forms
 */


/** ---------- Logout ------------- */
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_unset();
    session_destroy();
    setcookie(session_name(), '', time() - 42000, '/');
    redirectToPage('index.php');
    exit;
}

/** ---------- Login ------------- */
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isLoggedIn()) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token_input = $_POST['csrf_token'] ?? '';

    // CSRF validation
    if (!validateCSRFToken($csrf_token_input)) {
        $error = 'Invalid CSRF token. Please refresh the page and try again.';
    } else if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        // Limit username/password length to prevent abuse
        if (strlen($username) > 150 || strlen($password) > 255) {
            $error = 'Username or password too long.';
        } elseif (authenticateUser($username, $password)) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            redirectToPage('index.php?Loggedin');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

/** ---------- Login form ------------- */
if (!isLoggedIn()) {
    // Display messages
    $message = '';
    if (isset($_GET['SessionMissing'])) {
        $message = 'Please login to continue.';
    } elseif (isset($_GET['Blank'])) {
        $message = 'Please enter both username and password.';
    } elseif (isset($_GET['Incorrect'])) {
        $message = 'Invalid username or password.';
    }
    ?>
    <div class="login-container">
        <div class="login-card card">
            <h2>Login</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="alert alert-info"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            
            <form method="post" action="index.php" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken(), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control" required autofocus maxlength="150" autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required maxlength="255" autocomplete="current-password">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-full">Login</button>
                </div>
            </form>
        </div>
    </div>
    <?php include('footer.php'); ?>
    <?php
    exit;
}

/** ---------- Require login - show admin panel ------------ */
requireLogin();

/** ---------- Handle search functionality (SQL Injection safe) --------- */
$searchResults = [];
$searchMessage = '';
$isSearchMode = false;
$profileMode = false;

if (isset($_GET['search']) && isset($_GET['SValues'])) {
    $isSearchMode = true;
    $searchTerm = trim($_GET['search']);
    $searchColumn = $_GET['SValues'];

    // Validate column name - whitelist
    $allowedColumns = ['login_name', 'mobile', 'birth_date'];
    if (!in_array($searchColumn, $allowedColumns, true)) {
        $searchColumn = 'login_name';
    }

    // Maximum allowed search length
    if (strlen($searchTerm) > 150) {
        $searchMessage = 'Search term too long.';
    } else if (!empty($searchTerm)) {
        $connection = getConnection();
        $searchPattern = '%' . $searchTerm . '%';
        // Use prepared statement for user value only; column is whitelisted, include it directly
        $query = "SELECT * FROM " . TABLE_BIRTHDAYS . " WHERE `{$searchColumn}` LIKE ? ORDER BY days_left ASC";
        $stmt = mysqli_prepare($connection, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $searchPattern);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                $searchResults[] = $row;
            }
            mysqli_stmt_close($stmt);

            if (empty($searchResults)) {
                $searchMessage = 'No results found.';
            }
        } else {
            $searchMessage = 'Database error.';
        }
    } else {
        $searchMessage = 'Please enter a search term.';
    }
}

/** ---------- General messages ---------- */
$message = '';
$messageType = 'info';
if (isset($_GET['Loggedin'])) {
    $message = 'Successfully logged in!';
    $messageType = 'success';
} elseif (isset($_GET['Added'])) {
    $message = 'Record added successfully!';
    $messageType = 'success';
} elseif (isset($_GET['Profile'])) {
    $profileMode = true;
} elseif (isset($_GET['Updated'])) {
    $message = 'Record updated successfully!';
    $messageType = 'success';
} elseif (isset($_GET['Deleted'])) {
    $message = 'Record deleted successfully!';
    $messageType = 'success';
}


/** ------------ Profile page and password change -------------- */
if ($profileMode) {
    $profileMsg = '';
    $profileMsgType = '';

    // Session variables are trusted for current user
    $username = isset($_SESSION['u']) ? $_SESSION['u'] : '';
    $userid   = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
        // CSRF check
        $csrf_token_input = $_POST['csrf_token'] ?? '';
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (!validateCSRFToken($csrf_token_input)) {
            $profileMsg = 'Invalid CSRF token.';
            $profileMsgType = 'danger';
        } elseif (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $profileMsg = 'All fields are required.';
            $profileMsgType = 'danger';
        } elseif (strlen($new_password) > 255) {
            $profileMsg = 'Password too long.';
            $profileMsgType = 'danger';
        } elseif ($new_password !== $confirm_password) {
            $profileMsg = 'New password and confirm password do not match.';
            $profileMsgType = 'danger';
        } elseif (!authenticateUser($username, $current_password)) {
            $profileMsg = 'Current password is incorrect.';
            $profileMsgType = 'danger';
        } else {
            // Hash new password
            $hashedPassword = function_exists('hashPassword')
                ? hashPassword($new_password)
                : password_hash($new_password, PASSWORD_BCRYPT);

            $connection = getConnection();
            $query = "UPDATE " . TABLE_ADMIN . " SET password = ? WHERE id = ?";
            $stmt = mysqli_prepare($connection, $query);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'si', $hashedPassword, $userid);
                if (mysqli_stmt_execute($stmt)) {
                    $profileMsg = 'Password updated successfully!';
                    $profileMsgType = 'success';
                    // Regenerate session id on sensitive operation
                    session_regenerate_id(true);
                } else {
                    $profileMsg = 'Failed to update password.';
                    $profileMsgType = 'danger';
                }
                mysqli_stmt_close($stmt);
            } else {
                $profileMsg = 'Database error.';
                $profileMsgType = 'danger';
            }
        }
    }
    ?>
    <div class="profile-container">
        <div class="profile-card card">
            <h2>Profile</h2>
            <div><strong>Username:</strong> <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></div>
            <h3 class="mt-30">Change Password</h3>
            <?php if ($profileMsg): ?>
                <div class="alert alert-<?php echo htmlspecialchars($profileMsgType); ?>">
                    <?php echo htmlspecialchars($profileMsg, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
            <form method="post" action="index.php?Profile" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken(), ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="change_password" value="1">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" name="current_password" id="current_password" class="form-control" required autocomplete="current-password" maxlength="255">
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" name="new_password" id="new_password" class="form-control" required autocomplete="new-password" maxlength="255">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required autocomplete="new-password" maxlength="255">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-full">Change Password</button>
                    <a href="index.php" class="btn btn-secondary btn-full mt-10">Back to Admin Panel</a>
                </div>
            </form>
        </div>
    </div>
    <?php include('footer.php'); ?>
    <?php
    exit;
}

/** --------- Search mode results ----------- */
if ($isSearchMode) {
    ?>
    <div class="card">
        <div class="card-header">
            <h2>Search Results</h2>
        </div>
        <?php if ($searchMessage && empty($searchResults)): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($searchMessage, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?php if (!empty($searchResults)): ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Mobile</th>
                            <th>Days Left</th>
                            <th>Details</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($searchResults as $row): 
                            $daysLeft = intval($row['days_left']);
                            $badgeClass = 'badge-success';
                            if ($daysLeft <= 2) {
                                $badgeClass = 'badge-danger';
                            } elseif ($daysLeft <= 7) {
                                $badgeClass = 'badge-warning';
                            }
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['login_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['mobile'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo htmlspecialchars($row['days_left'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="record.php?id=<?php echo urlencode((string)(int)$row['id']); ?>" class="btn btn-primary btn-sm">Edit</a>
                                </td>
                                <td>
                                    <a
                                        href="record.php?action=delete&id=<?php echo urlencode((string)(int)$row['id']); ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Are you sure you want to delete this record?');">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <div class="form-group mt-20">
            <a href="index.php" class="btn btn-secondary">Back to Admin Panel</a>
        </div>
    </div>
    <?php include('footer.php'); ?>
    <?php
    exit;
}
?>

<div class="card">
    <div class="card-header">
        <h2>Welcome to Server Administration Panel</h2>
    </div>
    <?php if ($message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8'); ?>">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>
    <?php
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : (int)RECORDS_PER_PAGE;
    $targetPage = "index.php";
   
    $connection = getConnection();

    // Use prepared statement for counting, though COUNT(*) is safe
    $countQuery = "SELECT COUNT(*) as num FROM " . TABLE_BIRTHDAYS;
    $countResult = mysqli_query($connection, $countQuery);
    $countData = mysqli_fetch_array($countResult, MYSQLI_ASSOC);
    $totalRecords = isset($countData['num']) ? (int)$countData['num'] : 0;

    $paginationStages = 3;
    $currentPage = isset($_GET['page']) ? intval($_GET['page']) : 0;
    if ($currentPage) {
        $start = max(0, ($currentPage - 1) * $limit);
    } else {
        $start = 0;
        $currentPage = 1;
    }

    // Safe pagination - do not directly insert user value in LIMIT, use bound parameter if supported (MySQLi cannot for LIMIT, so cast carefully)
    $dataQuery = "SELECT * FROM " . TABLE_BIRTHDAYS . " ORDER BY days_left ASC LIMIT ?, ?";
    $stmt = mysqli_prepare($connection, $dataQuery);
    mysqli_stmt_bind_param($stmt, "ii", $start, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Pagination logic
    $lastPage = max(1, (int)ceil($totalRecords / $limit));
    $paginationStages = max(1, (int)$paginationStages);
    $paginate = '';

    // Always append the limit parameter in the pagination URLs
    $limitParam = "&limit={$limit}";

    if ($lastPage > 1) {
        $paginate .= "<div class='pagination'>";
        $window = $paginationStages * 2 + 1;
        $startPage = max(1, $currentPage - $paginationStages);
        $endPage = min($lastPage, $currentPage + $paginationStages);
        if ($endPage - $startPage + 1 < $window) {
            if ($startPage == 1) {
                $endPage = min($lastPage, $startPage + $window - 1);
            } elseif ($endPage == $lastPage) {
                $startPage = max(1, $endPage - $window + 1);
            }
        }

        // Previous
        if ($currentPage > 1) {
            $paginate .= "<a class='pg-btn' href='{$targetPage}?page=" . ($currentPage - 1) . "{$limitParam}'>Previous</a>";
        } else {
            $paginate .= "<span class='disabled pg-btn'>Previous</span>";
        }

        // First page and ellipsis
        if ($startPage > 1) {
            $paginate .= "<a class='pg-btn' href='{$targetPage}?page=1{$limitParam}'>1</a>";
            if ($startPage > 2) {
                $paginate .= "<span class='ellipsis'>...</span>";
            }
        }

        // Main page numbers
        for ($counter = $startPage; $counter <= $endPage; $counter++) {
            if ($counter == $currentPage) {
                $paginate .= "<span class='current pg-btn'>{$counter}</span>";
            } else {
                $paginate .= "<a class='pg-btn' href='{$targetPage}?page={$counter}{$limitParam}'>{$counter}</a>";
            }
        }

        // Last page and ellipsis
        if ($endPage < $lastPage) {
            if ($endPage < $lastPage - 1) {
                $paginate .= "<span class='ellipsis'>...</span>";
            }
            $paginate .= "<a class='pg-btn' href='{$targetPage}?page={$lastPage}{$limitParam}'>{$lastPage}</a>";
        }

        // Next
        if ($currentPage < $lastPage) {
            $paginate .= "<a class='pg-btn' href='{$targetPage}?page=" . ($currentPage + 1) . "{$limitParam}'>Next</a>";
        } else {
            $paginate .= "<span class='disabled pg-btn'>Next</span>";
        }
        $paginate .= "</div>";
    }
    ?>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Days Left</th>
                    <th>Details</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                        $daysLeft = intval($row['days_left']);
                        $badgeClass = 'badge-success';
                        if ($daysLeft <= 2) {
                            $badgeClass = 'badge-danger';
                        } elseif ($daysLeft <= 7) {
                            $badgeClass = 'badge-warning';
                        }
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['login_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($row['mobile'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <span class="badge <?php echo $badgeClass; ?>">
                                    <?php echo htmlspecialchars($row['days_left'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td>
                                <a href="record.php?id=<?php echo urlencode((string)(int)$row['id']); ?>" class="btn btn-primary btn-sm">
                                    Details
                                </a>
                            </td>
                            <td>
                                <a
                                  href="record.php?action=delete&id=<?php echo urlencode((string)(int)$row['id']); ?>"
                                  class="btn btn-danger btn-sm"
                                  onclick="return confirm('Are you sure you want to delete this record?');">
                                    Delete
                                </a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="5" class="text-center">No records found.</td>
                    </tr>
                    <?php
                }
                mysqli_stmt_close($stmt);
                ?>
            </tbody>
        </table>
    </div>
    <?php echo $paginate; ?>
</div>
<?php include('footer.php'); ?>
