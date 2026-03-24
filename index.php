<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Holy Cross College — Academic Evaluation</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --navy: #0a1f3c;
            --deep-blue: #0d2d5e;
            --mid-blue: #1a4a8a;
            --sky: #4a90d9;
            --pale-sky: #a8c8f0;
            --ice: #ddeeff;
            --frost: #eef5ff;
            --white: #ffffff;
            --mist: #f4f8ff;
            --gold: #c8a96e;
            --text-dark: #0d1f35;
            --text-mid: #3a5278;
            --text-light: #6a8aaa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--white);
            color: var(--text-dark);
            overflow-x: hidden;
        }

        /* ── HERO ── */
        .hero {
            min-height: 100vh;
            background: linear-gradient(160deg, var(--navy) 0%, var(--deep-blue) 40%, var(--mid-blue) 75%, #1e6ab0 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 2rem;
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 110%, rgba(74, 144, 217, 0.18) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% -10%, rgba(168, 200, 240, 0.12) 0%, transparent 55%);
        }

        /* Cross watermark */
        .hero-cross {
            position: absolute;
            right: 8%;
            top: 50%;
            transform: translateY(-50%);
            width: 380px;
            height: 380px;
            opacity: 0.04;
        }

        .hero-cross::before,
        .hero-cross::after {
            content: '';
            position: absolute;
            background: var(--white);
            border-radius: 4px;
        }

        .hero-cross::before {
            width: 60px;
            height: 100%;
            left: 50%;
            transform: translateX(-50%);
        }

        .hero-cross::after {
            height: 60px;
            width: 100%;
            top: 30%;
            transform: translateY(-50%);
        }

        /* Wave rings */
        .ring {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(168, 200, 240, 0.08);
            animation: expand 8s ease-out infinite;
        }

        .ring:nth-child(1) {
            width: 400px;
            height: 400px;
            left: -100px;
            bottom: -100px;
            animation-delay: 0s;
        }

        .ring:nth-child(2) {
            width: 600px;
            height: 600px;
            left: -200px;
            bottom: -200px;
            animation-delay: 2s;
        }

        .ring:nth-child(3) {
            width: 800px;
            height: 800px;
            left: -300px;
            bottom: -300px;
            animation-delay: 4s;
        }

        @keyframes expand {
            0% {
                opacity: 0.15;
                transform: scale(0.95);
            }

            100% {
                opacity: 0;
                transform: scale(1.1);
            }
        }

        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 780px;
            animation: fadeUp 1s ease 0.2s both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(28px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(168, 200, 240, 0.3);
            border-radius: 40px;
            padding: 7px 20px;
            font-size: 0.72rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--pale-sky);
            margin-bottom: 2rem;
            backdrop-filter: blur(8px);
        }

        .badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--gold);
            animation: pulse 2s ease infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.4;
            }
        }

        .hero h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(2.8rem, 6vw, 5rem);
            font-weight: 300;
            line-height: 1.1;
            color: var(--white);
            margin-bottom: 0.4rem;
            letter-spacing: -0.01em;
        }

        .hero h1 span {
            font-style: italic;
            color: var(--pale-sky);
        }

        .hero-sub {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(1rem, 2vw, 1.4rem);
            font-weight: 300;
            color: rgba(168, 200, 240, 0.7);
            letter-spacing: 0.06em;
            margin-bottom: 2.5rem;
        }

        .divider-gold {
            width: 60px;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
            margin: 0 auto 2.5rem;
        }

        .hero-desc {
            font-size: 1rem;
            font-weight: 300;
            color: rgba(200, 220, 245, 0.8);
            line-height: 1.8;
            max-width: 560px;
            margin: 0 auto 3rem;
        }

        .hero-cta {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--sky), var(--mid-blue));
            color: var(--white);
            border: none;
            padding: 14px 36px;
            border-radius: 4px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            font-weight: 500;
            letter-spacing: 0.06em;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 30px rgba(26, 74, 138, 0.5);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(74, 144, 217, 0.5);
        }

        .btn-admin {
    background: rgba(255,255,255,0.06);
    color: var(--white);
    border: 1px solid rgba(200,169,110,0.5);
    padding: 14px 36px;
    border-radius: 4px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.88rem;
    font-weight: 500;
    letter-spacing: 0.06em;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .btn-admin:hover {
    background: rgba(200,169,110,0.15);
    border-color: var(--gold);
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(200,169,110,0.2);
  }

  .btn-primary {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .role-hint {
    font-size: 0.75rem;
    color: rgba(168,200,240,0.4);
    letter-spacing: 0.06em;
    margin-top: 1.2rem;
  }

  .btn-ghost {
    background: transparent;
    color: var(--pale-sky);
    border: 1px solid rgba(168,200,240,0.35);
    padding: 14px 36px;
    border-radius: 4px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.88rem;
    font-weight: 400;
    letter-spacing: 0.06em;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .btn-ghost:hover {
    background: rgba(255,255,255,0.05);
    border-color: rgba(168,200,240,0.6);
  }

  .scroll-hint {
    position: absolute;
    bottom: 2.5rem;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    color: rgba(168,200,240,0.4);
    font-size: 0.7rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    animation: fadeUp 1s ease 1.2s both;
  }

  .scroll-line {
    width: 1px;
    height: 40px;
    background: linear-gradient(to bottom, rgba(168,200,240,0.4), transparent);
    animation: scrollPulse 2s ease infinite;
  }

  @keyframes scrollPulse {
    0%, 100% { opacity: 0.4; }
    50% { opacity: 1; }
  }

  /* ── STATS BAND ── */
  .stats-band {
    background: var(--frost);
    border-top: 1px solid var(--ice);
    border-bottom: 1px solid var(--ice);
    padding: 2.5rem 2rem;
    display: flex;
    justify-content: center;
    gap: 0;
  }

  .stat-item {
    flex: 1;
    max-width: 200px;
    text-align: center;
    padding: 0 2rem;
    border-right: 1px solid var(--ice);
    animation: fadeUp 0.8s ease both;
  }

  .stat-item:last-child { border-right: none; }
  .stat-item:nth-child(1) { animation-delay: 0.1s; }
  .stat-item:nth-child(2) { animation-delay: 0.2s; }
  .stat-item:nth-child(3) { animation-delay: 0.3s; }
  .stat-item:nth-child(4) { animation-delay: 0.4s; }

  .stat-num {
    font-family: 'Cormorant Garamond', serif;
    font-size: 2.6rem;
    font-weight: 300;
    color: var(--mid-blue);
    line-height: 1;
  }

  .stat-label {
    font-size: 0.72rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--text-light);
    margin-top: 6px;
  }

  /* ── SECTION BASE ── */
  section {
    padding: 6rem 2rem;
    max-width: 1100px;
    margin: 0 auto;
  }

  .section-eyebrow {
    font-size: 0.7rem;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--sky);
    margin-bottom: 0.8rem;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .section-eyebrow::before {
    content: '';
    width: 28px; height: 1px;
    background: var(--sky);
  }

  .section-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(2rem, 4vw, 3.2rem);
    font-weight: 300;
    color: var(--text-dark);
    line-height: 1.2;
    margin-bottom: 1rem;
  }

  .section-title em {
    font-style: italic;
    color: var(--mid-blue);
  }

  .section-desc {
    font-size: 1rem;
    font-weight: 300;
    color: var(--text-mid);
    line-height: 1.8;
    max-width: 600px;
    margin-bottom: 3.5rem;
  }

  /* ── EVALUATION AREAS ── */
  .eval-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5px;
    background: var(--ice);
    border: 1.5px solid var(--ice);
    border-radius: 8px;
    overflow: hidden;
  }

  .eval-card {
    background: var(--white);
    padding: 2.2rem 2rem;
    transition: background 0.3s ease;
    cursor: default;
  }

  .eval-card:hover {
    background: var(--frost);
  }

  .css-cross {
    width: 24px;
    height: 30px;
    position: relative;
    margin: 0 auto 0.5rem;
  }
  .css-cross::before,
  .css-cross::after {
    content: '';
    position: absolute;
    background: var(--gold);
    border-radius: 2px;
  }
  .css-cross::before { width: 4px; height: 100%; left: 50%; transform: translateX(-50%); }
  .css-cross::after  { width: 100%; height: 4px; top: 28%; transform: translateY(-50%); }

  .eval-icon {
    width: 40px; height: 40px;
    border-radius: 8px;
    background: var(--frost);
    border: 1px solid var(--ice);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.2rem;
    font-size: 1.1rem;
  }

  .eval-icon svg { width: 18px; height: 18px; stroke: var(--mid-blue); fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }

  .eval-card h3 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.3rem;
    font-weight: 400;
    color: var(--text-dark);
    margin-bottom: 0.6rem;
  }

  .eval-card p {
    font-size: 0.88rem;
    font-weight: 300;
    color: var(--text-light);
    line-height: 1.7;
    margin-bottom: 1.4rem;
  }

  .eval-progress {
    height: 3px;
    background: var(--ice);
    border-radius: 2px;
    overflow: hidden;
  }

  .eval-progress-bar {
    height: 100%;
    border-radius: 2px;
    background: linear-gradient(90deg, var(--sky), var(--mid-blue));
    animation: growBar 1.5s ease 0.5s both;
  }

  @keyframes growBar {
    from { width: 0 !important; }
  }

  .eval-score {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
  }

  .eval-score-label {
    font-size: 0.72rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--text-light);
  }

  .eval-score-value {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.1rem;
    color: var(--mid-blue);
  }

  /* ── FORM ── */
  .form-section {
    background: linear-gradient(160deg, var(--frost) 0%, var(--white) 100%);
    border-top: 1px solid var(--ice);
    border-bottom: 1px solid var(--ice);
    padding: 6rem 2rem;
  }

  .form-inner {
    max-width: 760px;
    margin: 0 auto;
  }

  .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.2rem;
    margin-bottom: 1.2rem;
  }

  .form-full { grid-column: 1 / -1; }

  .form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .form-label {
    font-size: 0.72rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--text-mid);
  }

  .form-input,
  .form-select,
  .form-textarea {
    background: var(--white);
    border: 1px solid var(--ice);
    border-radius: 4px;
    padding: 12px 16px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.92rem;
    font-weight: 300;
    color: var(--text-dark);
    outline: none;
    transition: border-color 0.25s ease, box-shadow 0.25s ease;
    width: 100%;
  }

  .form-input:focus,
  .form-select:focus,
  .form-textarea:focus {
    border-color: var(--sky);
    box-shadow: 0 0 0 3px rgba(74,144,217,0.1);
  }

  .form-textarea { resize: vertical; min-height: 120px; }

  .rating-group { grid-column: 1 / -1; }

  .rating-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 0;
    border-bottom: 1px solid var(--ice);
    flex-wrap: wrap;
    gap: 1rem;
  }

  .rating-row:last-child { border-bottom: none; }

  .rating-label {
    font-size: 0.9rem;
    font-weight: 300;
    color: var(--text-dark);
    flex: 1;
    min-width: 160px;
  }

  .stars {
    display: flex;
    gap: 6px;
  }

  .star {
    width: 28px; height: 28px;
    border-radius: 4px;
    background: var(--frost);
    border: 1px solid var(--ice);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    color: var(--text-light);
    transition: all 0.2s ease;
    user-select: none;
  }

  .star:hover,
  .star.active {
    background: var(--mid-blue);
    border-color: var(--mid-blue);
    color: var(--white);
  }

  .submit-row {
    display: flex;
    justify-content: flex-end;
    margin-top: 2rem;
    gap: 1rem;
  }

  /* ── FOOTER ── */
  footer {
    background: var(--navy);
    padding: 3rem 2rem;
    text-align: center;
  }

  .footer-cross {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.5rem;
    color: var(--gold);
    letter-spacing: 0.1em;
    margin-bottom: 0.5rem;
  }

  .footer-name {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.2rem;
    font-weight: 300;
    color: rgba(168,200,240,0.6);
    letter-spacing: 0.08em;
  }

  .footer-fine {
    font-size: 0.72rem;
    color: rgba(100,140,180,0.4);
    margin-top: 1.5rem;
    letter-spacing: 0.08em;
  }

  /* ── RESPONSIVE ── */
  @media (max-width: 640px) {
    .stats-band { flex-wrap: wrap; }
    .stat-item { max-width: 50%; border-right: none; padding: 1rem; }
    .form-grid { grid-template-columns: 1fr; }
    .form-full { grid-column: 1; }
  }
