<?php
session_start();
include 'db.php';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Fuel Crisis</title>
    
    <style>
      *{
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      body{
        background-color: #0f051a;
        font-family: "Segoe UI";
        color: #ffffff;
        min-height: 100vh;
      }

      .navbar{
        background-color: #302b3f;
        padding: 14px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      .navbar .logo{
        font-weight: 700;
        font-size: 14px;
        letter-spacing: 2px;
        color: #ffffff;
      }

      #user-avatar{
        width: 50px;
        height: 50px;
        border-radius: 50%;

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
      }

      .dropdown-menu a:hover{
        background-color: #4a435d;
      }

      .navbar .nav-links{
        display: flex;
        align-items: center;
        gap: 24px;
        list-style: none;
      }

      .navbar .nav-links a{
        text-decoration: none;
        color: #cccccc;
        font-size: 14px;
      }

      .navbar .nav-links a:hover{
        color: #ffffff;
      }

      .hamburger{
        display: none;
        flex-direction: column;
        cursor: pointer;
        gap: 5px;
        background: none;
        border: none;
        padding: 5px;
      }

      .hamburger span{
        width: 25px;
        height: 3px;
        background-color: white;
        border-radius: 2px;
      }

      .btn-request{
        background-color: #e8e8e8;
        color: #1a1a2e;
        border: none;
        border-radius: 8px;
        padding: 12px 28px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
      }

      .btn-request:hover{
        background-color: #ffffff;
      }

      .hero, .about, .promo-banner, .fuel-cta, .features{
        width: 96%;
        margin: 28px auto;
        border-radius: 16px;
        border: 1px solid #4a435d;
        background-color: #1a1328;
      }

      .hero{
        padding: 40px 32px;
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 24px;
        align-items: center;
      }

      .hero-content{
        text-align: left;
      }

      .hero-tag{
        display: inline-block;
        margin-bottom: 10px;
        background-color: #302b3f;
        color: #d2cee0;
        border: 1px solid #4a435d;
        border-radius: 999px;
        padding: 6px 14px;
        font-size: 12px;
        letter-spacing: 1px;
      }

      .hero h1{
        font-size: 2.2rem;
        margin-bottom: 14px;
      }

      .hero p{
        color: #d2cee0;
        max-width: 760px;
      }

      .hero-visual{
        position: relative;
      }

      .hero-visual img{
        width: 100%;
        height: 290px;
        object-fit: cover;
        border-radius: 14px;
        border: 1px solid #4a435d;
      }

      .hero-badge{
        position: absolute;
        left: 12px;
        bottom: 12px;
        background-color: rgba(15, 5, 26, 0.9);
        border: 1px solid #4a435d;
        color: #ffffff;
        border-radius: 10px;
        padding: 8px 12px;
        font-size: 13px;
        font-weight: 600;
      }

      .about, .promo-banner, .fuel-cta, .features{
        padding: 36px 28px;
      }

      .about h2,
      .fuel-cta h2{
        font-size: 1.8rem;
        margin-bottom: 10px;
      }

      .about p,
      .fuel-cta p{
        color: #d2cee0;
        max-width: 800px;
        margin: 0 auto;
      }

      .premium-points,
      .promo-points{
        margin-top: 16px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
      }

      .point-badge{
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background-color: #302b3f;
        border: 1px solid #4a435d;
        color: #e7e3f3;
        border-radius: 999px;
        padding: 7px 12px;
        font-size: 0.88rem;
        font-weight: 600;
      }

      .point-icon{
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        background-color: #1a1328;
        border: 1px solid #5c5470;
      }

      .about-list{
        margin-top: 16px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        list-style: none;
      }

      .about-list li{
        background-color: #302b3f;
        border-radius: 10px;
        padding: 12px;
        font-weight: 600;
        text-align: center;
      }

      .promo-banner{
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
        align-items: center;
        background: linear-gradient(120deg, #241b36, #1a1328);
      }

      .promo-banner h3{
        font-size: 1.6rem;
        margin-bottom: 8px;
      }

      .promo-banner p{
        color: #d2cee0;
      }

      .promo-banner img{
        width: 100%;
        height: 220px;
        object-fit: cover;
        border-radius: 12px;
        border: 1px solid #4a435d;
      }

      .about,
      .fuel-cta{
        text-align: center;
      }

      .fuel-cta .btn-request{
        margin-top: 18px;
        display: inline-block;
        text-decoration: none;
      }

      .features-head{
        text-align: center;
        margin-bottom: 20px;
      }

      .features-head h2{
        font-size: 1.8rem;
        margin-bottom: 8px;
      }

      .features-head p{
        color: #d2cee0;
      }

      .features-grid{
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
      }

      .feature-card{
        background-color: #302b3f;
        border: 1px solid #4a435d;
        border-radius: 12px;
        padding: 18px;
        display: flex;
        flex-direction: column;
        min-height: 240px;
      }

      .feature-icon{
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        background-color: #1a1328;
        border: 1px solid #4a435d;
        margin-bottom: 12px;
      }

      .feature-card h3{
        font-size: 1.15rem;
        margin-bottom: 8px;
      }

      .feature-card p{
        color: #d2cee0;
        font-size: 0.95rem;
        flex: 1;
      }

      .feature-card .btn-request{
        margin-top: 16px;
        width: fit-content;
        text-decoration: none;
      }

      footer{
        margin-top: 40px;
        background-color: #302b3f;
        color: #d2cee0;
        border-top: 1px solid #4a435d;
      }

      .footer-wrap{
        width: 96%;
        margin: 0 auto;
        padding: 34px 8px 18px;
      }

      .footer-top{
        display: grid;
        grid-template-columns: 1.2fr 1fr 1fr;
        gap: 22px;
      }

      .footer-brand h3{
        letter-spacing: 1px;
        margin-bottom: 10px;
      }

      .footer-brand p{
        color: #c8c3d7;
        max-width: 380px;
        font-size: 0.95rem;
      }

      .social-row{
        margin-top: 14px;
        display: flex;
        gap: 10px;
      }

      .social-btn{
        width: 38px;
        height: 38px;
        border-radius: 999px;
        border: 1px solid #5a526c;
        color: #ffffff;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.82rem;
        font-weight: 700;
        background-color: #1a1328;
      }

      .social-btn:hover{
        background-color: #e8e8e8;
        color: #1a1a2e;
      }

      .footer-col h4{
        margin-bottom: 10px;
        color: #ffffff;
      }

      .footer-links{
        list-style: none;
      }

      .footer-links li{
        margin-bottom: 8px;
      }

      .footer-links a{
        color: #d2cee0;
        text-decoration: none;
        font-size: 0.95rem;
      }

      .footer-links a:hover{
        color: #ffffff;
      }

      .footer-bottom{
        margin-top: 22px;
        border-top: 1px solid #4a435d;
        padding-top: 14px;
        display: flex;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        font-size: 0.9rem;
        color: #bcb7cb;
      }

      @media (max-width: 850px){
        .navbar{
          flex-direction: row;
          justify-content: space-between;
          position: relative;
        }

        .hamburger{
          display: flex;
        }

        .navbar .nav-links{
          display: none;
          flex-direction: column;
          position: absolute;
          top: 100%;
          left: 0;
          width: 100%;
          background-color: #302b3f;
          padding: 20px 0;
          gap: 15px;
          z-index: 1000;
          border-bottom: 1px solid #4a435d;
        }

        .navbar .nav-links.active{
          display: flex;
        }

        .about-list{
          grid-template-columns: 1fr;
        }

        .premium-points,
        .promo-points{
          justify-content: center;
        }

        .features-grid{
          grid-template-columns: 1fr;
        }

        .hero,
        .promo-banner{
          grid-template-columns: 1fr;
        }

        .hero{
          padding: 32px 22px;
        }

        .hero-content{
          text-align: center;
        }

        .hero p{
          margin: 0 auto;
        }

        .footer-top{
          grid-template-columns: 1fr;
        }

        .footer-bottom{
          flex-direction: column;
        }

        .dropdown-menu{
          position: static;
          width: 100%;
          box-shadow: none;
          border: none;
          margin-top: 0;
          background-color: transparent;
        }

        .dropdown-menu a{
          padding-left: 0;
          color: #cccccc;
        }
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
        <li><a href="./home.php">Home</a></li>
        <li><a href="User_Page/USER HOME/index.php">Dashboard</a></li>
        <li><a href="User_Page/USER STATION/index.php">Stations</a></li>
        <li><a href="User_Page/USER TOKEN/user-token-page.php">Token</a></li>
        <li>
          <div class="avatar-container" id="avatar-container">
            <img id="user-avatar" src="Assets/image.png" alt="" />
            <div class="dropdown-menu" id="avatar-dropdown">
              <?php if(isset($_SESSION['user_id'])): ?>
                <a href="logout.php">LogOut</a>
              <?php else: ?>
                <a href="Registration_Page/SignIn/index.php">Login</a>
              <?php endif; ?>
            </div>
          </div>
        </li>
      </ul>
    </nav>

    <main>
      <section class="hero">
        <div class="hero-content">
          <span class="hero-tag">SMART FUEL ACCESS</span>
          <h1>Fuel Faster, Drive Further</h1>
          <p>
            Reliable access to fuel stations with queue-aware service and
            smarter routing for every trip.
          </p>
        </div>
        <div class="hero-visual">
          <img src="Assets/Fuel (1).jpg" alt="" />
          <span class="hero-badge">Live Queue Tracking</span>
        </div>
      </section>

      <section class="about">
        <h2>About Us</h2>
        <p>
          Fuel Crisis is built to make fuel access easier, faster, and more
          transparent for every driver. Our platform connects users with trusted
          stations, gives real-time availability insights, and reduces
          uncertainty with smart queue visibility so people spend less time
          waiting and more time moving.
        </p>
        <ul class="about-list">
          <li><?php 
            $countRes = $conn->query("SELECT COUNT(*) as count FROM stations");
            echo $countRes->fetch_assoc()['count'];
          ?> Registered Stations</li>
          <li>Queue Transparency</li>
          <li>Token-Based Access</li>
        </ul>
      </section>

      <section class="promo-banner">
        <div>
          <h3>Your Fuel, Your Time</h3>
          <p>
            Skip uncertainty with clear station availability, better route
            decisions, and queue-aware updates designed for busy daily
            commuters. From planning your stop to tracking your token, every
            step is optimized to save your time and deliver a premium,
            stress-free fuel experience.
          </p>
          <div class="promo-points">
            <span class="point-badge"><span class="point-icon">⛽</span>Smart Refuel Planning</span>
            <span class="point-badge"><span class="point-icon">⏱</span>Time Saving Alerts</span>
            <span class="point-badge"><span class="point-icon">★</span>Verified Stations</span>
            <span class="point-badge"><span class="point-icon">⚡</span>Faster Service Flow</span>
          </div>
        </div>
        <img src="Assets/Fuel Gauge.jpeg" alt="" />
      </section>

      <section class="fuel-cta">
        <h2>Want to get fuel?</h2>
        <p>
          Go to the station page and pick the best location based on current
          service availability.
        </p>
        <a class="btn-request" href="User_Page/USER STATION/index.php">Go to Station Page</a>
      </section>
      

      <section class="features">
        <div class="features-head">
          <h2>Best Features</h2>
          <p>Everything built to make your fuel journey faster and easier.</p>
        </div>

        <div class="features-grid">
          <article class="feature-card">
            <span class="feature-icon" aria-hidden="true">⛽</span>
            <h3>Low Cost and Premium Fuel</h3>
            <p>
              Compare nearby station options and choose fuel that balances cost,
              quality, and trusted service.
            </p>
            <a class="btn-request" href="User_Page/USER STATION/index.php">Find Best Price</a>
          </article>

          <article class="feature-card">
            <span class="feature-icon" aria-hidden="true">⏱️</span>
            <h3>Low Waiting Time</h3>
            <p>
              Avoid long queues by checking station flow before you arrive and
              picking lower wait-time locations.
            </p>
            <a class="btn-request" href="User_Page/USER STATION/index.php">Check Wait Time</a>
          </article>

          <article class="feature-card">
            <span class="feature-icon" aria-hidden="true">📡</span>
            <h3>Live Tracking</h3>
            <p>
              Follow your token and station activity in real time so you always
              know when your turn is coming.
            </p>
            <a class="btn-request" href="User_Page/USER TOKEN/user-token-page.php">Track Now</a>
          </article>
        </div>
      </section>
    </main>

    <footer>
      <div class="footer-wrap">
        <div class="footer-top">
          <div class="footer-brand">
            <h3>FUEL CRISIS</h3>
            <p>
              A smarter way to find stations, reduce waiting time, and track
              your fuel token in real time.
            </p>
            <div class="social-row">
              <a class="social-btn" href="#" aria-label="Facebook">FB</a>
              <a class="social-btn" href="#" aria-label="Instagram">IG</a>
              <a class="social-btn" href="#" aria-label="Twitter">TW</a>
            </div>
          </div>

          <div class="footer-col">
            <h4>Quick Links</h4>
            <ul class="footer-links">
              <li><a href="User_Page/USER HOME/index.php">Dashboard</a></li>
              <li><a href="User_Page/USER STATION/index.php">Stations</a></li>
              <li>
                <a href="User_Page/USER TOKEN/user-token-page.php">Token</a>
              </li>
              <li>
                <a href="Registration_Page/SignIn/index.php">Sign In</a>
              </li>
            </ul>
          </div>

          <div class="footer-col">
            <h4>Legal & Support</h4>
            <ul class="footer-links">
              <li><a href="#">Terms and Conditions</a></li>
              <li><a href="#">Privacy Policy</a></li>
              <li><a href="#">Cookie Policy</a></li>
              <li><a href="#">Help Center</a></li>
            </ul>
          </div>
        </div>

        <div class="footer-bottom">
          <p>&copy; 2026 Fuel Crisis. All rights reserved.</p>
          <p>Built for faster and fairer fuel access.</p>
        </div>
      </div>
    </footer>
    <script>
      const hamburger = document.getElementById('hamburger');
      const navLinks = document.getElementById('nav-links');
      if(hamburger && navLinks){
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