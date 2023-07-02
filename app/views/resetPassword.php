<?php require_once __DIR__ . '/../../includes/header.php'; ?>

    
    <!-- Reset password form -->
    <div class="login-form">
        <div class="text">
            Reset Password
        </div>
        <!-- Reset password errors -->
        <div class="errors">
            <?php echo isset($_SESSION['status']) ? htmlspecialchars($_SESSION['status']) : ''; unset($_SESSION['status']);?>
        </div>
        <form method="post" action="Users">
            <input hidden name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"><br><br>
            <div class="field">
                <div class="fas fa-envelope"></div>
                <input type="email" name="email" placeholder="example@domain.name">
            </div>
            <button type="submit" name="submit" value="resetPassword">Confirm</button>
        </form>
        <div class="link"> 
            <p>Not a member ? Register <a href="/register">Here</a></p>
        </div>
        <div class="link"> 
            <p>Already a member ? Sign-In <a href="/login">Here</a></p>
        </div>
    </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>