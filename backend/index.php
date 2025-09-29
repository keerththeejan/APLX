<?php
require_once __DIR__ . '/init.php';
// Demo content (replace with DB later)
$services = [
  ['icon' => '🚚', 'title' => 'Same-day Pickup', 'description' => 'Book before 12 PM for same‑day pickup.'],
  ['icon' => '✈️', 'title' => 'Air Freight', 'description' => 'Fast and reliable domestic air.'],
  ['icon' => '🚢', 'title' => 'Ocean Freight', 'description' => 'Cost‑effective shipping solutions.'],
];
$features = [
  ['icon' => '🛰️', 'title' => 'Live Tracking', 'description' => 'Track your parcel end‑to‑end.'],
  ['icon' => '🔒', 'title' => 'Secure Handling', 'description' => 'Tamper‑evident packaging and QA.'],
  ['icon' => '🕒', 'title' => 'On‑Time SLA', 'description' => 'Reliable delivery timelines.'],
  ['icon' => '💳', 'title' => 'Flexible Payments', 'description' => 'COD, cards, transfers supported.'],
];
$admin = [
  'phone' => '+94 21 492 7799',
  'email' => 'info@stgfl.com',
  'address' => 'Ariviyal Nagar, Kilinochchi, Sri Lanka',
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Parcel Transport - Home</title>
  <link rel="stylesheet" href="/Parcel/css/style.css">
  <style>
    /* minimal scaffolding specific to this page if needed */
  </style>
</head>
<body>
  <header class="navbar">
    <div class="container">
      <div class="brand">Parcel Transport</div>
      <nav>
        <a href="/Parcel/backend/index.php" class="active">Home</a>
        <a href="/Parcel/frontend/track.html">Track</a>
        <a href="/Parcel/frontend/customer/book.html">Book</a>
        <a href="/Parcel/frontend/auth/login.html">Admin</a>
      </nav>
    </div>
  </header>

  <main>
    <div class="container-hero hero-wrap full-bleed">
      <section class="hero hero-full" style="background-image:url('https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?q=80&w=1600&auto=format&fit=crop');">
        <div class="slideshow" aria-hidden="true">
          <img class="active" src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?q=80&w=1600&auto=format&fit=crop" alt="" />
          <img src="https://images.unsplash.com/photo-1578662996442-48f60103fc96?q=80&w=1600&auto=format&fit=crop" alt="" />
          <img src="https://images.unsplash.com/photo-1436491865332-7a61a109cc05?q=80&w=1600&auto=format&fit=crop" alt="" />
          <img src="https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?q=80&w=1600&auto=format&fit=crop" alt="" />
        </div>
        <span class="eyebrow">Safe Transportation &amp; Logistics</span>
        <h1>Adaptable coordinated factors<br/>Quick Conveyance</h1>
        <p>Reliable logistics solutions for every shipment. From pickup to delivery, track and manage your parcels with ease.</p>
        <div class="form-actions" style="margin-top:16px; display:flex; gap:12px;">
          <a class="btn btn-primary" href="/Parcel/frontend/auth/login.html" style="text-decoration:none;">Get Started</a>
          <a class="btn btn-secondary" href="#contact" style="text-decoration:none;">Learn More</a>
        </div>
      </section>
    </div>

    <section class="services">
      <div class="services-grid">
        <?php if (!empty($services)): foreach ($services as $i => $s): ?>
          <div class="service-card reveal reveal-card <?php echo ['stagger-1','stagger-2','stagger-3'][$i % 3]; ?>">
            <div class="service-icon" aria-hidden="true">
              <?php if (!empty($s['icon'])): ?><span style="font-size:24px"><?php echo htmlspecialchars($s['icon']); ?></span><?php else: ?>
              <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M3 17h1a3 3 0 1 0 6 0h4a3 3 0 1 0 6 0h1v-4l-3-4h-5V7H3v10Zm14-4h2.5l1.5 2v2h-1.18a3.001 3.001 0 0 0-5.64 0H9.82a3.001 3.001 0 0 0-5.64 0H3V9h14v4Z"/></svg>
              <?php endif; ?>
            </div>
            <div class="service-title"><?php echo htmlspecialchars($s['title']); ?></div>
            <div class="service-desc"><?php echo htmlspecialchars($s['description']); ?></div>
          </div>
        <?php endforeach; else: ?>
          <div class="service-card"><div class="service-title">No services yet</div></div>
        <?php endif; ?>
      </div>
    </section>

    <section class="about-us">
      <div class="container">
        <div class="about-content">
          <div class="about-image reveal stagger-1">
            <img src="https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?q=80&w=600&auto=format&fit=crop" alt="Logistics Team" />
            <div class="experience-badge">
              <div class="experience-number">25+</div>
              <div class="experience-text">Years Experience</div>
            </div>
          </div>
          <div class="about-text">
            <div class="about-eyebrow reveal reveal-left stagger-2">ABOUT US</div>
            <h2 class="reveal reveal-up stagger-2">We Are Trusted Logistics & Transport</h2>
            <p class="reveal reveal-right stagger-3">We are a leading logistics and transportation company with over 25 years of experience in the industry. Our team of experts is dedicated to providing reliable and efficient logistics solutions tailored to your needs.</p>
            <div class="features-grid">
              <?php if (!empty($features)): foreach ($features as $k => $f): ?>
              <div class="feature-item reveal <?php echo ($k % 2 ? 'reveal-right' : 'reveal-left'); ?> <?php echo ['stagger-1','stagger-2','stagger-3'][$k % 3]; ?>">
                <div class="feature-icon"><?php echo htmlspecialchars($f['icon'] ?? '⭐'); ?></div>
                <div class="feature-content">
                  <h4><?php echo htmlspecialchars($f['title']); ?></h4>
                  <p><?php echo htmlspecialchars($f['description']); ?></p>
                </div>
              </div>
              <?php endforeach; else: ?>
              <div class="feature-item"><div class="feature-content"><h4>No features yet</h4></div></div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="why-choose-us">
      <div class="container">
        <div class="why-choose-header">
          <div class="why-choose-eyebrow">WHY CHOOSE US</div>
          <h2>We Provide Best Services</h2>
          <p>We are committed to providing the best logistics and transportation services to our clients with the highest levels of customer satisfaction.</p>
        </div>
        <div class="why-choose-grid">
          <div class="why-choose-card reveal reveal-card stagger-1">
            <div class="why-choose-icon">🔒</div>
            <h3>Safe & Secure</h3>
            <p>Your goods are in safe hands with our secure handling and transportation services.</p>
          </div>
          <div class="why-choose-card reveal reveal-card stagger-2">
            <div class="why-choose-icon">⏱️</div>
            <h3>On Time Delivery</h3>
            <p>We guarantee timely delivery of your shipments with our efficient logistics network.</p>
          </div>
          <div class="why-choose-card reveal reveal-card stagger-3">
            <div class="why-choose-icon">📞</div>
            <h3>24/7 Support</h3>
            <p>Our dedicated support team is available round the clock to assist you.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="our-services">
      <div class="container">
        <div class="our-services-header">
          <div class="our-services-eyebrow">OUR SERVICES</div>
          <h2>We Provide Best Services</h2>
          <p>We offer a wide range of logistics services to meet your business needs.</p>
        </div>
        <div class="truck-track" aria-hidden="true"></div>
        <svg class="plane" viewBox="0 0 240 120" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"></svg>
        <div class="our-services-grid">
          <div class="our-service-card reveal reveal-card stagger-1">
            <div class="our-service-image">
              <img src="https://images.unsplash.com/photo-1436491865332-7a61a109cc05?q=80&w=400&auto=format&fit=crop" alt="Air Freight" />
            </div>
            <div class="our-service-content">
              <h3>Air Freight</h3>
              <p>Fast and reliable air freight services worldwide.</p>
              <a href="#" class="read-more-link">Read More →</a>
            </div>
          </div>
          <div class="our-service-card reveal reveal-card stagger-2">
            <div class="our-service-image">
              <img src="https://images.unsplash.com/photo-1537047902294-62a40c20a6ae?q=80&w=900&auto=format&fit=crop" alt="Ocean Freight" />
            </div>
            <div class="our-service-content">
              <h3>Ocean Freight</h3>
              <p>Cost-effective ocean freight solutions.</p>
              <a href="#" class="read-more-link">Read More →</a>
            </div>
          </div>
          <div class="our-service-card reveal reveal-card stagger-3">
            <div class="our-service-image">
              <img src="https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?q=80&w=400&auto=format&fit=crop" alt="Land Transport" />
            </div>
            <div class="our-service-content">
              <h3>Land Transport</h3>
              <p>Efficient land transportation services.</p>
              <a href="#" class="read-more-link">Read More →</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="transport-gallery">
      <div class="container">
        <div class="tg-slider reveal" aria-label="Logistics photo slideshow">
          <div class="tg-track">
            <?php $imgs = [
              'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?q=80&w=400&auto=format&fit=crop',
              'https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?q=80&w=400&auto=format&fit=crop',
              'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?q=80&w=1600&auto=format&fit=crop',
              'https://images.unsplash.com/photo-1537047902294-62a40c20a6ae?q=80&w=900&auto=format&fit=crop',
              'https://images.unsplash.com/photo-1519638399535-1b036603ac77?q=80&w=900&auto=format&fit=crop',
              'https://images.unsplash.com/photo-1554631221-f9603e6808be?q=80&w=900&auto=format&fit=crop',
              'https://images.unsplash.com/photo-1529070538774-1843cb3265df?q=80&w=900&auto=format&fit=crop',
              'https://images.unsplash.com/photo-1542296332-2e4473faf563?q=80&w=900&auto=format&fit=crop',
            ];
            foreach (array_merge($imgs, $imgs) as $src): ?>
              <figure class="tg-item"><img src="<?php echo $src; ?>" alt="Logistics photo"></figure>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>

    <section id="contact" class="contact-quote-section reveal">
      <div class="contact-quote-container">
        <div class="contact-bg-decor" aria-hidden="true"></div>
        <div class="contact-info reveal stagger-1">
          <h2>Need Help With Your Shipping?</h2>
          <p>Our team is here to help you with all your logistics needs. Contact us today for a free quote.</p>
          <div class="contact-methods">
            <div class="contact-method"><div class="contact-icon">📞</div><div class="contact-details"><h4>Call Us Anytime</h4><p><?php echo htmlspecialchars($admin['phone']); ?></p></div></div>
            <div class="contact-method"><div class="contact-icon">✉️</div><div class="contact-details"><h4>Email Us</h4><p><?php echo htmlspecialchars($admin['email']); ?></p></div></div>
            <div class="contact-method"><div class="contact-icon">📍</div><div class="contact-details"><h4>Visit Us</h4><p><?php echo htmlspecialchars($admin['address']); ?></p></div></div>
            <div class="contact-method"><div class="contact-icon">🕒</div><div class="contact-details"><h4>Business Hours</h4><p>Mon - Fri: 9:00 AM - 6:00 PM<br>Sat: 9:00 AM - 2:00 PM<br>Sun: Closed</p></div></div>
          </div>
        </div>
        <div class="quote-form reveal stagger-2">
          <h2>Get A Free Quote</h2>
          <p>Fill out the form below and our team will get back to you as soon as possible.</p>
          <form id="homeQuoteForm" class="quote-form-container">
            <div class="form-row">
              <input id="hqName" name="name" type="text" placeholder="Your Name" class="form-input" required>
              <input id="hqEmail" name="email" type="email" placeholder="Your Email" class="form-input" required>
            </div>
            <input id="hqSubject" name="subject" type="text" placeholder="Subject" class="form-input full-width" required>
            <select id="hqService" name="service" class="form-input full-width" required>
              <option value="">Select Service</option>
              <?php foreach ($services as $s): ?>
                <option value="<?php echo strtolower(str_replace(' ', '-', $s['title'])); ?>"><?php echo htmlspecialchars($s['title']); ?></option>
              <?php endforeach; ?>
            </select>
            <textarea id="hqMessage" name="message" placeholder="Your Message" class="form-input full-width message-input" rows="4" required></textarea>
            <button id="hqSubmit" type="submit" class="send-message-btn">Send Message</button>
            <button type="button" class="quote-submit-btn" aria-hidden="true" tabindex="-1">↑</button>
          </form>
          <div id="homeQuoteStatus" class="inline-status" aria-live="polite"></div>
        </div>
      </div>
    </section>

    <section class="location-map-section">
      <div class="map-bg" aria-hidden="true" style="background-image:url('https://staticmap.openstreetmap.de/staticmap.php?center=9.38472,80.39876&zoom=13&size=1600x900&maptype=mapnik&format=png&markers=9.38472,80.39876,lightblue1');"></div>
      <div class="container">
        <div class="map-header">
          <h2>Find Us Here</h2>
          <p>Visit our office location in Kilinochchi, Sri Lanka</p>
        </div>
        <div class="map-container">
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3949.123456789!2d80.39876!3d9.38472!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3afe1db0d5c0c5c5%3A0x1234567890abcdef!2sAriviyal%20Nagar%2C%20Kilinochchi%2C%20Sri%20Lanka!5e0!3m2!1sen!2slk!4v1234567890123!5m2!1sen!2slk&markers=color:red%7Clabel:A%7C9.38472,80.39876" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
          <div class="map-decor" aria-hidden="true"></div>
          <div class="map-vehicles" aria-hidden="true"></div>
        </div>
        <div class="location-info">
          <div class="location-details">
            <div class="location-item"><div class="location-icon">📍</div><div><h4>Address</h4><p>Ariviyal Nagar, Kilinochchi, Sri Lanka</p></div></div>
            <div class="location-item"><div class="location-icon">🕒</div><div><h4>Business Hours</h4><p>Mon - Fri: 9:00 AM - 6:00 PM<br>Sat: 9:00 AM - 2:00 PM<br>Sun: Closed</p></div></div>
            <div class="location-item"><div class="location-icon">📞</div><div><h4>Contact</h4><p>Phone: +94 21 492 7799<br>Email: info@stgfl.com</p></div></div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="container">© 2025 Parcel Transport</div>
  </footer>

  <script>
  // Services interactive grid
  (function() {
    const grid = document.querySelector('.services-grid'); if (!grid) return;
    const cards = Array.from(grid.querySelectorAll('.service-card')); let selected = null;
    function toggle(){ if (selected === this){ this.classList.remove('selected'); this.setAttribute('aria-selected','false'); grid.classList.remove('dim-others'); selected=null; return;} cards.forEach(c=>{c.classList.remove('selected'); c.setAttribute('aria-selected','false');}); this.classList.add('selected'); this.setAttribute('aria-selected','true'); grid.classList.add('dim-others'); selected=this; }
    cards.forEach(card=>{ card.setAttribute('tabindex','0'); card.setAttribute('role','button'); card.addEventListener('click',toggle); card.addEventListener('keydown',e=>{ if(e.key==='Enter'||e.key===' '){ e.preventDefault(); toggle.call(card);} }); });
  })();
  // Transport gallery auto-scroll
  (function(){ const prefersReduced=window.matchMedia('(prefers-reduced-motion: reduce)'); if(prefersReduced.matches) return; const slider=document.querySelector('.transport-gallery .tg-slider'); const track=document.querySelector('.transport-gallery .tg-track'); if(!slider||!track) return; track.style.transform='translate3d(0,0,0)'; function setVars(){ const visible=slider.clientWidth||0; const total=track.scrollWidth||0; const delta=Math.max(0,total-visible); if(delta<8){ track.style.setProperty('--tg-shift','0px'); track.style.setProperty('--tg-duration','16s'); track.style.animationPlayState='paused'; return;} track.style.animationPlayState='running'; track.style.setProperty('--tg-shift',(-delta)+'px'); const duration=Math.min(48, Math.max(12, 16*(delta/1000))); track.style.setProperty('--tg-duration', duration+'s'); } const imgs=track.querySelectorAll('img'); imgs.forEach(img=>{ if(img.complete) return; img.addEventListener('load', setVars, {once:true}); img.addEventListener('error', setVars, {once:true}); }); const ro=new ResizeObserver(setVars); ro.observe(slider); ro.observe(track); window.addEventListener('orientationchange',()=>setTimeout(setVars,200)); window.addEventListener('resize', setVars); prefersReduced.addEventListener?.('change', ()=>{ if(prefersReduced.matches) track.style.animation='none'; }); setTimeout(setVars,0); if(document.readyState==='complete') setVars(); else window.addEventListener('load', setVars, {once:true}); })();
  // Hero cross-fade
  (function(){ const slideshow=document.querySelector('.hero .slideshow'); if(!slideshow) return; const slides=Array.from(slideshow.querySelectorAll('img')); if(slides.length<=1) return; const servicesSection=document.querySelector('.services'); let idx=0; let timer=null; function show(i){ slides.forEach((img,k)=>img.classList.toggle('active', k===i)); try{ if(servicesSection && slides[i] && slides[i].src){ servicesSection.style.backgroundImage = `url('${slides[i].src}')`; } }catch(e){} } function next(){ idx=(idx+1)%slides.length; show(idx);} function start(){ if(!timer) timer=setInterval(next,5000);} function stop(){ if(timer){ clearInterval(timer); timer=null; } } show(0); start(); slideshow.addEventListener('mouseenter', stop); slideshow.addEventListener('mouseleave', start); document.addEventListener('visibilitychange', ()=>{ document.hidden ? stop() : start(); }); })();
  // Bubble header on scroll
  (function(){ const navbar=document.querySelector('.navbar'); if(!navbar) return; function onScroll(){ if(window.scrollY>10) navbar.classList.add('scrolled'); else navbar.classList.remove('scrolled'); } window.addEventListener('scroll', onScroll, {passive:true}); onScroll(); })();
  // Why-choose-us bg slideshow
  (function(){ const section=document.querySelector('.why-choose-us'); if(!section) return; const images=[ 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?q=80&w=1600&auto=format&fit=crop','https://images.unsplash.com/photo-1436491865332-7a61a109cc05?q=80&w=1600&auto=format&fit=crop','https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?q=80&w=1600&auto=format&fit=crop' ]; let idx=0; function setBg(i){ section.style.backgroundImage = `url('${images[i]}')`; } function next(){ idx=(idx+1)%images.length; setBg(idx);} if(images.length) setBg(0); let timer=setInterval(next,7000); document.addEventListener('visibilitychange',()=>{ if(document.hidden){ clearInterval(timer); timer=null; } else if(!timer){ timer=setInterval(next,7000); } }); })();
  // Reveal on scroll
  (function(){ const autoSelectors=['.services .service-card','.services .service-title','.services .service-desc','.about-us .about-image','.about-us .about-text','.about-us .about-text h2','.about-us .about-text p','.about-us .feature-item','.why-choose-us .why-choose-card','.why-choose-us .why-choose-header *','.our-services .our-service-card','.our-services .our-services-header *','.contact-quote-section .contact-info','.contact-quote-section .quote-form','.contact-quote-section h2, .contact-quote-section p','.location-map-section .map-header *','.location-map-section .location-info','.location-map-section .location-details .location-item','.footer','.footer .footer-column','.footer .footer-title','.footer .footer-description','.footer .footer-links a','.contact-info-footer .contact-item-footer','.footer-bottom .footer-bottom-content']; autoSelectors.forEach(sel=>{ document.querySelectorAll(sel).forEach(el=>{ if(!el.classList.contains('reveal')) el.classList.add('reveal'); if(!el.classList.contains('reveal-replay')) el.classList.add('reveal-replay'); }); }); const els=document.querySelectorAll('.reveal'); if(!('IntersectionObserver' in window)){ els.forEach(el=>el.classList.add('reveal-visible')); return; } const io=new IntersectionObserver((entries)=>{ entries.forEach(entry=>{ const el=entry.target; if(entry.isIntersecting){ el.classList.add('reveal-visible'); } else if(el.classList.contains('reveal-replay')){ el.classList.remove('reveal-visible'); } }); }, { rootMargin:'0px 0px -15% 0px', threshold:0.1 }); els.forEach(el=>{ const rect=el.getBoundingClientRect(); const inView=rect.top<(window.innerHeight*0.88)&&rect.bottom>0; if(inView){ el.classList.add('reveal-visible'); } io.observe(el); }); })();
  // Mouse bubble trail (optional layer exists)
  (function(){ const bubblesLayer=document.querySelector('.mouse-bubbles'); if(!bubblesLayer) return; const prefersReduced=window.matchMedia('(prefers-reduced-motion: reduce)').matches; if(prefersReduced) return; let last=0; const throttleMs=90; const colors=['radial-gradient(circle at 30% 30%, rgba(147,197,253,.9), rgba(59,130,246,.65))','radial-gradient(circle at 30% 30%, rgba(203,213,225,.9), rgba(148,163,184,.55))','radial-gradient(circle at 30% 30%, rgba(110,231,183,.9), rgba(16,185,129,.55))']; function spawnBubble(x,y,size,dx,dy){ const el=document.createElement('span'); el.className='mouse-bubble'; el.style.width=size+'px'; el.style.height=size+'px'; el.style.left=x+'px'; el.style.top=y+'px'; el.style.background=colors[(Math.random()*colors.length)|0]; el.style.setProperty('--dx', dx+'px'); el.style.setProperty('--dy', dy+'px'); bubblesLayer.appendChild(el); el.addEventListener('animationend', ()=> el.remove()); } function onMove(e){ const now=performance.now(); if(now-last<throttleMs) return; last=now; const baseX=e.clientX, baseY=e.clientY; spawnBubble(baseX, baseY, 28+Math.random()*16, (Math.random()*30-15).toFixed(1), (Math.random()*36-10).toFixed(1)); spawnBubble(baseX+(Math.random()*18-9), baseY+(Math.random()*18-9), 22+Math.random()*14, (Math.random()*30-15).toFixed(1), (Math.random()*36-10).toFixed(1)); spawnBubble(baseX+(Math.random()*22-11), baseY+(Math.random()*22-11), 20+Math.random()*12, (Math.random()*30-15).toFixed(1), (Math.random()*36-10).toFixed(1)); if(bubblesLayer.childElementCount>120){ for(let i=0;i<20;i++){ const n=bubblesLayer.firstElementChild; if(!n) break; n.remove(); } } } window.addEventListener('mousemove', onMove, {passive:true}); })();
  </script>
</body>
</html>
