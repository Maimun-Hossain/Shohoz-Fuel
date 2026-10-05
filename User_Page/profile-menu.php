<?php
$profileUser = null;
$lastPurchase = null;

if(isset($_SESSION['user_id'])){
    $profileStmt = $conn->prepare("SELECT full_name, email, location, location_x, location_y FROM users WHERE id=?");
    $profileStmt->bind_param("i", $_SESSION['user_id']);
    $profileStmt->execute();
    $profileUser = $profileStmt->get_result()->fetch_assoc();

    $purchaseStmt = $conn->prepare("SELECT s.name AS station_name, t.fuel_type, t.liters, t.created_at FROM tokens t JOIN stations s ON s.id=t.station_id WHERE t.user_id=? AND t.status='Completed' ORDER BY t.created_at DESC, t.id DESC LIMIT 1");
    $purchaseStmt->bind_param("i", $_SESSION['user_id']);
    $purchaseStmt->execute();
    $lastPurchase = $purchaseStmt->get_result()->fetch_assoc();

    if(!isset($_SESSION['profile_csrf_token'])){
        $_SESSION['profile_csrf_token'] = bin2hex(random_bytes(32));
    }
}

$profileNotice = $_SESSION['profile_notice'] ?? '';
unset($_SESSION['profile_notice']);
?>
<div class="avatar-container" id="avatar-container">
  <button class="profile-trigger" id="avatar-trigger" type="button" aria-label="Open profile" aria-expanded="false" aria-controls="avatar-dropdown">
    <img id="user-avatar" src="../../Assets/image.png" alt="User profile">
  </button>
  <div class="dropdown-menu profile-dropdown" id="avatar-dropdown">
    <?php if($profileUser): ?>
      <div class="profile-heading">
        <strong><?php echo htmlspecialchars($profileUser['full_name']); ?></strong>
        <span><?php echo htmlspecialchars($profileUser['email']); ?></span>
      </div>
      <?php if($profileNotice !== ''): ?>
        <p class="profile-notice" role="status"><?php echo htmlspecialchars($profileNotice); ?></p>
      <?php endif; ?>
      <form class="profile-location-form" action="../profile-handler.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['profile_csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($profileReturnPath, ENT_QUOTES, 'UTF-8'); ?>">
        <label for="profile-location">Location</label>
        <input id="profile-location" type="text" name="location" maxlength="255" value="<?php echo htmlspecialchars($profileUser['location'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Add your location" autocomplete="address-level2" required>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:10px;">
          <div>
            <label for="profile-location-x" style="display:block; margin-bottom:4px; font-size:12px; color:#e9d9f7;">X</label>
            <input id="profile-location-x" type="number" step="0.01" name="location_x" value="<?php echo htmlspecialchars((string)($profileUser['location_x'] ?? 0.00), ENT_QUOTES, 'UTF-8'); ?>" placeholder="x" required>
          </div>
          <div>
            <label for="profile-location-y" style="display:block; margin-bottom:4px; font-size:12px; color:#e9d9f7;">Y</label>
            <input id="profile-location-y" type="number" step="0.01" name="location_y" value="<?php echo htmlspecialchars((string)($profileUser['location_y'] ?? 0.00), ENT_QUOTES, 'UTF-8'); ?>" placeholder="y" required>
          </div>
        </div>
        <button type="submit">Save location</button>
      </form>
      <div class="profile-last-purchase">
        <span class="profile-section-label">Last purchase</span>
        <?php if($lastPurchase): ?>
          <strong><?php echo htmlspecialchars($lastPurchase['station_name']); ?></strong>
          <span><?php echo htmlspecialchars(ucfirst($lastPurchase['fuel_type'])); ?> · <?php echo number_format((float)$lastPurchase['liters'], 2); ?> L</span>
          <time datetime="<?php echo htmlspecialchars(date('c', strtotime($lastPurchase['created_at']))); ?>"><?php echo date('M j, Y', strtotime($lastPurchase['created_at'])); ?></time>
        <?php else: ?>
          <span>No completed purchase yet.</span>
        <?php endif; ?>
      </div>
      <a class="profile-logout" href="../../logout.php">Log out</a>
    <?php else: ?>
      <a href="../../Registration_Page/SignIn/index.php">Login</a>
    <?php endif; ?>
  </div>
</div>
