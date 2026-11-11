<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Birthday Reminder System - Administration</title>
    <script>
        // Optimize theme flash prevention by using localStorage directly
        (function() {
            try {
                var theme = localStorage.getItem('theme') || 'light';
                document.documentElement.setAttribute('data-theme', theme);
            } catch(e) {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/modern.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
<div class="container">
    <header class="header d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div class="logo">
            <h1>Birthday Reminder System</h1>
        </div>
        <nav class="nav">
            <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                <a href="index.php" class="nbutton<?= (basename($_SERVER['PHP_SELF']) == 'index.php' && !isset($_GET['Profile'])) ? ' active' : '' ?>">Home</a>
                <a href="record.php" class="nbutton<?= (basename($_SERVER['PHP_SELF']) == 'record.php') ? ' active' : '' ?>">Add Account</a>
                <a href="#" class="nbutton opener">Search</a>
                <a href="server.php" class="nbutton<?= (basename($_SERVER['PHP_SELF']) == 'server.php') ? ' active' : '' ?>">Run Cron</a>
                <a href="index.php?Profile" class="nbutton<?= (basename($_SERVER['PHP_SELF']) == 'index.php' && isset($_GET['Profile'])) ? ' active' : '' ?>">Profile</a>
                <a href="index.php?logout=true" class="nbutton">Logout</a>
            <?php else: ?>
                <a href="index.php" class="nbutton<?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? ' active' : '' ?>">Login</a>
            <?php endif; ?>
            <button class="theme-toggle" id="theme-toggle" aria-label="Toggle theme" type="button">
                <span class="icon-moon" aria-hidden="true">🌙</span>
                <span class="icon-sun" aria-hidden="true">☀️</span>
            </button>
        </nav>
    </header>

    <!-- Search Popup Dialog -->
    <div id="dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Search</h5>
                <button type="button" class="close-modal" aria-label="Close" id="close-modal-btn">&times;</button>
            </div>
            <form action="index.php" method="get" class="search-form" autocomplete="off">
                <div class="form-group">
                    <input type="text" name="search" id="search" class="form-control" placeholder="Enter search term..." required autocomplete="off">
                </div>
                <div class="form-group">
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="SValues" value="login_name" checked> Name
                        </label>
                        <label>
                            <input type="radio" name="SValues" value="mobile"> Mobile
                        </label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>
    </div>

    <script>
    $(function() {
        // Theme Initialization
        function setTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            
        }

        // Initialize theme
        var theme = localStorage.getItem('theme') || 'light';
        setTheme(theme);

        // Theme toggle handler
        $('#theme-toggle').on('click', function(e) {
            e.preventDefault();
            var currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            setTheme(currentTheme === 'dark' ? 'light' : 'dark');
        });

        // Open popup dialog
        $(".opener").on('click', function(e) {
            e.preventDefault();
            $('#dialog').addClass('popup-active');
            setTimeout(function() {
                $('#search').trigger('focus');
            }, 180);
            $('body').css('overflow', 'hidden');
        });

        // Close popup dialog helper
        function closePopupDialog() {
            $('#dialog').removeClass('popup-active');
            $('body').css('overflow', '');
        }

        $('#close-modal-btn').on('click', closePopupDialog);

        $('#dialog').on('click', function(e) {
            if (e.target === this) closePopupDialog();
        });

        $(document).on('keydown', function(e) {
            if (e.key === "Escape" && $('#dialog').hasClass('popup-active')) {
                closePopupDialog();
            }
        });
    });
    </script>

    <main>