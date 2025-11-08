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
      // Load services (top 4 by sort_order, id)
      $services = [];
      if ($r = $conn->query("SELECT icon_url, image_url, title, description, sort_order FROM services ORDER BY sort_order ASC, id ASC LIMIT 4")) {
        while ($row = $r->fetch_assoc()) { $services[] = $row; }
      }
      // Load gallery items (limit 10 most recent by id desc; if sort_order exists use it)
      $gallery = [];
      if ($g = $conn->query("SELECT image_url, tag, day, month FROM gallery ORDER BY sort_order ASC, id ASC")) {
        while ($row = $g->fetch_assoc()) { $gallery[] = $row; }
      }
      // Load contact details (single row id=1)
      $contact = ['address'=>'','phone'=>'','email'=>'','hours_weekday'=>'','hours_sat'=>'','hours_sun'=>''];
      if ($c = $conn->query("SELECT * FROM site_contact WHERE id=1")) {
        $row = $c->fetch_assoc();
        if ($row) { $contact = $row; }
      }
      // Load map section (single row id=1)
      $map = [
        'header_title' => 'Find Us Here',
        'header_subtext' => 'Visit our office location in Kilinochchi, Sri Lanka',
        'map_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d127317.59409384069!2d80.04778896986493!3d9.664430939428727!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3afe53c9c5a7a7c5%3A0x9b2b9a5f7b0d1d0!2sAriviyal%20Nagar!5e0!3m2!1sen!2slk!4v1700000000000'
      ];
      if ($m = $conn->query("SELECT * FROM map_section WHERE id=1")) {
        $row = $m->fetch_assoc();
        if ($row) { $map = array_merge($map, $row); }
      }

      // Load Help + Quote (left panel) dynamic content
      $hq = [
        'eyebrow' => 'Transport & Logistics Services',
        'title' => 'We are the best',
        'subtext' => "Transmds is the world's driving worldwide coordinations supplier — we uphold industry and exchange the world.",
        'bullets_text' => "Preaching Worship An Online Family\nPreaching Worship An Online Family",
        'mini_image_url' => '',
        'mini_title' => 'Leading global logistic',
        'mini_sub' => 'and transport agency since 1990',
      ];
      try {
        if ($q = $conn->query('SELECT * FROM help_quote WHERE id=1')){
          $row = $q->fetch_assoc();
          if ($row) { $hq = array_merge($hq, $row); }
        }
      } catch (Throwable $e) { /* keep defaults */ }
      // Load home stats (single row id=1)
      $homeStats = [
        'hero_title'   => 'We Provide Full Assistance in Freight & Warehousing',
        'hero_subtext' => 'Comprehensive ocean, air, and land freight backed by modern warehousing. Track, optimize, and scale with confidence.',
        'image_url'    => '',
        'stat1_number' => '35+', 'stat1_label' => 'Countries Represented',
        'stat2_number' => '853+', 'stat2_label' => 'Projects completed',
        'stat3_number' => '35+', 'stat3_label' => 'Total Revenue',
      ];
      if ($h = $conn->query("SELECT * FROM home_stats WHERE id=1")) {
        $row = $h->fetch_assoc();
        if ($row) { $homeStats = array_merge($homeStats, $row); }
      }
      // Load about section (single row id=1)
      $about = [
        'eyebrow' => 'Safe Transportation & Logistics',
        'title'   => 'Modern transport system & secure packaging',
        'subtext' => 'We combine real‑time visibility with secure handling to move your freight quickly and safely.',
        'image_url' => '',
        'feature1_icon_text' => '',
        'feature1_icon_url'  => '',
        'feature1_title'     => 'Air Freight Transportation',
        'feature1_desc'      => 'Fast air cargo across regions.',
        'feature2_icon_text' => '',
        'feature2_icon_url'  => '',
        'feature2_title'     => 'Ocean Freight Transportation',
        'feature2_desc'      => 'Cost‑effective global lanes.',
      ];
      if ($a = $conn->query("SELECT * FROM about_section WHERE id=1")) {
        $row = $a->fetch_assoc();
        if ($row) { $about = array_merge($about, $row); }
      }
      // Load why-best section (single row id=1)
      $why = [
        'header_title' => 'Why we are considered the best in business',
        'header_subtext' => 'Decentralized trade, direct transport, high flexibility and secure delivery.',
        'center_image_url' => '',
        'f1_icon_text' => '⬢', 'f1_icon_url' => '', 'f1_title' => 'Decentralized Trade', 'f1_desc' => 'Streamlined hubs maximize speed.',
        'f2_icon_text' => '➤', 'f2_icon_url' => '', 'f2_title' => 'Direct Transport', 'f2_desc' => 'Fewer touches, faster delivery.',
        'f3_icon_text' => '⏱', 'f3_icon_url' => '', 'f3_title' => 'Highly Flexible', 'f3_desc' => 'Adaptable capacity and routes.',
        'f4_icon_text' => '⬛', 'f4_icon_url' => '', 'f4_title' => 'Secure Delivery', 'f4_desc' => 'Tamper‑evident packaging, QA.',
      ];
      if ($w = $conn->query("SELECT * FROM why_best WHERE id=1")) {
        $row = $w->fetch_assoc();
        if ($row) { $why = array_merge($why, $row); }
      }
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
          <a id="heroCta1" class="btn btn-primary" href="/APLX/frontend/customer/register.php" style="text-decoration:none;">
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
        <?php if (!empty($services)): ?>
          <?php foreach ($services as $i => $s): ?>
            <div class="service-card reveal reveal-card stagger-<?php echo (($i % 3) + 1); ?>">
              <div class="service-icon">
                <?php if (!empty($s['icon_url'])): ?>
                  <img src="<?php echo htmlspecialchars($s['icon_url']); ?>" alt="" style="width:48px;height:48px;border-radius:999px;object-fit:cover;border:1px solid var(--border);" />
                <?php elseif (!empty($s['image_url'])): ?>
                  <img src="<?php echo htmlspecialchars($s['image_url']); ?>" alt="" style="width:48px;height:48px;border-radius:999px;object-fit:cover;border:1px solid var(--border);" />
                <?php else: ?>
                  <span style="display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;border-radius:999px;border:1px solid var(--border);">⬢</span>
                <?php endif; ?>
              </div>
              <div class="service-title"><?php echo htmlspecialchars($s['title'] ?? ''); ?></div>
              <div class="service-desc"><?php echo htmlspecialchars($s['description'] ?? ''); ?></div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="service-card reveal reveal-card stagger-1">
            <div class="service-icon"> </div>
            <div class="service-title">Air Freight</div>
            <div class="service-desc">Efficient and reliable air freight solutions for your business needs.</div>
          </div>
          <div class="service-card reveal reveal-card stagger-2">
            <div class="service-icon"> </div>
            <div class="service-title">Ocean Freight</div>
            <div class="service-desc">Comprehensive ocean freight services worldwide.</div>
          </div>
          <div class="service-card reveal reveal-card stagger-3">
            <div class="service-icon"> </div>
            <div class="service-title">Land Transport</div>
            <div class="service-desc">Efficient land transportation solutions for all your needs.</div>
          </div>
          <div class="service-card reveal reveal-card stagger-1">
            <div class="service-icon"> </div>
            <div class="service-title">Warehousing</div>
            <div class="service-desc">Secure storage and inventory management.</div>
          </div>
        <?php endif; ?>
      </div>
      </div>
    </section>

