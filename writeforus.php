<!DOCTYPE html>
<html>

<head>
    <?php include "./meta.php" ?>
    <meta property="og:title" content="Write for Us - 360MiQ.com" />
    <meta property="og:url" content="https://360miq.com/writeforus" />
    <meta property="og:image" content="assets/img/360Logo_512.png" />
    <meta name="description" content="360MiQ.com Write for Us – Share Your Investment Insights" />
    <meta property="og:description" content="360MiQ.com Write for Us – Share Your Investment Insights" />

    <title>Write for Us - 360MiQ.com</title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" sizes="16x15" href="assets/img/360Logo_16.png">
    <link rel="icon" type="image/png" sizes="32x31" href="assets/img/360Logo_32.png">
    <link rel="icon" type="image/png" sizes="179x169" href="assets/img/360Logo_180.png">
    <link rel="icon" type="image/png" sizes="192x181" href="assets/img/360Logo_192.png">
    <link rel="icon" type="image/png" sizes="512x482" href="assets/img/360Logo_512.png">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:400,400i,700,700i,600,600i">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/fonts/simple-line-icons.min.css">
    <link rel="stylesheet" href="assets/css/card.css">
    <link rel="stylesheet" href="assets/css/jquery-ui.css">
    <link rel="stylesheet" href="assets/css/MUSA_no-more-tables.css">
    <link rel="stylesheet" href="assets/css/signallight.css">
    <link rel="stylesheet" href="assets/css/Tabbed-Panel.css">
    <link rel="stylesheet" href="assets/css/theme.css?v=20260804.1">
    <!--link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"-->
    <script src="assets/js/Utils.js"></script>
    <!--<script src="assets/js/LanguageTimezone.js"></script>
    <script src="assets/js/TA.js"></script>
    <script src="assets/js/AdvDecPie.js"></script>-->
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }
        main.page ul {
            padding-left: 40px;
        }
        .note {
            font-style: italic;
            color: #666;
        }
        .screenshot {
            margin: 20px 0;
        }
        .screenshot img {
            max-width: 100%;
            max-height: 440px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .top-bar {
          width: 100%;
          background: var(--bg, #faf6f0);
          padding: 10px 20px;
          display: flex;
          justify-content: flex-end;
          align-items: center;
          flex-wrap: wrap;
          z-index: 1000;
          gap: 10px;
        }
        .existing-user-help {
            margin: 0 0 24px;
            padding: 18px 20px;
            background: var(--bg-card, #fff);
            color: var(--text-primary, #000);
            border: 1px solid var(--border-color, #dee2e6);
            border-left: 4px solid #3b99e0;
            border-radius: 6px;
            text-align: left;
        }
        .existing-user-help h2 {
            margin: 0 0 10px;
            color: inherit;
            font-size: 1.2rem;
        }
        .existing-user-help ol {
            margin: 10px 0;
            padding-left: 24px;
        }
        .existing-user-help .legacy-login-note {
            margin: 12px 0 0;
            color: var(--text-muted, #666);
            font-size: 0.95rem;
        }
        .existing-user-help a {
            color: var(--text-link, #007bff);
        }
        .existing-user-help a:hover,
        .existing-user-help a:focus {
            color: var(--text-link-hover, #0056b3);
        }
        .text-info {
            color: #3b99e0 !important;
        }

        .btn-outline-primary {
            transition: all 0.2s ease;
        }

        .btn-outline-primary:hover {
            background-color: lightblue !important;
        }
    </style>
</head>

<body><style>
.not-selectable {
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}
</style>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>

<?php
$page = 'writeforus';
$write_for_us_login_url = 'account_sso.php?target=new-post';
$write_for_us_register_url = 'account_sso.php?target=new-post&signup=1';
$legacy_wordpress_login_url = '/blog/wp-login.php';
include "./header.php";
?>

    <main class="page">
    <section class="clean-block about-us" style="padding: 0 0 30px;"><div class="container">
    <div class="container">
    <div class="block-heading">
        <br><br>
        <div class="top-bar">
            <a href="<?php echo htmlspecialchars($write_for_us_login_url, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-primary me-2">Sign in with 360MiQ</a>
            <a href="<?php echo htmlspecialchars($write_for_us_register_url, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary">Create or connect your account</a>
        </div>
        <h1 class="text-info">Write for Us – Share Your Investment Insights</h1>
    </div>

  <aside class="existing-user-help" aria-labelledby="existing-wordpress-user-title">
      <h2 id="existing-wordpress-user-title">Already have a WordPress contributor account?</h2>
      <p>Connect it to the new 360MiQ login without losing your author profile, published articles, drafts, or permissions.</p>
      <ol>
          <li>Select <strong>Create or connect your account</strong>.</li>
          <li>Register with the <strong>same email address</strong> used by your WordPress account, then verify that email.</li>
          <li>Sign in with 360MiQ. Your existing WordPress account will be connected automatically.</li>
      </ol>
      <p>Your old WordPress password does not become your 360MiQ password. Choose a new 360MiQ password or use Google sign-in.</p>
      <p class="legacy-login-note">Need access during the transition? <a href="<?php echo htmlspecialchars($legacy_wordpress_login_url, ENT_QUOTES, 'UTF-8'); ?>">Use the legacy WordPress login</a>.</p>
  </aside>

  <p>Are you passionate about investing, trading strategies, or financial markets? Join our free community of contributors and share your expertise with a global audience eager to learn and grow. Use your 360MiQ account to open the article editor—there is no separate WordPress registration or password to manage. All submissions are saved for editorial review and cannot be published directly by a new contributor.</p>
  <br>
  <h4>Why Contribute?</h4>
  <ul>
    <li><strong>Reach a Targeted Audience</strong>: Your articles will be featured on our platform, reaching thousands of readers interested in investment and trading.</li>
    <li><strong>Establish Authority</strong>: Build your reputation as a thought leader in the financial industry.</li>
    <li><strong>Engage with Peers</strong>: Connect with other professionals and enthusiasts in the investment community.</li>
  </ul>
  <br>
  <h4>Where Your Article Will Appear</h4>
  <p><strong>Please note:</strong> Only relevant articles that include a featured image or an inline image will be featured in these locations.</p>
    <ul>
        <li><div class="screenshot-caption">Your article will be spotlighted on <strong>Home page</strong> to attract broad readership.</div>
        <div class="screenshot">
            <img src="/assets/img/writeforus_home.jpg" alt="Home Page Feature"/>
        </div></li>
        <li><div class="screenshot-caption">Your article will be linked on the top of <strong>Market pages</strong> alongside trending financial content.</div>
        <div class="screenshot">
            <img src="/assets/img/writeforus_market.jpg" alt="Market Page Feature"/>
        </div></li>
        <li><div class="screenshot-caption">Stock symbol tagged articles may appear on <strong>individual Stock Info pages</strong> for added context.</div>
        <div class="screenshot">
            <img src="/assets/img/writeforus_stock.jpg" alt="Stock Info Page Feature"/>
        </div></li>
        <li><div class="screenshot-caption">Your analysis will be showcased on the dedicated <strong>Analysis section</strong>.</div>
        <div class="screenshot">
            <img src="/assets/img/writeforus_analysis.jpg?" alt="Analysis Page Feature"/>
        </div></li>
    </ul>
  <br>
  <h4>Topics We Welcome</h4>
  <p>We are looking for insightful and original articles on:</p>
  <ul>
    <li><strong>Investment Strategies</strong>: Stock analysis, portfolio management, long-term investing.</li>
    <li><strong>Trading Techniques</strong>: Day trading, swing trading, technical analysis.</li>
    <li><strong>Market Analysis</strong>: Economic trends, sector performance, market forecasts.</li>
    <li><strong>Financial Instruments</strong>: ETFs, options, futures, cryptocurrencies.</li>
    <li><strong>Personal Finance</strong>: Wealth building, retirement planning, risk management.</li>
  </ul>
  <br>
  <h4>Submission Guidelines</h4>
  <p>To maintain the quality and relevance of our content, please adhere to the following guidelines:</p>
  <ul>
    <li><strong>Original Content</strong>: Submissions must be original.</li>
    <li><strong>Word Count</strong>: Articles should be between 800 to 1,500 words.</li>
    <li><strong>Clarity and Structure</strong>: Use clear language, subheadings, bullet points, and concise paragraphs to enhance readability.</li>
    <li><strong>Data and Sources</strong>: Support your arguments with data and cite reputable sources.</li>
    <li><strong>Tone</strong>: Maintain a professional and informative tone suitable for a financial audience.</li>
    <li><strong>No Promotional Content</strong>: Articles should be educational and not serve as advertisements for products or services.</li>
    <li><strong>Author Links</strong>: You are welcome to include one link in your article back to your website and/or social media profiles. Additional links may be included in your author bio.</li>
  </ul>
  <br>
  <h4>How to Submit</h4>
  <ol>
      <li><a href="<?php echo htmlspecialchars($write_for_us_register_url, ENT_QUOTES, 'UTF-8'); ?>">Create or connect a free 360MiQ account</a>, or <a href="<?php echo htmlspecialchars($write_for_us_login_url, ENT_QUOTES, 'UTF-8'); ?>">sign in if you already have one</a>. Google sign-in is supported.</li>
      <li>Review your <strong>Public display name</strong> in Account Settings. This is the author name shown with articles created through 360MiQ SSO.</li>
      <li>Open the WordPress editor through the Write for Us link, write your article, and submit it for review.</li>
      <li>An editor will review the draft before anything is published.</li>
  </ol>
  <p><strong>Existing WordPress contributors:</strong> always use the same email address when connecting your 360MiQ account. Existing Contributor, Author, Editor and Administrator roles are preserved.</p>
  <p>Our editorial team will review your submission and respond within 1 business day.</p>

  <br>
  <h4>Contributor Benefits</h4>
  <ul>
    <li><strong>Author Profile</strong>: Feature your bio and a link to your professional website or LinkedIn profile.</li>
    <li><strong>Social Media Promotion</strong>: We will promote your article across our social media channels.</li>
    <!--li><strong>Newsletter Inclusion</strong>: Top articles may be included in our weekly newsletter, reaching a wider audience.</li-->
  </ul>

  <p>Join us in educating and empowering readers to make informed financial decisions. We look forward to your contributions!</p>

  <p class="note">*Note: We reserve the right to edit submissions for clarity, grammar, and alignment with our content standards.*</p>
</div>
</section>
    </main>

<?php include "./footer.php" ?>

    <!--<script src="assets/js/jquery.min.js"></script>-->
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <!--<script src="assets/js/smart-forms.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/baguettebox.js/1.10.0/baguetteBox.min.js"></script>
    <script src="assets/js/smoothproducts.min.js"></script>
    <script src="assets/js/highcharts-theme.js"></script>
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/jquery-ui1.12.1.min.js"></script>
    <!--<script src="assets/js/jquery.ui.treemap.js"></script>-->
</body>

</html>