</style>
</head>
<body>

<!-- HERO -->
<div class="hero">
  <div class="ring"></div>
  <div class="ring"></div>
  <div class="ring"></div>
  <div class="hero-cross"></div>

  <div class="hero-content">
    <div class="badge">
      <span class="badge-dot"></span>
      Academic Year 2024 – 2025
    </div>
    <h1>Holy Cross <span>College</span></h1>
    <div class="hero-sub">Institutional Evaluation Program</div>
    <div class="divider-gold"></div>
    <p class="hero-desc">
      Your thoughtful assessment helps us shape a more excellent, faith-centered, and student-focused learning environment. Every response is valued and confidential.
    </p>
    <div class="hero-cta">
      <button class="btn-primary" onclick="window.location.href='Client_login.php'">
        Student / Teacher Account
      </button>
      <button class="btn-admin" onclick="window.location.href='Adminlogin.php'">
        Admin Account
      </button>
    </div>
    <p class="role-hint">Choose the portal that matches your role to continue</p>
  </div>

  <div class="scroll-hint">
    <span>Scroll</span>
    <div class="scroll-line"></div>
  </div>
</div>

<!-- STATS BAND -->
<div class="stats-band">
  <div class="stat-item">
    <div class="stat-num">4</div>
    <div class="stat-label">Core Areas</div>
  </div>
  <div class="stat-item">
    <div class="stat-num">5</div>
    <div class="stat-label">Min. to Complete</div>
  </div>
  <div class="stat-item">
    <div class="stat-num">100%</div>
    <div class="stat-label">Confidential</div>
  </div>
  <div class="stat-item">
    <div class="stat-num">∞</div>
    <div class="stat-label">Impact</div>
  </div>
