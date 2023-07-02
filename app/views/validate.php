<?php require_once __DIR__ . '/../../includes/header.php'; ?>

    <!-- New password errors -->
    <div class="errors">
        <?php echo isset($_SESSION['status']) ? htmlspecialchars($_SESSION['status']) : ''; unset($_SESSION['status']);?>
    </div>
    
    <!-- New password form -->
    <div class="login-form">
        <form method="post" action="/Users">
            <input hidden type="text" name="token" value="<?php echo $_GET['token'] ?>"><br><br>
            <input hidden name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"><br><br>
            <button type="submit" name="submit" value="validate">Validate Account</button>
        </form>
    </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>