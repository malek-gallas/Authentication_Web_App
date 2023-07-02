<?php require_once __DIR__ . '/../../includes/header.php'; ?>

    <!-- New password errors -->
    <div class="errors">
        <?php echo isset($_SESSION['status']) ? htmlspecialchars($_SESSION['status']) : ''; unset($_SESSION['status']);?>
    </div>
    <!-- New password form -->
    <div class="login-form">
        <div class="text">
            New Password
        </div>
        <form method="post" action="/Users">
            <input hidden type="text" name="token" value="<?php echo $_GET['token'] ?>"><br><br>
            <input hidden name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"><br><br>
            <div class="field">
                <div class="fas fa-lock"></div>
                <input type="password" name="password" placeholder="Password" required><br><br>
            </div>
            <div class="field">
                <div class="fas fa-redo"></div>
                <input type="password" name="passwordRepeat" placeholder="Repeat Password" required><br><br>
            </div>
            <button type="submit" name="submit" value="newPasswordCall">Confirm</button>
        </form>
    </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>