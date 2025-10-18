<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Parcel Transport - Home</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    .landing{padding:24px 0}
    .hero-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:24px;align-items:center}
    .hero h1{font-size:38px;margin:0 0 10px 0}
    .hero p{color:var(--muted);margin:0 0 16px 0}
    .hero .cta{display:flex;gap:10px;flex-wrap:wrap}
    .hero-figure{position:relative}
    .hero-figure img{width:100%;height:auto;border-radius:16px;border:1px solid var(--border);box-shadow:0 10px 24px rgba(0,0,0,.25)}
    .badges{display:flex;gap:10px;margin-top:12px;flex-wrap:wrap}
    .badge{background:#0b1220;border:1px solid var(--border);padding:6px 10px;border-radius:999px;font-size:12px;color:var(--muted)}
    .features{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;margin-top:20px}
    .feature{padding:14px;border:1px solid var(--border);border-radius:12px;background:#0b1220}
    .feature .icon{font-size:20px}
    @media (max-width:900px){.hero-grid{grid-template-columns:1fr;}}
  </style>
</head>
<body>
  <?php require_once __DIR__ . '/partials/header.php'; ?>
  <?php
    // Load hero banners server-side (no API)
    $heroBanners = [];
    $heroFirst = null;
    try {
      require_once __DIR__ . '/../backend/init.php';
      $res = $conn->query("SELECT * FROM hero_banners WHERE is_active=1 ORDER BY sort_order ASC, id ASC");
      while ($row = $res->fetch_assoc()) { $heroBanners[] = $row; }
      if (count($heroBanners) > 0) { $heroFirst = $heroBanners[0]; }
    } catch (Throwable $e) { /* ignore, fallback to static */ }
  ?>
  <div class="spotlight-layer" id="spotlight"></div>
  <main>


    <!-- Hero full with slideshow -->
    <div class="container-hero hero-wrap full-bleed">
      <section class="hero hero-full">
        <div class="slideshow" aria-hidden="true">
          <?php if (!empty($heroBanners)): ?>
            <?php foreach ($heroBanners as $i => $b): $src = htmlspecialchars($b['image_url'] ?? ''); ?>
              <img <?php echo $i===0 ? 'id="heroBg1" class="active"' : '';?> src="<?php echo $src; ?>" alt="" />
            <?php endforeach; ?>
          <?php else: ?>
            <img id="heroBg1" class="active" src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?q=80&w=1600&auto=format&fit=crop" alt="" />
            <img src="https://images.unsplash.com/photo-1578662996442-48f60103fc96?q=80&w=1600&auto=format&fit=crop" alt="" />
            <img src="https://images.unsplash.com/photo-1436491865332-7a61a109cc05?q=80&w=1600&auto=format&fit=crop" alt="" />
            <img src="https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?q=80&w=1600&auto=format&fit=crop" alt="" />
            <img src="https://images.unsplash.com/photo-1529078155058-5d716f45d604?q=80&w=1600&auto=format&fit=crop" alt="" />
          <?php endif; ?>
        </div>
        <span class="eyebrow" id="heroEyebrow"><?php echo htmlspecialchars($heroFirst['eyebrow'] ?? 'Safe Transportation & Logistics'); ?></span>
        <h1 id="heroTitle"><?php
          $t = (string)($heroFirst['title'] ?? 'Adaptable coordinated factors');
          $s = (string)($heroFirst['subtitle'] ?? 'Quick Conveyance');
          echo htmlspecialchars($t);
          echo '<br/>';
          echo htmlspecialchars($s);
        ?></h1>
        <p id="heroTagline"><?php echo htmlspecialchars($heroFirst['tagline'] ?? 'Reliable logistics solutions for every shipment. From pickup to delivery, track and manage your parcels with ease.'); ?></p>
        <div class="form-actions" style="margin-top:16px; display:flex; gap:12px;">
          <a id="heroCta1" class="btn btn-primary" href="<?php echo htmlspecialchars(($heroFirst['cta1_link'] ?? '/APLX/frontend/login.php') ?: '/APLX/frontend/login.php'); ?>" style="text-decoration:none;">
            <?php echo htmlspecialchars(($heroFirst['cta1_text'] ?? 'Get Started') ?: 'Get Started'); ?>
          </a>
          <a id="heroCta2" class="btn btn-secondary" href="<?php echo htmlspecialchars(($heroFirst['cta2_link'] ?? '#') ?: '#'); ?>" style="text-decoration:none;">
            <?php echo htmlspecialchars(($heroFirst['cta2_text'] ?? 'Learn More') ?: 'Learn More'); ?>
          </a>
        </div>
      </section>

  
    <!-- Services strip (icons 1..4) -->
    <section class="services" id="services">
      <div class="container">
      <div class="services-grid" id="servicesGrid">
        <div class="service-card reveal reveal-card stagger-1">
          <div class="service-icon">✈️</div>
          <div class="service-title">Air Freight</div>
          <div class="service-desc">Efficient and reliable air freight solutions for your business needs.</div>
        </div>
        <div class="service-card reveal reveal-card stagger-2">
          <div class="service-icon">🛳️</div>
          <div class="service-title">Ocean Freight</div>
          <div class="service-desc">Comprehensive ocean freight services worldwide.</div>
        </div>
        <div class="service-card reveal reveal-card stagger-3">
          <div class="service-icon">🚚</div>
          <div class="service-title">Land Transport</div>
          <div class="service-desc">Efficient land transportation solutions for all your needs.</div>
        </div>
        <div class="service-card reveal reveal-card stagger-1">
          <div class="service-icon">🏬</div>
          <div class="service-title">Warehousing</div>
          <div class="service-desc">Secure storage and inventory management.</div>
        </div>
      </div>
      </div>
    </section>

<!-- Services Tabs (left list + right image with red overlay) -->
    <section class="services-tabs" id="servicesTabs">
      <div class="container">
        <div class="st-grid">
          <aside class="st-left" role="tablist" aria-label="Services">
            <button class="st-item active" role="tab" aria-selected="true" data-img="images/cda6387f3ee1ca2a8f08f4e846dfcf59.jpg" data-bullets='["Fast Delivery","Safety","Good Package","Privacy"]'>
              <span class="st-icon">🛫</span>
              <span class="st-text">Air Transportation</span>
            </button>
            <button class="st-item" role="tab" aria-selected="false" data-img="images/truck-moving-shipping-container-min-1024x683.jpeg" data-bullets='["On-time","Tracking","Cost Effective","Secure"]'>
              <span class="st-icon">🚆</span>
              <span class="st-text">Train Transportation</span>
            </button>
            <button class="st-item" role="tab" aria-selected="false" data-img="images/premium_photo-1661962420310-d3be75c8921c.jpg" data-bullets='["Worldwide","Bulk Cargo","Insured","Reliable"]'>
              <span class="st-icon">🚢</span>
              <span class="st-text">Cargo Ship Freight</span>
            </button>
            <button class="st-item" role="tab" aria-selected="false" data-img="images/iStock-1024024568-scaled.jpg" data-bullets='["Climate Control","Inventory","Security","Compliance"]'>
              <span class="st-icon">🛳️</span>
              <span class="st-text">Maritime Transportation</span>
            </button>
            <button class="st-item" role="tab" aria-selected="false" data-img="images/COLOURBOX35652344.jpg" data-bullets='["Express","Priority Handling","Live Support","Customs Help"]'>
              <span class="st-icon">🛩️</span>
              <span class="st-text">Flight Transportation</span>
            </button>
          </aside>
          <div class="st-right">
            <div class="st-media">
              <img id="stImage" src="images/cda6387f3ee1ca2a8f08f4e846dfcf59.jpg" alt="Service preview">
              <div class="st-overlay">
                <div class="st-badge">🚚</div>
                <ul id="stBullets" class="st-bullets">
                  <li>Fast Delivery</li>
                  <li>Safety</li>
                  <li>Good Package</li>
                  <li>Privacy</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    </div>

    <!-- Transport system + secure packaging section with two feature bullets -->
    <section class="about-us">
      <div class="container">
        <div class="about-content">
          <div class="about-image reveal stagger-1">
            <img src="https://images.unsplash.com/photo-1537047902294-62a40c20a6ae?q=80&w=900&auto=format&fit=crop" alt="Containers and Truck" />
          </div>
          <div class="about-text">
            <div class="about-eyebrow reveal reveal-left stagger-2">Safe Transportation &amp; Logistics</div>
            <h2 class="reveal reveal-up stagger-2">Modern transport system &amp; secure packaging</h2>
            <p class="reveal reveal-right stagger-3">We combine real‑time visibility with secure handling to move your freight quickly and safely.</p>
            <div class="features-grid">
              <div class="feature-item reveal reveal-left stagger-1"><div class="feature-icon">🏬</div><div class="feature-content"><h4>Air Freight Transportation</h4><p>Fast air cargo across regions.</p></div></div>
              <div class="feature-item reveal reveal-right stagger-2"><div class="feature-icon">🚢</div><div class="feature-content"><h4>Ocean Freight Transportation</h4><p>Cost‑effective global lanes.</p></div></div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Why best in business (center image with two-left, two-right feature list) -->
    <section class="why-choose-us">
      <div class="container">
        <div class="why-choose-header">
          <h2>Why we are considered the best in business</h2>
          <p>Decentralized trade, direct transport, high flexibility and secure delivery.</p>
        </div>
        <div class="why-choose-layout">
          <div class="why-col left">
            <div class="why-item reveal stagger-1">
              <div class="why-icon">⬢</div>
              <div class="why-text">
                <h3>Decentralized Trade</h3>
                <p>Streamlined hubs maximize speed.</p>
              </div>
            </div>
            <div class="why-item reveal stagger-2">
              <div class="why-icon">➤</div>
              <div class="why-text">
                <h3>Direct Transport</h3>
                <p>Fewer touches, faster delivery.</p>
              </div>
            </div>
          </div>
          <div class="why-center reveal stagger-2">
            <img src="https://images.unsplash.com/photo-1529078155058-5d716f45d604?q=80&w=1600&auto=format&fit=crop" alt="Container in yard" />
          </div>
          <div class="why-col right">
            <div class="why-item reveal stagger-1">
              <div class="why-icon">⏱</div>
              <div class="why-text">
                <h3>Highly Flexible</h3>
                <p>Adaptable capacity and routes.</p>
              </div>
            </div>
            <div class="why-item reveal stagger-2">
              <div class="why-icon">⬛</div>
              <div class="why-text">
                <h3>Secure Delivery</h3>
                <p>Tamper‑evident packaging, QA.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact + Quote redesigned -->
    <section id="contact" class="contact-quote-section reveal">
      <div class="cq-wrap">
        <!-- Left info panel -->
        <div class="cq-left">
          <div class="cq-left-inner">
            <div class="about-eyebrow">Transport &amp; Logistics Services</div>
            <h2 class="cq-title">We are the best</h2>
            <p class="cq-sub">Transmds is the world's driving worldwide coordinations supplier — we uphold industry and exchange the world.</p>
            <ul class="cq-bullets">
              <li><span class="tick">✓</span> Preaching Worship An Online Family</li>
              <li><span class="tick">✓</span> Preaching Worship An Online Family</li>
            </ul>
            <div class="cq-mini">
              <img src="https://images.unsplash.com/photo-1436491865332-7a61a109cc05?q=80&w=1600&auto=format&fit=crop" alt="Air freight" />
              <div class="mini-text"><strong>Leading global logistic</strong><br/>and transport agency since <b>1990</b></div>
            </div>
          </div>
        </div>
        <!-- Right quote form panel -->
        <div class="cq-right">
          <div class="cq-right-inner">
            <h2>Request a quote form</h2>
            <form id="homeQuoteForm" class="cq-form">
              <div class="cq-row">
                <input id="hqName" name="name" type="text" placeholder="Your Name" required>
              </div>
              <div class="cq-row two">
                <input id="hqEmail" name="email" type="email" placeholder="Email" required>
                <input id="hqPhone" name="phone" type="tel" placeholder="Phone">
              </div>
              <div class="cq-row">
                <input id="hqCity" name="delivery_city" type="text" placeholder="Delivery City" required>
              </div>
              <div class="cq-row two">
                <select id="hqFreight" name="freight_type" required>
                  <option value="">Freight Type</option>
                  <option>Air</option>
                  <option>Ocean</option>
                  <option>Land</option>
                </select>
                <select id="hqIncoterms" name="incoterms" required>
                  <option value="">Incoterms</option>
                  <option>FOB</option>
                  <option>CIF</option>
                  <option>DDP</option>
                </select>
              </div>
              <div class="cq-row checks">
                <label><input type="checkbox" name="fragile"> Fragile</label>
                <label><input type="checkbox" name="express"> Express delivery</label>
                <label><input type="checkbox" name="insurance"> Insurance</label>
              </div>
              <div class="cq-row">
                <textarea id="hqMessage" name="message" placeholder="Your Message" rows="4"></textarea>
              </div>
              <button id="hqSubmit" type="submit" class="cq-submit">Send Message</button>
            </form>
            <div id="homeQuoteStatus" class="inline-status" aria-live="polite"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats hero (image left, title + metrics right) -->
    <section class="stats-hero">
      <div class="container">
        <div class="stats-hero-grid">
          <div class="stats-hero-image">
            <img src="https://images.unsplash.com/photo-1550317138-10000687a72b?q=80&w=1600&auto=format&fit=crop" alt="Cargo ship at sea">
          </div>
          <div class="stats-hero-content">
            <h2>We Provide Full Assistance in Freight &amp; Warehousing</h2>
            <p>Comprehensive ocean, air, and land freight backed by modern warehousing. Track, optimize, and scale with confidence.</p>
            <div class="stats-cards">
              <div class="stat-card stat-red">
                <div class="stat-number">35+</div>
                <div class="stat-label">Countries Represented</div>
              </div>
              <div class="stat-card stat-navy">
                <div class="stat-number">853+</div>
                <div class="stat-label">Projects completed</div>
              </div>
              <div class="stat-card stat-yellow">
                <div class="stat-number">35+</div>
                <div class="stat-label">Total Revenue</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Auto-scrolling gallery (10 images) -->
    <section class="transport-gallery">
      <div class="container">
        <div class="tg-slider">
          <div class="tg-track" id="tgTrack" style="--tg-duration:24s; --tg-shift:-2400px;">
            <!-- 10 cards -->
            <article class="tg-item news-card">
              <span class="date-badge"><strong>25</strong><small>Dec</small></span>
              <img src="images/truck-moving-shipping-container-min-1024x683.jpeg" alt="Truck at dock">
              <span class="tag-pill">Transport</span>
            </article>
            <article class="tg-item news-card">
              <span class="date-badge"><strong>20</strong><small>Dec</small></span>
              <img src="images/premium_photo-1661962420310-d3be75c8921c.jpg" alt="Cargo ships">
              <span class="tag-pill">Transport</span>
            </article>
            <article class="tg-item news-card">
              <span class="date-badge"><strong>30</strong><small>Dec</small></span>
              <img src="images/premium_photo-1661932015882-c35eee885897.jpg" alt="Port container ship">
              <span class="tag-pill">Transport</span>
            </article>
            <article class="tg-item news-card">
              <span class="date-badge"><strong>05</strong><small>Jan</small></span>
              <img src="images/iStock-1024024568-scaled.jpg" alt="Warehouse pallets">
              <span class="tag-pill">Warehouse</span>
            </article>
            <article class="tg-item news-card">
              <span class="date-badge"><strong>12</strong><small>Jan</small></span>
              <img src="images/pngtree-truck-delivering-packages-across-a-3d-world-map-picture-image_3756058.jpg" alt="Truck highway">
              <span class="tag-pill">Logistics</span>
            </article>
            <article class="tg-item news-card">
              <span class="date-badge"><strong>18</strong><small>Jan</small></span>
              <img src="images/COLOURBOX35652344.jpg" alt="Forklift loading">
              <span class="tag-pill">Warehouse</span>
            </article>
            <article class="tg-item news-card">
              <span class="date-badge"><strong>24</strong><small>Jan</small></span>
              <img src="images/cda6387f3ee1ca2a8f08f4e846dfcf59.jpg" alt="Cargo containers">
              <span class="tag-pill">Shipping</span>
            </article>
            <article class="tg-item news-card">
              <span class="date-badge"><strong>01</strong><small>Feb</small></span>
              <img src="images/premium_photo-1661962420310-d3be75c8921c.jpg" alt="Air freight">
              <span class="tag-pill">Air</span>
            </article>
            <article class="tg-item news-card">
              <span class="date-badge"><strong>06</strong><small>Feb</small></span>
              <img src="images/iStock-1024024568-scaled.jpg" alt="Warehouse aisle">
              <span class="tag-pill">Warehouse</span>
            </article>
            <article class="tg-item news-card">
              <span class="date-badge"><strong>14</strong><small>Feb</small></span>
              <img src="images/truck-moving-shipping-container-min-1024x683.jpeg" alt="Port cranes">
              <span class="tag-pill">Transport</span>
            </article>
          </div>
        </div>
      </div>
    </section>

    <!-- Help + Quote (two column) -->
    <section class="help-quote">
      <div class="hq-wrap">
        <!-- Left: Help info -->
        <div class="hq-left">
          <div class="hq-left-inner">
            <h2>Need Help With Your Shipping?</h2>
            <p>Our team is here to help you with all your logistics needs. Contact us today for a free quote.</p>
            <div class="hq-card">
              <div class="hq-card-icon">📞</div>
              <div>
                <div class="hq-card-title">Call Us Anytime</div>
                <div class="hq-card-sub">+94 21 492 7799</div>
              </div>
            </div>
            <div class="hq-card">
              <div class="hq-card-icon">✉️</div>
              <div>
                <div class="hq-card-title">Email Us</div>
                <div class="hq-card-sub">info@slgti.com</div>
              </div>
            </div>
            <div class="hq-card">
              <div class="hq-card-icon">📍</div>
              <div>
                <div class="hq-card-title">Visit Us</div>
                <div class="hq-card-sub">Ariviyal Nagar, Kilinochchi, Sri Lanka</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Quote form -->
        <div class="hq-right">
          <div class="hq-right-inner">
            <h2>Get A Free Quote</h2>
            <p>Fill out the form below and our team will get back to you as soon as possible.</p>
            <form id="quickQuoteForm" class="hq-form">
              <div class="hq-row two">
                <input name="name" type="text" placeholder="Your Name" required>
                <input name="email" type="email" placeholder="Your Email" required>
              </div>
              <div class="hq-row">
                <input name="subject" type="text" placeholder="Subject" required>
              </div>
              <div class="hq-row">
                <select name="service" required>
                  <option value="">Select Service</option>
                  <option>Air Freight</option>
                  <option>Ocean Freight</option>
                  <option>Land Transport</option>
                  <option>Warehousing</option>
                </select>
              </div>
              <div class="hq-row">
                <textarea name="message" rows="4" placeholder="Your Message"></textarea>
              </div>
              <button type="submit" class="hq-submit">Send Message</button>
            </form>
            <div id="quickQuoteStatus" class="inline-status" aria-live="polite"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Map -->
    <section class="location-map-section">
      <div class="map-bg" aria-hidden="true" style="background-image:url('https://staticmap.openstreetmap.de/staticmap.php?center=9.6574,80.1628&zoom=12&size=1600x900&maptype=mapnik&format=png');"></div>
      <div class="container">
        <div class="map-header">
          <h2>Find Us Here</h2>
          <p>Visit our office location in Kilinochchi, Sri Lanka</p>
        </div>
        <div class="map-container">
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d127317.59409384069!2d80.04778896986493!3d9.664430939428727!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3afe53c9c5a7a7c5%3A0x9b2b9a5f7b0d1d0!2sAriviyal%20Nagar!5e0!3m2!1sen!2slk!4v1700000000000" width="100%" height="320" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>

        <!-- Contact info cards below the map -->
        <div class="contact-info-cards">
          <div class="contact-card">
            <div class="contact-icon">📍</div>
            <div>
              <h4>Address</h4>
              <p>Ariviyal Nagar, Kilinochchi, Sri Lanka</p>
            </div>
          </div>
          <div class="contact-card">
            <div class="contact-icon">🕒</div>
            <div>
              <h4>Business Hours</h4>
              <ul>
                <li>Mon - Fri: 8:30 AM - 4:15 PM</li>
                <li>Sat: 9:00 AM - 2:00 PM</li>
                <li>Sun: Closed</li>
              </ul>
            </div>
          </div>
          <div class="contact-card">
            <div class="contact-icon">📞</div>
            <div>
              <h4>Contact</h4>
              <p>Phone: <a href="tel:+94214927799">+94 21 492 7799</a><br/>Email: <a href="mailto:info@slgti.com">info@slgti.com</a></p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-col">
          <div class="footer-logo"><span class="brand-icon">🚚</span> Parcel Transport</div>
          <p class="footer-description">Your reliable logistics partner for all your transportation and supply chain needs. We deliver excellence with every shipment.</p>
          <div class="social-links">
            <a href="#" aria-label="Facebook">f</a>
            <a href="#" aria-label="Twitter">t</a>
            <a href="#" aria-label="Instagram">ig</a>
            <a href="#" aria-label="LinkedIn">in</a>
          </div>
        </div>
        <div class="footer-col">
          <div class="footer-title">Quick Links</div>
          <ul class="footer-links">
            <li><a href="/APLX/">Home</a></li>
            <li><a href="#about">About Us</a></li>
            <li><a href="#services">Services</a></li>
            <li><a href="track.php">Track Shipment</a></li>
            <li><a href="#contact">Contact Us</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <div class="footer-title">Our Services</div>
          <ul class="footer-links">
            <li><a href="#">Air Freight</a></li>
            <li><a href="#">Ocean Freight</a></li>
            <li><a href="#">Land Transport</a></li>
            <li><a href="#">Warehousing</a></li>
            <li><a href="#">Supply Chain</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <div class="footer-title">Contact Info</div>
          <ul class="footer-contact">
            <li>📍 Sri Lanka German Training Institute, Ariviyal Nagar, Kilinochchi, Sri Lanka</li>
            <li>📞 <a href="tel:+94214927799">+94 21 492 7799</a></li>
            <li>✉️ <a href="mailto:info@slgti.com">info@slgti.com</a></li>
            <li>⏰ Mon – Fri: 8:30 A.M – 16:15 P.M</li>
          </ul>
        </div>
      </div>
      <hr class="footer-divider" />
      <div class="footer-bottom">
        <div>© 2025 Parcel Transport. All rights reserved.</div>
        <div class="footer-bottom-links">
          <a href="#">Privacy Policy</a>
          <span class="sep">|</span>
          <a href="#">Terms &amp; Conditions</a>
        </div>
      </div>
    </div>
  </footer>
  <script>
  // Services interactive grid
  (function() { const grid=document.querySelector('.services-grid'); if(!grid) return; const cards=[...grid.querySelectorAll('.service-card')]; let selected=null; function toggle(){ if(selected===this){ this.classList.remove('selected'); this.setAttribute('aria-selected','false'); grid.classList.remove('dim-others'); selected=null; return;} cards.forEach(c=>{c.classList.remove('selected'); c.setAttribute('aria-selected','false');}); this.classList.add('selected'); this.setAttribute('aria-selected','true'); grid.classList.add('dim-others'); selected=this; } cards.forEach(card=>{ card.setAttribute('tabindex','0'); card.setAttribute('role','button'); card.addEventListener('click',toggle); card.addEventListener('keydown',e=>{ if(e.key==='Enter'||e.key===' '){ e.preventDefault(); toggle.call(card);} }); }); })();
  // Dynamic Services: load from backend if available, fallback to static
  (function(){
    const grid = document.getElementById('servicesGrid');
    if (!grid) return;
    function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m])); }
    fetch('/APLX/backend/services_list.php', { cache: 'no-store' })
      .then(r => r.ok ? r.json() : Promise.reject(r.status))
      .then(data => {
        const items = (data && Array.isArray(data.items)) ? data.items.slice(0,4) : [];
        if (!items.length) return; // keep static
        grid.innerHTML = items.map((it,i)=>{
          const icon = it.icon ? escapeHtml(it.icon) : '';
          const title = escapeHtml(it.title);
          const desc = escapeHtml(it.description);
          const img = it.image_url ? `<img src="${escapeHtml(it.image_url)}" alt="" style="width:48px;height:48px;border-radius:999px;object-fit:cover;border:1px solid var(--border);">` : '';
          const iconHtml = icon ? `<div class=\"service-icon\">${icon}</div>` : (img ? `<div class=\"service-icon\">${img}</div>` : '<div class="service-icon">⬢</div>');
          const cls = 'service-card reveal reveal-card stagger-' + ((i%3)+1);
          return `<div class="${cls}">${iconHtml}<div class="service-title">${title}</div><div class="service-desc">${desc}</div></div>`;
        }).join('');
      })
      .catch(()=>{});
  })();
  // Transport gallery: fetch from backend, render, then enable seamless scroll
  (function(){
    const track = document.querySelector('.transport-gallery .tg-track');
    if (!track) return;
    const slider = track.closest('.tg-slider');

    function buildItems(items){
      function esc(s){ return String(s||'').replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m])); }
      if (!Array.isArray(items) || !items.length) return false;
      track.innerHTML = items.map(it => {
        const day = it.day ? String(it.day).padStart(2,'0') : '';
        const month = it.month ? esc(String(it.month).slice(0,3)) : '';
        const tag = it.tag ? esc(it.tag) : 'Transport';
        const img = esc(it.image_url || '');
        if (!img) return '';
        const badge = (day||month) ? `<span class="date-badge"><strong>${day}</strong><small>${month}</small></span>` : '';
        return `<article class="tg-item news-card">${badge}<img src="${img}" alt=""><span class="tag-pill">${tag}</span></article>`;
      }).join('');
      return !!track.children.length;
    }

    function enableScroll(){
      const getGap = () => parseFloat(getComputedStyle(track).gap || '0');
      const totalWidth = () => Array.from(track.children).reduce((w, el) => w + el.getBoundingClientRect().width, 0) + (track.children.length - 1) * getGap();
      const itemsNow = Array.from(track.children);
      if (!itemsNow.length || itemsNow.length === 1) { track.style.animation = 'none'; return; }
      const base = Array.from(itemsNow);
      let guard = 0;
      let target = (slider?.getBoundingClientRect().width || 800) * 2 + 100;
      while (totalWidth() < target && guard < 50) {
        base.forEach(el => {
          const clone = el.cloneNode(true);
          clone.setAttribute('aria-hidden','true');
          track.appendChild(clone);
        });
        guard += base.length;
      }
      const shift = -Math.round(totalWidth() / 2);
      track.style.setProperty('--tg-shift', shift + 'px');
      const speed = 120; // px/sec
      const duration = Math.abs(shift) / speed;
      track.style.setProperty('--tg-duration', duration + 's');
      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        track.style.animation = 'none';
      }
    }

    function init(){ enableScroll(); }

    // Try backend first, fallback to existing static markup
    fetch('/APLX/backend/gallery_list.php', { cache: 'no-store' })
      .then(r => r.ok ? r.json() : Promise.reject(r.status))
      .then(data => {
        const ok = data && Array.isArray(data.items) && buildItems(data.items);
        if (!ok) { /* keep static */ }
      })
      .catch(() => { /* keep static */ })
      .finally(() => {
        if (document.readyState === 'complete') init(); else window.addEventListener('load', init);
      });
  })();

  // Dynamic Book link: require customer login
  (function(){
    const book = document.getElementById('navBook');
    if (!book) return;
    const isLoggedIn = localStorage.getItem('isLoggedIn') === '1';
    const role = localStorage.getItem('userRole') || 'customer';
    const loggedCustomer = isLoggedIn && role === 'customer';
    book.href = loggedCustomer
      ? '/APLX/frontend/customer/book.php'
      : '/APLX/frontend/login.php?next=%2FAPLX%2Ffrontend%2Fcustomer%2Fbook.php';
  })();
  // Hero cross-fade
  (function(){
    const slideshow = document.querySelector('.hero .slideshow');
    if (!slideshow) return;
    const slides = [...slideshow.querySelectorAll('img')];
    if (slides.length <= 1) return;
    let idx = 0;
    function show(i){
      slides.forEach((img, k) => img.classList.toggle('active', k === i));
    }
    show(0);
    setInterval(()=>{
      idx = (idx + 1) % slides.length;
      show(idx);
    }, 5000);
  })();
  // Reveal on scroll
  (function(){ const sels=['.services .service-card','.about-us .about-image','.about-us .about-text','.about-us .feature-item','.why-choose-us .our-services-grid > *','.contact-quote-section .contact-info','.contact-quote-section .quote-form','.location-map-section .map-header *']; sels.forEach(sel=>{ document.querySelectorAll(sel).forEach(el=>{ el.classList.add('reveal','reveal-replay'); }); }); const els=document.querySelectorAll('.reveal'); if(!('IntersectionObserver' in window)){ els.forEach(el=>el.classList.add('reveal-visible')); return;} const io=new IntersectionObserver((entries)=>{ entries.forEach(entry=>{ const el=entry.target; if(entry.isIntersecting){ el.classList.add('reveal-visible'); } else if(el.classList.contains('reveal-replay')){ el.classList.remove('reveal-visible'); } }); }, {rootMargin:'0px 0px -15% 0px', threshold:0.1}); els.forEach(el=>io.observe(el)); })();

  // Theme toggle + persist
  (function(){
    const btn = document.getElementById('themeToggle');
    const saved = localStorage.getItem('theme');
    if (saved) document.documentElement.setAttribute('data-theme', saved);
    function updateVisual(){
      const cur = document.documentElement.getAttribute('data-theme') || 'dark';
      if (btn) {
        btn.textContent = cur === 'light' ? '☀️' : '🌙';
        btn.setAttribute('aria-pressed', String(cur !== 'light'));
        btn.setAttribute('aria-label', cur === 'light' ? 'Switch to dark mode' : 'Switch to light mode');
      }
    }
    function setTheme(t){ document.documentElement.setAttribute('data-theme', t); localStorage.setItem('theme', t); updateVisual(); }
    updateVisual();
    btn?.addEventListener('click', ()=>{
      const cur = document.documentElement.getAttribute('data-theme') || 'dark';
      setTheme(cur === 'light' ? 'dark' : 'light');
    });
  })();

  // Services Tabs logic (hover to activate)
  (function(){
    const root = document.getElementById('servicesTabs');
    if (!root) return;
    const items = root.querySelectorAll('.st-item');
    const imgEl = document.getElementById('stImage');
    const bulletsEl = document.getElementById('stBullets');

    function activate(btn){
      items.forEach(b=>{ b.classList.remove('active'); b.setAttribute('aria-selected','false'); });
      btn.classList.add('active');
      btn.setAttribute('aria-selected','true');
      const url = btn.getAttribute('data-img');
      const bullets = JSON.parse(btn.getAttribute('data-bullets')||'[]');
      if (url && imgEl) imgEl.src = url;
      if (bulletsEl){ bulletsEl.innerHTML = bullets.map(t => `<li>✓ ${t}</li>`).join(''); }
    }

    items.forEach(btn=>{
      btn.addEventListener('mouseenter', ()=> activate(btn));
      btn.addEventListener('focus', ()=> activate(btn));
      btn.addEventListener('click', ()=> activate(btn));
    });

    // Initialize first/active
    const initial = root.querySelector('.st-item.active') || items[0];
    if (initial) activate(initial);
  })();

  // Mouse spotlight
  (function(){
    const layer = document.getElementById('spotlight');
    if (!layer) return;
    const textSel = 'p, h1, h2, h3, h4, h5, h6, a, span, li, label, small, strong, em, .service-title, .service-desc, .feature-content, .why-choose-header, .our-service-content, .contact-info, .map-header, .brand, nav a, .btn, .eyebrow, .footer';
    window.addEventListener('mousemove', (e)=>{
      layer.style.setProperty('--mx', e.clientX + 'px');
      layer.style.setProperty('--my', e.clientY + 'px');
      const el = document.elementFromPoint(e.clientX, e.clientY);
      const isText = !!(el && (el.matches(textSel) || el.closest(textSel)));
      document.body.classList.toggle('text-spot', isText);
    }, { passive: true });
    window.addEventListener('mouseleave', ()=>{ document.body.classList.remove('text-spot'); });
  })();
  </script>
  <!-- Mobile Track Modal -->
  <div id="trackModal" class="modal-backdrop" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal-panel">
      <div class="modal-header">
        <h3 class="modal-title">Track Shipment</h3>
        <button class="modal-close" id="trackModalClose" type="button" aria-label="Close">✕</button>
      </div>
      <div class="modal-body">
        <form method="get" action="/APLX/backend/track_result.php">
          <div class="track-form-row">
            <input type="text" name="tn" placeholder="Enter Tracking Number" required>
            <button class="btn" type="submit">Track</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
  // Mobile-only: open Track modal instead of navigating
  (function(){
    const mq = window.matchMedia('(max-width: 640px)');
    const navLink = document.querySelector('.navbar nav a[href$="/track.php"], .navbar nav a[href$="track.php"]');
    const modal = document.getElementById('trackModal');
    const closeBtn = document.getElementById('trackModalClose');
    if (!navLink || !modal) return;
    function open(){ modal.classList.add('open'); modal.setAttribute('aria-hidden','false'); document.body.style.overflow='hidden'; }
    function close(){ modal.classList.remove('open'); modal.setAttribute('aria-hidden','true'); document.body.style.overflow=''; }
    navLink.addEventListener('click', (e)=>{ if (mq.matches){ e.preventDefault(); open(); } });
    closeBtn?.addEventListener('click', close);
    modal.addEventListener('click', (e)=>{ if (e.target === modal) close(); });
    window.addEventListener('keydown', (e)=>{ if (e.key === 'Escape') close(); });
  })();
  </script>
</body>
</html>




