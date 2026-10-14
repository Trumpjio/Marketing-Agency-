<?php require_once __DIR__ . '/_layout.php';
$settings = settings_load();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $old = $settings;
    foreach ($settings as $key => $val) {
        $settings[$key] = trim($_POST[$key] ?? $val);
    }
    settings_save($settings);
    // Propagate changes across all HTML files
    require __DIR__ . '/_build.php';
    build_apply_settings($old, $settings);
    $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'Settings saved and applied to every page.'];
    header('Location: settings.php');
    exit;
}
?>
<h2>Site Settings</h2>
<p style="color:#6b7280;margin-bottom:1.5rem;">These settings apply site-wide. Saving will update every page automatically.</p>

<form method="post">
  <?= csrf_field() ?>
  <div class="card">
    <h3 style="margin-top:0;">Brand</h3>
    <div class="field">
      <label>Brand name (shown in nav + footer)</label>
      <input type="text" name="brand_name" value="<?= h($settings['brand_name']) ?>" required>
      <div class="help">Shown as text if no logo image is uploaded.</div>
    </div>
    <div class="field">
      <label>Logo image (optional — replaces the text brand in nav + footer when set)</label>
      <div class="image-picker-field">
        <img class="image-picker-preview" data-preview-for="brand_logo" src="<?= !empty($settings['brand_logo']) ? '../' . h(ltrim($settings['brand_logo'], '/')) : '' ?>" alt="" onerror="this.style.visibility='hidden'" style="max-height:40px;object-fit:contain;">
        <input type="text" id="brand_logo" name="brand_logo" value="<?= h($settings['brand_logo'] ?? '') ?>" placeholder="click Pick image →">
        <button type="button" class="image-picker-btn" onclick="openMedia('brand_logo')">📷 Pick logo</button>
      </div>
      <div class="help">Upload at Media Library (SVG preferred, 2:1 ratio works best). Max height on the live site is 32px nav / 40px footer.</div>
    </div>
    <div class="field">
      <label>Brand tagline (shown in footer)</label>
      <input type="text" name="brand_tagline" value="<?= h($settings['brand_tagline']) ?>">
    </div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">Call to Action</h3>
    <div class="field">
      <label>CTA button URL (e.g. your Calendly link)</label>
      <input type="url" name="cta_url" value="<?= h($settings['cta_url']) ?>" required>
      <div class="help">Every "Book a Call" button on the site links here.</div>
    </div>
    <div class="field">
      <label>CTA button label</label>
      <input type="text" name="cta_label" value="<?= h($settings['cta_label']) ?>" required>
    </div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">Contact</h3>
    <div class="row">
      <div class="field">
        <label>Email</label>
        <input type="email" name="email" value="<?= h($settings['email']) ?>">
      </div>
      <div class="field">
        <label>Phone (optional, shown on Contact page)</label>
        <input type="text" name="phone" value="<?= h($settings['phone']) ?>" placeholder="+971 50 123 4567">
      </div>
    </div>
    <div class="field">
      <label>WhatsApp number (digits only, international format, no + or spaces)</label>
      <input type="text" name="whatsapp" value="<?= h($settings['whatsapp'] ?? '') ?>" placeholder="971501234567" pattern="[0-9]*">
      <div class="help">Used for the WhatsApp button on the Contact page. Leave blank to hide the button.</div>
    </div>
    <div class="field">
      <label>Address (optional)</label>
      <input type="text" name="address" value="<?= h($settings['address']) ?>">
    </div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">Social Links (optional)</h3>
    <div class="field">
      <label>LinkedIn URL</label>
      <input type="url" name="social_linkedin" value="<?= h($settings['social_linkedin']) ?>">
    </div>
    <div class="field">
      <label>Instagram URL</label>
      <input type="url" name="social_instagram" value="<?= h($settings['social_instagram']) ?>">
    </div>
    <div class="field">
      <label>Twitter / X URL</label>
      <input type="url" name="social_twitter" value="<?= h($settings['social_twitter']) ?>">
    </div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">SEO & Site</h3>
    <div class="field">
      <label>Site base URL (used for canonical tags)</label>
      <input type="url" name="base_url" value="<?= h($settings['base_url']) ?>">
    </div>
    <div class="field">
      <label>Footer copyright line</label>
      <input type="text" name="footer_copyright" value="<?= h($settings['footer_copyright']) ?>">
    </div>
  </div>

  <button type="submit" class="btn">Save & Apply to Site</button>
</form>

<?php require __DIR__ . '/_footer.php'; ?>