</div>

<!-- EVALUATION AREAS -->
<section id="areas">
  <div class="section-eyebrow">Assessment Framework</div>
  <h2 class="section-title">Four Pillars of <em>Institutional Excellence</em></h2>
  <p class="section-desc">Our evaluation is guided by the Lasallian tradition of faith, service, and community — covering every dimension of the Holy Cross experience.</p>

  <div class="eval-grid">
    <div class="eval-card">
      <div class="eval-icon"><svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg></div>
      <h3>Academic Quality</h3>
      <p>Curriculum depth, faculty expertise, instructional methods, and learning outcomes across all programs and disciplines.</p>
      <div class="eval-score">
        <span class="eval-score-label">Current Rating</span>
        <span class="eval-score-value">4.6 / 5.0</span>
      </div>
      <div class="eval-progress"><div class="eval-progress-bar" style="width:92%"></div></div>
    </div>

    <div class="eval-card">
      <div class="eval-icon"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg></div>
      <h3>Campus Environment</h3>
      <p>Facilities, infrastructure, cleanliness, safety, accessibility, and the overall quality of physical spaces for learning and growth.</p>
      <div class="eval-score">
        <span class="eval-score-label">Current Rating</span>
        <span class="eval-score-value">4.3 / 5.0</span>
      </div>
      <div class="eval-progress"><div class="eval-progress-bar" style="width:86%"></div></div>
    </div>

    <div class="eval-card">
      <div class="eval-icon"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
      <h3>Student Services</h3>
      <p>Guidance counseling, student affairs, registration processes, library resources, and the overall administrative support experience.</p>
      <div class="eval-score">
        <span class="eval-score-label">Current Rating</span>
        <span class="eval-score-value">4.4 / 5.0</span>
      </div>
      <div class="eval-progress"><div class="eval-progress-bar" style="width:88%"></div></div>
    </div>

    <div class="eval-card">
      <div class="eval-icon"><svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></div>
      <h3>Faith & Values Formation</h3>
      <p>Integration of Christian values, campus ministry, service programs, and the cultivation of moral and spiritual character.</p>
      <div class="eval-score">
        <span class="eval-score-label">Current Rating</span>
        <span class="eval-score-value">4.8 / 5.0</span>
      </div>
      <div class="eval-progress"><div class="eval-progress-bar" style="width:96%"></div></div>
    </div>
  </div>