<!-- Services Tabs (left list + right image with red overlay) -->
    <section class="services-tabs" id="servicesTabs">
      <div class="container">
        <div class="st-grid">
          <aside class="st-left" role="tablist" aria-label="Services">
            <button class="st-item active" role="tab" aria-selected="true" data-img="images/cda6387f3ee1ca2a8f08f4e846dfcf59.jpg" data-bullets='["Fast Delivery","Safety","Good Package","Privacy"]'>
              <span class="st-icon"> </span>
              <span class="st-text">Air Transportation</span>
            </button>
            <button class="st-item" role="tab" aria-selected="false" data-img="images/truck-moving-shipping-container-min-1024x683.jpeg" data-bullets='["On-time","Tracking","Cost Effective","Secure"]'>
              <span class="st-icon"> </span>
              <span class="st-text">Train Transportation</span>
            </button>
            <button class="st-item" role="tab" aria-selected="false" data-img="images/premium_photo-1661962420310-d3be75c8921c.jpg" data-bullets='["Worldwide","Bulk Cargo","Insured","Reliable"]'>
              <span class="st-icon"> </span>
              <span class="st-text">Cargo Ship Freight</span>
            </button>
            <button class="st-item" role="tab" aria-selected="false" data-img="images/iStock-1024024568-scaled.jpg" data-bullets='["Climate Control","Inventory","Security","Compliance"]'>
              <span class="st-icon"> </span>
              <span class="st-text">Maritime Transportation</span>
            </button>
            <button class="st-item" role="tab" aria-selected="false" data-img="images/COLOURBOX35652344.jpg" data-bullets='["Express","Priority Handling","Live Support","Customs Help"]'>
              <span class="st-icon"> </span>
              <span class="st-text">Flight Transportation</span>
            </button>
          </aside>
          <div class="st-right">
            <div class="st-media">
              <img id="stImage" src="images/cda6387f3ee1ca2a8f08f4e846dfcf59.jpg" alt="Service preview">
              <div class="st-overlay">
                <div class="st-badge"> </div>
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

    <!-- Transport system + secure packaging section with two feature bullets (dynamic) -->
    <section class="about-us">
      <div class="container">
        <div class="about-content">
          <div class="about-image reveal stagger-1">
            <?php $aboutImg = trim((string)($about['image_url'] ?? '')); ?>
            <img src="<?php echo htmlspecialchars($aboutImg ?: 'https://images.unsplash.com/photo-1537047902294-62a40c20a6ae?q=80&w=900&auto=format&fit=crop'); ?>" alt="Containers and Truck" />
          </div>
          <div class="about-text">
            <div class="about-eyebrow reveal reveal-left stagger-2"><?php echo htmlspecialchars($about['eyebrow'] ?? ''); ?></div>
            <h2 class="reveal reveal-up stagger-2"><?php echo htmlspecialchars($about['title'] ?? ''); ?></h2>
            <p class="reveal reveal-right stagger-3"><?php echo htmlspecialchars($about['subtext'] ?? ''); ?></p>
            <div class="features-grid">
              <?php $f1icon = trim((string)($about['feature1_icon_url'] ?? '')); $f1txt = trim((string)($about['feature1_icon_text'] ?? '')); ?>
              <div class="feature-item reveal reveal-left stagger-1">
                <div class="feature-icon">
                  <?php if ($f1icon): ?><img src="<?php echo htmlspecialchars($f1icon); ?>" alt="" style="width:28px;height:28px;border-radius:6px;border:1px solid var(--border);object-fit:cover" /><?php else: ?><?php echo htmlspecialchars($f1txt ?: ' '); ?><?php endif; ?>
                </div>
                <div class="feature-content"><h4><?php echo htmlspecialchars($about['feature1_title'] ?? ''); ?></h4><p><?php echo htmlspecialchars($about['feature1_desc'] ?? ''); ?></p></div>
              </div>
              <?php $f2icon = trim((string)($about['feature2_icon_url'] ?? '')); $f2txt = trim((string)($about['feature2_icon_text'] ?? '')); ?>
              <div class="feature-item reveal reveal-right stagger-2">
                <div class="feature-icon">
                  <?php if ($f2icon): ?><img src="<?php echo htmlspecialchars($f2icon); ?>" alt="" style="width:28px;height:28px;border-radius:6px;border:1px solid var(--border);object-fit:cover" /><?php else: ?><?php echo htmlspecialchars($f2txt ?: ' '); ?><?php endif; ?>
                </div>
                <div class="feature-content"><h4><?php echo htmlspecialchars($about['feature2_title'] ?? ''); ?></h4><p><?php echo htmlspecialchars($about['feature2_desc'] ?? ''); ?></p></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Why best in business (dynamic) -->
    <section class="why-choose-us">
      <div class="container">
        <div class="why-choose-header">
          <h2><?php echo htmlspecialchars($why['header_title'] ?? ''); ?></h2>
          <p><?php echo htmlspecialchars($why['header_subtext'] ?? ''); ?></p>
        </div>
        <div class="why-choose-layout">
          <div class="why-col left">
            <div class="why-item reveal stagger-1">
              <div class="why-icon">
                <?php if (!empty($why['f1_icon_url'])): ?><img src="<?php echo htmlspecialchars($why['f1_icon_url']); ?>" alt="" style="width:24px;height:24px;border:1px solid var(--border);border-radius:6px;object-fit:cover" /><?php else: ?><?php echo htmlspecialchars($why['f1_icon_text'] ?? ''); ?><?php endif; ?>
              </div>
              <div class="why-text">
                <h3><?php echo htmlspecialchars($why['f1_title'] ?? ''); ?></h3>
                <p><?php echo htmlspecialchars($why['f1_desc'] ?? ''); ?></p>
              </div>
            </div>
            <div class="why-item reveal stagger-2">
              <div class="why-icon">
                <?php if (!empty($why['f2_icon_url'])): ?><img src="<?php echo htmlspecialchars($why['f2_icon_url']); ?>" alt="" style="width:24px;height:24px;border:1px solid var(--border);border-radius:6px;object-fit:cover" /><?php else: ?><?php echo htmlspecialchars($why['f2_icon_text'] ?? ''); ?><?php endif; ?>
              </div>
              <div class="why-text">
                <h3><?php echo htmlspecialchars($why['f2_title'] ?? ''); ?></h3>
                <p><?php echo htmlspecialchars($why['f2_desc'] ?? ''); ?></p>
              </div>
            </div>
          </div>
          <div class="why-center reveal stagger-2">
            <?php $whyImg = trim((string)($why['center_image_url'] ?? '')); ?>
            <img src="<?php echo htmlspecialchars($whyImg ?: 'https://images.unsplash.com/photo-1529078155058-5d716f45d604?q=80&w=1600&auto=format&fit=crop'); ?>" alt="Center" />
          </div>
          <div class="why-col right">
            <div class="why-item reveal stagger-1">
              <div class="why-icon">
                <?php if (!empty($why['f3_icon_url'])): ?><img src="<?php echo htmlspecialchars($why['f3_icon_url']); ?>" alt="" style="width:24px;height:24px;border:1px solid var(--border);border-radius:6px;object-fit:cover" /><?php else: ?><?php echo htmlspecialchars($why['f3_icon_text'] ?? ''); ?><?php endif; ?>
              </div>
              <div class="why-text">
                <h3><?php echo htmlspecialchars($why['f3_title'] ?? ''); ?></h3>
                <p><?php echo htmlspecialchars($why['f3_desc'] ?? ''); ?></p>
              </div>
            </div>
            <div class="why-item reveal stagger-2">
              <div class="why-icon">
                <?php if (!empty($why['f4_icon_url'])): ?><img src="<?php echo htmlspecialchars($why['f4_icon_url']); ?>" alt="" style="width:24px;height:24px;border:1px solid var(--border);border-radius:6px;object-fit:cover" /><?php else: ?><?php echo htmlspecialchars($why['f4_icon_text'] ?? ''); ?><?php endif; ?>
              </div>
              <div class="why-text">
                <h3><?php echo htmlspecialchars($why['f4_title'] ?? ''); ?></h3>
                <p><?php echo htmlspecialchars($why['f4_desc'] ?? ''); ?></p>
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
            <div class="about-eyebrow"><?php echo htmlspecialchars($hq['eyebrow'] ?? ''); ?></div>
            <h2><?php echo htmlspecialchars($hq['title'] ?? ''); ?></h2>
            <p><?php echo htmlspecialchars($hq['subtext'] ?? ''); ?></p>
            <ul class="cq-bullets" style="list-style:none;padding:0;margin:10px 0;display:grid;gap:8px">
              <?php $bul = preg_split('/\r?\n/', (string)($hq['bullets_text'] ?? ''), -1, PREG_SPLIT_NO_EMPTY); foreach ($bul as $b): ?>
                <li><span class="tick">✓</span> <?php echo htmlspecialchars($b); ?></li>
              <?php endforeach; ?>
            </ul>
            <?php if (!empty($hq['mini_image_url']) || !empty($hq['mini_title']) || !empty($hq['mini_sub'])): ?>
            <div class="cq-mini">
              <?php if (!empty($hq['mini_image_url'])): ?>
                <img src="<?php echo htmlspecialchars($hq['mini_image_url']); ?>" alt="mini" />
              <?php endif; ?>
              <div class="mini-text"><strong><?php echo htmlspecialchars($hq['mini_title'] ?? ''); ?></strong><br/><?php echo htmlspecialchars($hq['mini_sub'] ?? ''); ?></div>
            </div>
            <?php endif; ?>
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
            <?php $statsImg = trim((string)($homeStats['image_url'] ?? '')); ?>
            <img src="<?php echo htmlspecialchars($statsImg ?: 'https://images.unsplash.com/photo-1550317138-10000687a72b?q=80&w=1600&auto=format&fit=crop'); ?>" alt="Stats image">
          </div>
          <div class="stats-hero-content">
            <h2><?php echo htmlspecialchars($homeStats['hero_title'] ?? ''); ?></h2>
            <p><?php echo htmlspecialchars($homeStats['hero_subtext'] ?? ''); ?></p>
            <div class="stats-cards">
              <div class="stat-card stat-red">
                <div class="stat-number"><?php echo htmlspecialchars($homeStats['stat1_number'] ?? ''); ?></div>
                <div class="stat-label"><?php echo htmlspecialchars($homeStats['stat1_label'] ?? ''); ?></div>
              </div>
              <div class="stat-card stat-navy">
                <div class="stat-number"><?php echo htmlspecialchars($homeStats['stat2_number'] ?? ''); ?></div>
                <div class="stat-label"><?php echo htmlspecialchars($homeStats['stat2_label'] ?? ''); ?></div>
              </div>
              <div class="stat-card stat-yellow">
                <div class="stat-number"><?php echo htmlspecialchars($homeStats['stat3_number'] ?? ''); ?></div>
                <div class="stat-label"><?php echo htmlspecialchars($homeStats['stat3_label'] ?? ''); ?></div>
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
            <?php if (!empty($gallery)): ?>
              <?php foreach ($gallery as $gi): ?>
                <?php $img = htmlspecialchars($gi['image_url'] ?? ''); if (!$img) continue; ?>
                <article class="tg-item news-card">
                  <?php if (!empty($gi['day']) || !empty($gi['month'])): ?>
                    <span class="date-badge"><strong><?php echo htmlspecialchars(str_pad((string)($gi['day'] ?? ''),2,'0',STR_PAD_LEFT)); ?></strong><small><?php echo htmlspecialchars(substr((string)($gi['month'] ?? ''),0,3)); ?></small></span>
                  <?php endif; ?>
                  <img src="<?php echo $img; ?>" alt="">
                  <span class="tag-pill"><?php echo htmlspecialchars($gi['tag'] ?? ''); ?></span>
                </article>
              <?php endforeach; ?>
            <?php else: ?>
              <!-- fallback static cards remain if gallery empty -->
              <article class="tg-item news-card"><img src="images/truck-moving-shipping-container-min-1024x683.jpeg" alt="Truck at dock"><span class="tag-pill">Transport</span></article>
            <?php endif; ?>
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
                <div class="hq-card-sub"><?php echo htmlspecialchars($contact['phone'] ?? ''); ?></div>
              </div>
            </div>
            <div class="hq-card">
              <div class="hq-card-icon">✉️</div>
              <div>
                <div class="hq-card-title">Email Us</div>
                <div class="hq-card-sub"><?php echo htmlspecialchars($contact['email'] ?? ''); ?></div>
              </div>
            </div>
            <div class="hq-card">
              <div class="hq-card-icon">📍</div>
              <div>
                <div class="hq-card-title">Visit Us</div>
                <div class="hq-card-sub"><?php echo htmlspecialchars($contact['address'] ?? ''); ?></div>
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

    <!-- Map (dynamic) -->
    <section class="location-map-section">
      <div class="map-bg" aria-hidden="true" style="background-image:url('https://staticmap.openstreetmap.de/staticmap.php?center=9.6574,80.1628&zoom=12&size=1600x900&maptype=mapnik&format=png');"></div>
      <div class="container">
        <div class="map-header">
          <h2><?php echo htmlspecialchars($map['header_title'] ?? 'Find Us Here'); ?></h2>
          <p><?php echo htmlspecialchars($map['header_subtext'] ?? 'Visit our office location in Kilinochchi, Sri Lanka'); ?></p>
        </div>
        <div class="map-container">
          <?php $mapSrc = trim((string)($map['map_embed_url'] ?? '')); ?>
          <iframe src="<?php echo htmlspecialchars($mapSrc ?: 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d127317.59409384069!2d80.04778896986493!3d9.664430939428727!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3afe53c9c5a7a7c5%3A0x9b2b9a5f7b0d1d0!2sAriviyal%20Nagar!5e0!3m2!1sen!2slk!4v1700000000000'); ?>" width="100%" height="320" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>

        <!-- Contact info cards below the map -->
        <div class="contact-info-cards">
          <div class="contact-card">
            <div class="contact-icon">📍</div>
            <div>
              <h4>Address</h4>
              <p><?php echo htmlspecialchars($contact['address'] ?? ''); ?></p>
            </div>
          </div>
          <div class="contact-card">
            <div class="contact-icon">🕒</div>
            <div>
              <h4>Business Hours</h4>
              <ul>
                <li><?php echo htmlspecialchars($contact['hours_weekday'] ?? ''); ?></li>
                <li><?php echo htmlspecialchars($contact['hours_sat'] ?? ''); ?></li>
                <li><?php echo htmlspecialchars($contact['hours_sun'] ?? ''); ?></li>
              </ul>
            </div>
          </div>
          <div class="contact-card">
            <div class="contact-icon">📞</div>
            <div>
              <h4>Contact</h4>
              <p>
                Phone: <a href="tel:<?php echo htmlspecialchars(preg_replace('/\s+/','',$contact['phone'] ?? '')); ?>"><?php echo htmlspecialchars($contact['phone'] ?? ''); ?></a><br/>
                Email: <a href="mailto:<?php echo htmlspecialchars($contact['email'] ?? ''); ?>"><?php echo htmlspecialchars($contact['email'] ?? ''); ?></a>
              </p>
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
            <li>📍 <?php echo htmlspecialchars($contact['address'] ?? ''); ?></li>
            <li>📞 <a href="tel:<?php echo htmlspecialchars(preg_replace('/\s+/','',$contact['phone'] ?? '')); ?>"><?php echo htmlspecialchars($contact['phone'] ?? ''); ?></a></li>
            <li>✉️ <a href="mailto:<?php echo htmlspecialchars($contact['email'] ?? ''); ?>"><?php echo htmlspecialchars($contact['email'] ?? ''); ?></a></li>
            <li>⏰ <?php echo htmlspecialchars($contact['hours_weekday'] ?? ''); ?></li>
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
  // Dynamic Services fetch disabled to rely on server-rendered cards for consistency

  // Quote/Contact forms -> submit to admin notifications (store message)
  (function(){
    async function submitForm(form, statusEl){
      if (!form) return;
      statusEl && (statusEl.textContent = 'Sending...');
      try{
        const fd = new FormData(form);
        const res = await fetch('/APLX/backend/public/message_submit.php', { method:'POST', body: fd });
        if (!res.ok) throw new Error('HTTP '+res.status);
        const data = await res.json();
        if (data && data.ok){
          statusEl && (statusEl.textContent = 'Message sent');
          form.reset();
        } else {
          statusEl && (statusEl.textContent = (data && data.error) ? data.error : 'Failed to send');
        }
      }catch(err){
        statusEl && (statusEl.textContent = 'Failed to send');
      }
    }
    const homeForm = document.getElementById('homeQuoteForm');
    const homeStatus = document.getElementById('homeQuoteStatus');
    homeForm && homeForm.addEventListener('submit', (e)=>{ e.preventDefault(); submitForm(homeForm, homeStatus); });
    const quickForm = document.getElementById('quickQuoteForm');
    const quickStatus = document.getElementById('quickQuoteStatus');
    quickForm && quickForm.addEventListener('submit', (e)=>{ e.preventDefault(); submitForm(quickForm, quickStatus); });
  })();
  // Transport gallery: fetch from backend, render, then enable seamless scroll
  (function(){
    const track = document.querySelector('.transport-gallery .tg-track');
    if (!track) return;
    const slider = track.closest('.tg-slider');

    function buildItems(items){
      function esc(s){ return String(s||'').replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m])); }
      if (!Array.isArray(items) || !items.length) return false;
      track.innerHTML = items.map((it,i)=>{
        const day = it.day ? String(it.day).padStart(2,'0') : '';
        const month = it.month ? esc(String(it.month).slice(0,3)) : '';
        const tag = it.tag ? esc(it.tag) : 'Transport';
        const imgSrc = esc(it.image_url||'');
        const badge = (day||month) ? `<span class="date-badge"><strong>${day}</strong><small>${month}</small></span>` : '';
        const imgTag = imgSrc ? `<img src="${imgSrc}" alt="">` : '';
        return `<article class="tg-item news-card">${badge}${imgTag}<span class="tag-pill">${tag}</span></article>`;
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
    book.href = '/APLX/frontend/customer/book.php';
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

  // Services Tabs: fetch dynamic data from backend and build the left list + initial right content
  (function(){
    const root = document.getElementById('servicesTabs');
    if (!root) return;
    const aside = root.querySelector('.st-left');
    const imgEl = document.getElementById('stImage');
    const bulletsEl = document.getElementById('stBullets');
    const badgeEl = root.querySelector('.st-badge');
    function esc(s){ return String(s||'').replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m])); }
    function build(items){
      if (!Array.isArray(items) || !items.length) return false;
      const btns = items.map((it, i)=>{
        const img = esc(it.image_url||'');
        const title = esc(it.title||'');
        const bullets = JSON.stringify((it.bullets||[]).map(String));
        const iconText = it.icon_text ? esc(it.icon_text) : '';
        const iconUrl = it.icon_url ? esc(it.icon_url) : '';
        const iconHtml = iconUrl ? `<img src="${iconUrl}" alt="" style="width:22px;height:22px;border-radius:6px;border:1px solid var(--border);object-fit:cover">` : (iconText || '⬢');
        const cls = 'st-item' + (i===0 ? ' active' : '');
        return `<button class="${cls}" role="tab" aria-selected="${i===0?'true':'false'}" data-img="${img}" data-bullets='${bullets}' data-title='${title}'>
                  <span class="st-icon">${iconHtml}</span>
                  <span class="st-text">${title}</span>
                </button>`;
      }).join('');
      aside.innerHTML = btns;
      const first = items[0];
      if (first && imgEl) imgEl.src = first.image_url || imgEl.src;
      if (first && bulletsEl){ bulletsEl.innerHTML = (first.bullets||[]).map(t=>`<li>✓ ${esc(t)}</li>`).join(''); }
      // Set badge from first bullet or title
      if (badgeEl){
        const txt = (first && Array.isArray(first.bullets) && first.bullets[0]) ? String(first.bullets[0]) : (first ? String(first.title||'') : '');
        badgeEl.innerHTML = `<span class="dot" style="display:inline-block;width:34px;height:34px;border-radius:50%;background:#fff;margin-right:10px;vertical-align:middle"></span><span style="vertical-align:middle">✓ ${esc(txt)}</span>`;
      }
      return true;
    }
    fetch('/APLX/backend/service_tabs_list.php', { cache:'no-store' })
      .then(r => r.ok ? r.json() : Promise.reject(r.status))
      .then(data => { build(data && data.items); })
      .catch(()=>{});
  })();
  // Services Tabs logic (event delegation so dynamic items work on hover/click)
  (function(){
    const root = document.getElementById('servicesTabs');
    if (!root) return;
    const aside = root.querySelector('.st-left');
    const imgEl = document.getElementById('stImage');
    const bulletsEl = document.getElementById('stBullets');
    const badgeEl = root.querySelector('.st-badge');

    function activate(btn){
      const all = root.querySelectorAll('.st-item');
      all.forEach(b=>{ b.classList.remove('active'); b.setAttribute('aria-selected','false'); });
      btn.classList.add('active');
      btn.setAttribute('aria-selected','true');
      const url = btn.getAttribute('data-img');
      let bullets = [];
      try { bullets = JSON.parse(btn.getAttribute('data-bullets')||'[]'); } catch(e) { bullets = []; }
      if (url && imgEl) imgEl.src = url;
      if (bulletsEl){ bulletsEl.innerHTML = bullets.map(t => `<li>✓ ${esc(t)}</li>`).join(''); }
      if (badgeEl){
        const title = btn.getAttribute('data-title')||'';
        const txt = (bullets && bullets[0]) ? bullets[0] : title;
        badgeEl.innerHTML = `<span class="dot" style="display:inline-block;width:34px;height:34px;border-radius:50%;background:#fff;margin-right:10px;vertical-align:middle"></span><span style="vertical-align:middle">✓ ${esc(txt)}</span>`;
      }
    }

    function getBtn(target){ return target.closest && target.closest('.st-item'); }
    ['mouseover','focusin','click'].forEach(evt=>{
      aside.addEventListener(evt, (e)=>{
        const btn = getBtn(e.target);
        if (btn) activate(btn);
      });
    });

    // Initialize if static markup exists
    const initial = root.querySelector('.st-item.active') || root.querySelector('.st-item');
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




