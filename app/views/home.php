<!-- Session check -->
<?php
if (!isset($_SESSION['full_name'])) {
    header('Location: /login');
    exit;
}
?>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

    <!-- Welcome message -->
    <div class="text">
        <?php echo 'Welcome ' . htmlspecialchars($_SESSION['full_name']); echo '</br>'; ?>
        <!-- Logout form -->
        <form action="/Users" method="post">
            <input hidden name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"><br><br>
            <button type="submit" name="submit" value="logout">Logout</button>
        </form>
    </div>
    

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>