</section>

<!-- EVALUATION FORM -->
<div class="form-section" id="evaluate">
  <div class="form-inner">
    <div class="section-eyebrow">Submit Your Evaluation</div>
    <h2 class="section-title">Share Your <em>Experience</em></h2>
    <p class="section-desc">All responses are anonymous unless you choose to provide contact details. Thank you for your time and honest feedback.</p>

    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">Role / Affiliation</label>
        <select class="form-select">
          <option value="">Select your role</option>
          <option>Student</option>
          <option>Alumni</option>
          <option>Faculty</option>
          <option>Staff</option>
          <option>Parent / Guardian</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Academic Year</label>
        <select class="form-select">
          <option>2024 – 2025</option>
          <option>2023 – 2024</option>
          <option>2022 – 2023</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Department / Program (Optional)</label>
        <input class="form-input" type="text" placeholder="e.g. College of Education">
      </div>

      <div class="form-group">
        <label class="form-label">Email (Optional)</label>
        <input class="form-input" type="email" placeholder="your@email.com">
      </div>

      <!-- RATINGS -->
      <div class="form-group rating-group">
        <label class="form-label" style="margin-bottom: 0.8rem;">Rate Each Area (1 – 5)</label>

        <div class="rating-row">
          <span class="rating-label">Academic Quality & Instruction</span>
          <div class="stars" data-group="academic">
            <div class="star" onclick="rate(this,1)">1</div>
            <div class="star" onclick="rate(this,2)">2</div>
            <div class="star" onclick="rate(this,3)">3</div>
            <div class="star" onclick="rate(this,4)">4</div>
            <div class="star" onclick="rate(this,5)">5</div>
          </div>
        </div>

        <div class="rating-row">
          <span class="rating-label">Campus Facilities & Environment</span>
          <div class="stars" data-group="campus">
            <div class="star" onclick="rate(this,1)">1</div>
            <div class="star" onclick="rate(this,2)">2</div>
            <div class="star" onclick="rate(this,3)">3</div>
            <div class="star" onclick="rate(this,4)">4</div>
            <div class="star" onclick="rate(this,5)">5</div>
          </div>
        </div>

        <div class="rating-row">
          <span class="rating-label">Student Services & Administration</span>
          <div class="stars" data-group="services">
            <div class="star" onclick="rate(this,1)">1</div>
            <div class="star" onclick="rate(this,2)">2</div>
            <div class="star" onclick="rate(this,3)">3</div>
            <div class="star" onclick="rate(this,4)">4</div>
            <div class="star" onclick="rate(this,5)">5</div>
          </div>
        </div>

        <div class="rating-row">
          <span class="rating-label">Faith Formation & Community</span>
          <div class="stars" data-group="faith">
            <div class="star" onclick="rate(this,1)">1</div>
            <div class="star" onclick="rate(this,2)">2</div>
            <div class="star" onclick="rate(this,3)">3</div>
            <div class="star" onclick="rate(this,4)">4</div>
            <div class="star" onclick="rate(this,5)">5</div>
          </div>
        </div>

        <div class="rating-row">
          <span class="rating-label">Overall Satisfaction</span>
          <div class="stars" data-group="overall">
            <div class="star" onclick="rate(this,1)">1</div>
            <div class="star" onclick="rate(this,2)">2</div>
            <div class="star" onclick="rate(this,3)">3</div>
            <div class="star" onclick="rate(this,4)">4</div>
            <div class="star" onclick="rate(this,5)">5</div>
          </div>
        </div>
      </div>

      <div class="form-group form-full">
        <label class="form-label">What does Holy Cross College do exceptionally well?</label>
        <textarea class="form-textarea" placeholder="Share your thoughts…"></textarea>
      </div>

      <div class="form-group form-full">
        <label class="form-label">What areas need the most improvement?</label>
        <textarea class="form-textarea" placeholder="Your suggestions help us grow…"></textarea>
      </div>

      <div class="form-group form-full">
        <label class="form-label">Any additional comments or recommendations</label>
        <textarea class="form-textarea" placeholder="Anything else you'd like us to know…"></textarea>
      </div>
    </div>

    <div class="submit-row">
      <button class="btn-ghost" style="border-color: var(--ice); color: var(--text-mid);">Clear Form</button>
      <button class="btn-primary" onclick="handleSubmit()">Submit Evaluation →</button>
    </div>
  </div>
</div>

<!-- FOOTER -->
<footer>
  <div class="footer-cross">
    <div class="css-cross"></div>
  </div>
  <div class="footer-name">Holy Cross College</div>
  <div class="footer-fine">Institutional Quality Assurance Office · All responses are strictly confidential · © 2025</div>
</footer>

<script>
  function rate(el, val) {
    const group = el.parentElement;
    const stars = group.querySelectorAll('.star');
    stars.forEach((s, i) => {
      s.classList.toggle('active', i < val);
    });
  }

  function handleSubmit() {
    const btn = event.target;
    btn.textContent = 'Submitted — Thank you!';
    btn.style.background = 'linear-gradient(135deg, #2ecc71, #27ae60)';
    btn.disabled = true;
    setTimeout(() => {
      btn.textContent = 'Submit Evaluation →';
      btn.style.background = '';
      btn.disabled = false;
    }, 4000);
  }

  // Scroll-triggered fade-in
  const observer = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.style.opacity = '1';
        e.target.style.transform = 'translateY(0)';
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.eval-card, .stat-item').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(el);
  });
</script>
</body>
</html>