<?php
session_start();
include '../../db.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../../Registration_Page/SignIn/index.php");
    exit();
}

$logs = $conn->query("SELECT l.*, u.full_name FROM logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY timestamp DESC LIMIT 20");
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css" />
    <style>
      body{ 
        width: 100%; 
        overflow-x: hidden; 
        margin: 0; 
        padding: 0; 
      }
      
      *{ 
        box-sizing: border-box; 
      }

      .avatar-container{
        position: relative;
        display: inline-block;
        cursor: pointer;
      }


      .dropdown-menu{
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        background-color: #302b3f;
        min-width: 150px;
        box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
        z-index: 1001;
        border-radius: 8px;
        overflow: hidden;
        margin-top: 10px;
        border: 1px solid #4a435d;
      }


      .dropdown-menu.show{
        display: block;
      }


      .dropdown-menu a{
        color: #ffffff;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        font-size: 14px;
        transition: background-color 0.3s;
      }


      .dropdown-menu a:hover{
        background-color: #4a435d;
      }
      

      table{
        width: 100%;
        border-collapse: collapse;
      }
      

      th, 
      td{
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #4a435d;
      }
      

      th{
        color: #A28EAB;
      }
      

      .card-user{
        background-color: rgba(48, 43, 63, 1);
        width: 96%;
        margin: 40px auto;
        padding: 30px;
        border-radius: 25px;
        border: 1px solid #A28EAB;
        overflow: hidden;
      }
      

      .btn-action{
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        cursor: pointer;
        text-decoration: none;
        color: white;
        margin-right: 4px;
      }
      

      .btn-promote{
        background-color: #10B981; 
      }
      .btn-demote{ 
        background-color: #FBBF24; color: black; 
      }
      .btn-ban{
        background-color: #EF4444;
      }
      .btn-unban{
        background-color: #34D399;
      }
      

      .role-badge{
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 99px;
        text-transform: uppercase;
        font-weight: bold;
      }
      

      .role-admin{ 
        background: #F43F5E; color: white; 
      }
      .role-staff{ 
        background: #98DDE5; color: black; 
      }
      .role-user{ 
        background: #A28EAB; color: white; 
      }
    </style>
  </head>
  <body>
    <nav class="navbar">
      <span class="logo">FUEL CRISIS</span>
      <button class="hamburger" id="hamburger">
        <span></span>
        <span></span>
        <span></span>
      </button>
      <ul class="nav-links" id="nav-links">
        <li><a href="./index.php">Dashboard</a></li>
        <li><a href="../ADMIN STATION/index.php">Stations</a></li>
        <li><a href="../ADMIN TOKEN/admin-token-page.php">Token</a></li>
        <li><a href="../ADMIN ANALYTICS/index.php">Analytics</a></li>
        <li>
          <div class="avatar-container" id="avatar-container">
            <img
              id="user-avatar"
              src="../../Assets/image.png"
              alt="User Avatar"
            />
            <div class="dropdown-menu" id="avatar-dropdown">
              <a href="../../logout.php">LogOut</a>
            </div>
          </div>
        </li>
      </ul>
    </nav>

    <section class="banner-1">
      <h2 class="title-1">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
      <div class="card-container">
        <div class="card-1">
          <h3 class="title-2">System Activity Logs</h3>
          <div style="max-height: 300px; overflow-y: auto; overflow-x: auto;">
            <table>
              <thead>
                <tr>
                  <th>Time</th>
                  <th>User</th>
                  <th>Action</th>
                  <th>Details</th>
                </tr>
              </thead>
              <tbody>
                <?php if($logs->num_rows > 0): ?>
                  <?php while($l = $logs->fetch_assoc()): ?>
                    <tr>
                      <td style="font-size: 12px;"><?php echo date('h:i A', strtotime($l['timestamp'])); ?></td>
                      <td style="font-size: 12px;"><?php echo htmlspecialchars($l['full_name'] ?? 'System'); ?></td>
                      <td style="font-size: 12px; font-weight: bold;"><?php echo htmlspecialchars($l['action']); ?></td>
                      <td style="font-size: 12px;"><?php echo htmlspecialchars($l['details']); ?></td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="4">No logs available</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="card-2">
          <h3 class="title-3">Quick Action</h3>
          <a href="../ADMIN STATION/index.php"><button>Manage Stations</button></a>
          <a href="../ADMIN TOKEN/admin-token-page.php"><button>Manage Queue</button></a>
          <a href="../ADMIN ANALYTICS/index.php"><button>View Analytics</button></a>
        </div>
      </div>
    </section>


    <section class="card-user">
      <h3 class="title-2" style="margin-bottom: 20px;">User Management System</h3>
      <?php
        $allUsers = $conn->query("SELECT * FROM users ORDER BY role DESC, full_name ASC");
      ?>
      <div style="max-height: 400px; overflow-y: auto; overflow-x: auto;">
        <table style="min-width: 600px;">
          <thead>
            <tr>
              <th>Full Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while($u = $allUsers->fetch_assoc()): ?>
              <tr>
                <td style="font-size: 13px;"><?php echo ($u['full_name']); ?></td>
                <td style="font-size: 13px;"><?php echo ($u['email']); ?></td>
                <td>
                    <span class="role-badge role-<?php echo $u['role']; ?>">
                        <?php echo $u['role']; ?>
                    </span>
                </td>
                <td>
                    <strong style="font-size: 12px; color: <?php 
                        if($u['status'] == 'Active'){
                            echo '#10B981';
                        }
                        else{
                            echo '#EF4444';
                        }
                    ?>">
                        <?php echo $u['status']; ?>
                    </strong>
                </td>
                <td>
                    <?php if($u['role'] == 'user'): ?>
                        <a href="admin_user_handler.php?action=change_role&id=<?php echo $u['id']; ?>&role=staff" class="btn-action btn-promote">Make Staff</a>
                    <?php elseif($u['role'] == 'staff'): ?>
                        <a href="admin_user_handler.php?action=change_role&id=<?php echo $u['id']; ?>&role=admin" class="btn-action btn-promote">Make Admin</a>
                        <a href="admin_user_handler.php?action=change_role&id=<?php echo $u['id']; ?>&role=user" class="btn-action btn-demote">Demote</a>
                    <?php elseif($u['role'] == 'admin' && $u['id'] != $_SESSION['user_id']): ?>
                        <a href="admin_user_handler.php?action=change_role&id=<?php echo $u['id']; ?>&role=staff" class="btn-action btn-demote">Demote</a>
                    <?php endif; ?>


                    <?php if($u['id'] != $_SESSION['user_id']): ?>
                        <?php 
                            if($u['status'] == 'Active'){
                                $banClass = 'btn-ban';
                                $banText = 'Ban';
                            }
                            else{
                                $banClass = 'btn-unban';
                                $banText = 'Unban';
                            }
                        ?>
                        <a href="admin_user_handler.php?action=toggle_ban&id=<?php echo $u['id']; ?>&status=<?php echo $u['status']; ?>" 
                           class="btn-action <?php echo $banClass; ?>">
                            <?php echo $banText; ?>
                        </a>
                    <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>
    <script>
      const hamburger = document.getElementById('hamburger');
      const navLinks = document.getElementById('nav-links');
      if (hamburger && navLinks) {
        hamburger.addEventListener('click', function(){
          navLinks.classList.toggle('active');
        });
      }

      const avatarContainer = document.getElementById('avatar-container');
      const avatarDropdown = document.getElementById('avatar-dropdown');

      if(avatarContainer && avatarDropdown){

        avatarContainer.addEventListener('click', function(e){
          e.stopPropagation();
          avatarDropdown.classList.toggle('show');
        });


        window.addEventListener('click', function(){
          if(avatarDropdown.classList.contains('show')){
            avatarDropdown.classList.remove('show');
          }
        });
      }
    </script>
  </body>
</html>