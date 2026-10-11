<?php require_once __DIR__ . '/_layout.php';

$design = design_load();
$defaults = design_defaults();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action'] ?? '') === 'restore') {
        design_save($defaults);
        $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'Design restored to defaults.'];
        header('Location: design.php'); exit;
    }

    $new = $design;
    // Validate every colour; reject anything that is not a hex or rgba() value.
    $colorKeys = ['primary','secondary','bg_page','bg_surface','bg_soft','text_primary','text_secondary','btn_primary_bg','btn_primary_text','border','grid_color'];
    foreach ($colorKeys as $k) {
        $v = trim($_POST[$k] ?? '');
        if ($v !== '' && (preg_match('/^#[0-9a-fA-F]{3,8}$/', $v) || preg_match('/^rgba?\([0-9., ]+\)$/i', $v))) {
            $new[$k] = $v;
        }
    }
    $new['heading_font']    = in_array($_POST['heading_font'] ?? '', ['Manrope','Inter','Playfair Display','Space Grotesk','Fraunces','IBM Plex Sans'], true) ? $_POST['heading_font'] : $design['heading_font'];
    $new['body_font']       = in_array($_POST['body_font'] ?? '', ['Inter','Manrope','Space Grotesk','IBM Plex Sans','Fraunces'], true) ? $_POST['body_font'] : $design['body_font'];
    $new['body_size']       = max(14, min(22, intval($_POST['body_size'] ?? 16)));
    $new['line_height']     = max(1.3, min(2.0, floatval($_POST['line_height'] ?? 1.6)));
    $new['letter_spacing']  = max(-0.05, min(0.05, floatval($_POST['letter_spacing'] ?? -0.005)));
    $new['heading_weight']  = in_array(intval($_POST['heading_weight'] ?? 700), [400, 500, 600, 700, 800]) ? intval($_POST['heading_weight']) : 700;
    $new['btn_radius']      = max(2, min(999, intval($_POST['btn_radius'] ?? 980)));
    $new['grid_enabled']    = empty($_POST['grid_enabled']) ? 0 : 1;
    $new['grid_opacity']    = max(0, min(0.1, floatval($_POST['grid_opacity'] ?? 0.028)));
    $new['guide_enabled']   = empty($_POST['guide_enabled']) ? 0 : 1;
    $new['hints_enabled']   = empty($_POST['hints_enabled']) ? 0 : 1;
    $new['guide_tail']      = empty($_POST['guide_tail']) ? 0 : 1;
    $new['guide_trigger']   = in_array($_POST['guide_trigger'] ?? '', ['inactivity','click','error'], true) ? $_POST['guide_trigger'] : 'inactivity';
    $new['guide_where']     = in_array($_POST['guide_where'] ?? '', ['all','home','insights','off'], true) ? $_POST['guide_where'] : 'all';
    $new['tip_style']       = in_array($_POST['tip_style'] ?? '', ['card','minimal'], true) ? $_POST['tip_style'] : 'card';
    $new['guide_size']      = in_array($_POST['guide_size'] ?? '', ['sm','md','lg'], true) ? $_POST['guide_size'] : 'md';
    foreach (['guide_face_color','guide_eye_color'] as $k) {
        $v = trim($_POST[$k] ?? '');
        if ($v !== '' && preg_match('/^#[0-9a-fA-F]{3,8}$/', $v)) $new[$k] = $v;
    }
    // Background preset + custom CSS
    $new['bg_preset']       = in_array($_POST['bg_preset'] ?? '', ['grid','plain','dots','gradient','custom'], true) ? $_POST['bg_preset'] : 'grid';
    $new['bg_custom_css']   = (string)($_POST['bg_custom_css'] ?? '');

    design_save($new);
    $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'Design settings saved. Changes apply to the live site immediately.'];
    header('Location: design.php'); exit;
}

$d = $design;
$fontOptions = ['Inter','Manrope','Space Grotesk','Playfair Display','IBM Plex Sans','Fraunces'];
?>

<h2>Design</h2>
<p style="color:#6b7280;margin-bottom:1.5rem;">Change the colours, typography and buttons across the whole site. Edit a value on the left, see it on the right. Click <strong>Save design</strong> to apply to every visitor.</p>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;align-items:start;">
<form method="post" id="design-form" style="min-width:0;">
  <?= csrf_field() ?>

  <div class="card">
    <h3 style="margin-top:0;">Colours</h3>
    <?php
    $colorFields = [
      'primary' => 'Primary brand',
      'secondary' => 'Secondary brand',
      'bg_page' => 'Page background',
      'bg_surface' => 'Card background',
      'bg_soft' => 'Soft tint background',
      'text_primary' => 'Primary text',
      'text_secondary' => 'Secondary text',
      'border' => 'Border lines',
      'btn_primary_bg' => 'Primary button fill',
      'btn_primary_text' => 'Primary button text',
    ];
    foreach ($colorFields as $k => $label):
      $val = $d[$k];
      // Convert rgba() to a plausible hex for the native picker (fallback to #cccccc for the border rgba default)
      $hex = preg_match('/^#/', $val) ? $val : '#cccccc';
    ?>
      <div class="field" style="display:flex;align-items:center;gap:0.75rem;">
        <label style="width:160px;margin:0;font-size:0.825rem;"><?= h($label) ?></label>
        <input type="color" value="<?= h($hex) ?>" data-color-pair="<?= $k ?>" style="width:46px;height:38px;border:1px solid var(--line);padding:0;border-radius:4px;background:#fff;cursor:pointer;flex-shrink:0;" />
        <input type="text" name="<?= $k ?>" value="<?= h($val) ?>" style="flex:1;font-family:monospace;font-size:0.825rem;" data-design="<?= $k ?>" />
      </div>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">Typography</h3>
    <div class="row">
      <div class="field">
        <label>Heading font</label>
        <select name="heading_font" data-design="heading_font">
          <?php foreach ($fontOptions as $f): ?>
            <option value="<?= $f ?>" <?= $d['heading_font']===$f?'selected':'' ?>><?= $f ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label>Body font</label>
        <select name="body_font" data-design="body_font">
          <?php foreach ($fontOptions as $f): ?>
            <option value="<?= $f ?>" <?= $d['body_font']===$f?'selected':'' ?>><?= $f ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="row">
      <div class="field">
        <label>Body text size — <span data-echo="body_size"><?= $d['body_size'] ?></span>px</label>
        <input type="range" min="14" max="22" step="1" name="body_size" value="<?= $d['body_size'] ?>" data-design="body_size" oninput="document.querySelector('[data-echo=body_size]').textContent=this.value" />
      </div>
      <div class="field">
        <label>Line height — <span data-echo="line_height"><?= $d['line_height'] ?></span></label>
        <input type="range" min="1.3" max="2.0" step="0.05" name="line_height" value="<?= $d['line_height'] ?>" data-design="line_height" oninput="document.querySelector('[data-echo=line_height]').textContent=this.value" />
      </div>
    </div>
    <div class="row">
      <div class="field">
        <label>Letter spacing — <span data-echo="letter_spacing"><?= $d['letter_spacing'] ?></span>em</label>
        <input type="range" min="-0.05" max="0.05" step="0.005" name="letter_spacing" value="<?= $d['letter_spacing'] ?>" data-design="letter_spacing" oninput="document.querySelector('[data-echo=letter_spacing]').textContent=this.value" />
      </div>
      <div class="field">
        <label>Heading weight</label>
        <select name="heading_weight" data-design="heading_weight">
          <?php foreach ([400,500,600,700,800] as $w): ?>
            <option value="<?= $w ?>" <?= intval($d['heading_weight'])===$w?'selected':'' ?>><?= $w ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">Buttons</h3>
    <div class="field">
      <label>Button corner radius — <span data-echo="btn_radius"><?= $d['btn_radius'] ?></span>px</label>
      <input type="range" min="2" max="999" step="1" name="btn_radius" value="<?= $d['btn_radius'] ?>" data-design="btn_radius" oninput="document.querySelector('[data-echo=btn_radius]').textContent=this.value" />
      <div class="help">980 = fully pill-shaped. 8 = sharp Linear-style. 0 = square.</div>
    </div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">Floating guide (the animated face)</h3>
    <div class="field" style="display:flex;align-items:center;gap:0.75rem;">
      <label class="switch">
        <input type="checkbox" name="guide_enabled" value="1" <?= $d['guide_enabled']?'checked':'' ?> data-design="guide_enabled" />
        <span class="slider"></span>
      </label>
      <span>Show the floating icon</span>
    </div>
    <div class="field" style="display:flex;align-items:center;gap:0.75rem;">
      <label class="switch">
        <input type="checkbox" name="hints_enabled" value="1" <?= $d['hints_enabled']?'checked':'' ?> data-design="hints_enabled" />
        <span class="slider"></span>
      </label>
      <span>Allow the icon to pop up contextual hints</span>
    </div>
    <div class="help">Switch hints off and the icon still floats but never shows a tooltip unless the visitor clicks it.</div>

    <div class="field" style="display:flex;align-items:center;gap:0.75rem;">
      <label class="switch">
        <input type="checkbox" name="guide_tail" value="1" <?= $d['guide_tail']?'checked':'' ?> data-design="guide_tail" />
        <span class="slider"></span>
      </label>
      <span>Show the small tail/pointer under the icon</span>
    </div>

    <div class="field">
      <label>When should the icon activate?</label>
      <select name="guide_trigger" data-design="guide_trigger">
        <option value="inactivity" <?= $d['guide_trigger']==='inactivity'?'selected':'' ?>>Always (shows a hint 2 s after scrolling stops)</option>
        <option value="click"      <?= $d['guide_trigger']==='click'?'selected':'' ?>>Only when the visitor clicks the face</option>
        <option value="error"      <?= $d['guide_trigger']==='error'?'selected':'' ?>>Only when a form shows an error</option>
      </select>
      <div class="help">The icon itself is still always visible (unless hidden above). This controls when it tries to help the visitor.</div>
    </div>

    <div class="row">
      <div class="field">
        <label>Where should the icon appear?</label>
        <select name="guide_where" data-design="guide_where">
          <option value="all"      <?= $d['guide_where']==='all'?'selected':'' ?>>Everywhere</option>
          <option value="home"     <?= $d['guide_where']==='home'?'selected':'' ?>>Homepage only</option>
          <option value="insights" <?= $d['guide_where']==='insights'?'selected':'' ?>>Insights pages only</option>
          <option value="off"      <?= $d['guide_where']==='off'?'selected':'' ?>>Nowhere (same as switching off)</option>
        </select>
      </div>
      <div class="field">
        <label>Icon size</label>
        <select name="guide_size" data-design="guide_size">
          <option value="sm" <?= $d['guide_size']==='sm'?'selected':'' ?>>Small (36px)</option>
          <option value="md" <?= $d['guide_size']==='md'?'selected':'' ?>>Medium (48px)</option>
          <option value="lg" <?= $d['guide_size']==='lg'?'selected':'' ?>>Large (60px)</option>
        </select>
      </div>
    </div>

    <div class="field" style="display:flex;align-items:center;gap:0.75rem;">
      <label style="width:160px;margin:0;font-size:0.825rem;">Icon body colour</label>
      <input type="color" value="<?= h($d['guide_face_color']) ?>" data-color-pair="guide_face_color" style="width:46px;height:38px;border:1px solid var(--line);padding:0;border-radius:4px;background:#fff;cursor:pointer;flex-shrink:0;" />
      <input type="text" name="guide_face_color" value="<?= h($d['guide_face_color']) ?>" style="flex:1;font-family:monospace;font-size:0.825rem;" data-design="guide_face_color" />
    </div>
    <div class="field" style="display:flex;align-items:center;gap:0.75rem;">
      <label style="width:160px;margin:0;font-size:0.825rem;">Icon eyes + mouth</label>
      <input type="color" value="<?= h($d['guide_eye_color']) ?>" data-color-pair="guide_eye_color" style="width:46px;height:38px;border:1px solid var(--line);padding:0;border-radius:4px;background:#fff;cursor:pointer;flex-shrink:0;" />
      <input type="text" name="guide_eye_color" value="<?= h($d['guide_eye_color']) ?>" style="flex:1;font-family:monospace;font-size:0.825rem;" data-design="guide_eye_color" />
    </div>

    <div class="field">
      <label>Pop-up style</label>
      <select name="tip_style" data-design="tip_style">
        <option value="card"    <?= $d['tip_style']==='card'?'selected':'' ?>>Card — white rounded box, shadow, label and body</option>
        <option value="minimal" <?= $d['tip_style']==='minimal'?'selected':'' ?>>Minimal — plain text bubble, no label, flat</option>
      </select>
    </div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">Background</h3>
    <div class="field">
      <label>Preset</label>
      <select name="bg_preset" data-design="bg_preset" id="bg-preset">
        <option value="grid"     <?= $d['bg_preset']==='grid'?'selected':'' ?>>Grid — thin lines (current OrcaPulse default)</option>
        <option value="plain"    <?= $d['bg_preset']==='plain'?'selected':'' ?>>Plain — no decoration, pure white</option>
        <option value="dots"     <?= $d['bg_preset']==='dots'?'selected':'' ?>>Dots — subtle dot grid</option>
        <option value="gradient" <?= $d['bg_preset']==='gradient'?'selected':'' ?>>Gradient — soft brand-colour glows</option>
        <option value="custom"   <?= $d['bg_preset']==='custom'?'selected':'' ?>>Custom — write your own CSS</option>
      </select>
      <div class="help">Pick a preset to change the look of the page background. "Custom" lets you paste your own CSS.</div>
    </div>
    <div class="field">
      <label>Grid / dots opacity — <span data-echo="grid_opacity"><?= $d['grid_opacity'] ?></span></label>
      <input type="range" min="0" max="0.1" step="0.002" name="grid_opacity" value="<?= $d['grid_opacity'] ?>" data-design="grid_opacity" oninput="document.querySelector('[data-echo=grid_opacity]').textContent=this.value" />
      <div class="help">Only applies to the Grid and Dots presets.</div>
    </div>
    <div class="field" style="display:flex;align-items:center;gap:0.75rem;">
      <label style="width:160px;margin:0;font-size:0.825rem;">Grid / dots colour</label>
      <input type="color" value="<?= h($d['grid_color']) ?>" data-color-pair="grid_color" style="width:46px;height:38px;border:1px solid var(--line);padding:0;border-radius:4px;background:#fff;cursor:pointer;flex-shrink:0;" />
      <input type="text" name="grid_color" value="<?= h($d['grid_color']) ?>" style="flex:1;font-family:monospace;font-size:0.825rem;" data-design="grid_color" />
    </div>
    <!-- Keep hidden grid_enabled toggle for backward compatibility: Grid preset implies on -->
    <input type="hidden" name="grid_enabled" value="1" data-design="grid_enabled" />
    <div class="field" id="bg-custom-field" style="display:none;">
      <label>Custom background CSS</label>
      <textarea name="bg_custom_css" rows="6" style="font-family:ui-monospace,Menlo,Consolas,monospace;font-size:0.825rem;" data-design="bg_custom_css" placeholder=".page::before {&#10;  background-image: url(uploads/your-pattern.svg);&#10;  background-size: 60px 60px;&#10;}"><?= h($d['bg_custom_css']) ?></textarea>
      <div class="help">Target <code>.page::before</code> or <code>body</code>. <code>&lt;script&gt;</code> tags and <code>javascript:</code> URLs are stripped on save.</div>
    </div>
    <script>
      (function () {
        var sel = document.getElementById('bg-preset');
        var custom = document.getElementById('bg-custom-field');
        function toggle() { custom.style.display = sel.value === 'custom' ? 'block' : 'none'; }
        sel.addEventListener('change', toggle); toggle();
      })();
    </script>
  </div>

  <div style="display:flex;gap:0.5rem;position:sticky;bottom:0;background:var(--bg);padding:1rem 0;border-top:1px solid var(--line);flex-wrap:wrap;">
    <button type="submit" class="btn">Save design</button>
    <a href="design.php" class="btn btn--ghost">Cancel</a>
    <button type="submit" formaction="design.php" formmethod="post" name="action" value="restore" class="btn btn--ghost" onclick="return confirm('Restore every design setting to the OrcaPulse defaults? This cannot be undone.');" style="margin-left:auto;">↩ Restore original design</button>
  </div>
</form>

<div style="position:sticky;top:1rem;align-self:start;min-width:0;">
  <div style="font-size:0.75rem;letter-spacing:0.1em;text-transform:uppercase;color:var(--muted);font-weight:700;margin-bottom:0.5rem;">Live preview</div>
  <div id="design-preview" style="border:1px solid var(--line);border-radius:12px;overflow:hidden;background:#fff;">
    <iframe id="preview-iframe" src="../index.html?preview=1" style="width:100%;height:70vh;border:0;background:#fff;" title="Design preview"></iframe>
  </div>
  <p style="color:var(--muted);font-size:0.75rem;margin-top:0.5rem;">Preview reflects the form above. Save to apply to the live site.</p>
</div>
</div>

<script>
// --------- Live-preview wiring ---------
const iframe = document.getElementById('preview-iframe');

function hexToRgba(hex, a) {
  hex = hex.replace('#', '');
  if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
  const r = parseInt(hex.substr(0, 2), 16), g = parseInt(hex.substr(2, 2), 16), b = parseInt(hex.substr(4, 2), 16);
  return `rgba(${r}, ${g}, ${b}, ${a})`;
}

function collectValues() {
  const vals = {};
  document.querySelectorAll('[data-design]').forEach(el => {
    const k = el.getAttribute('data-design');
    if (el.type === 'checkbox') vals[k] = el.checked ? 1 : 0;
    else vals[k] = el.value;
  });
  return vals;
}

function buildCSS(v) {
  let css = `:root{
    --blue:${v.primary};--cyan:${v.secondary};--blue-deep:${v.primary};
    --ap-bg:${v.bg_page};--ap-bg-soft:${v.bg_soft};--surface:${v.bg_surface};
    --ap-ink:${v.text_primary};--ink:${v.text_primary};
    --ap-muted:${v.text_secondary};--muted:${v.text_secondary};
    --ap-line:${v.border};--line:${v.border};
  }
  body{font-size:${v.body_size}px;line-height:${v.line_height};letter-spacing:${v.letter_spacing}em;font-family:'${v.body_font}',sans-serif;}
  h1,h2,h3,.section-head h2,.hero h1{font-family:'${v.heading_font}','Manrope',sans-serif;font-weight:${v.heading_weight};}
  .btn{border-radius:${v.btn_radius}px;}
  .btn--primary,.cta .btn--primary{background:${v.btn_primary_bg};color:${v.btn_primary_text};}
  .btn--primary:hover,.cta .btn--primary:hover{background:${v.primary};}
  `;
  if (!parseInt(v.grid_enabled)) {
    css += `.page::before{display:none!important;}`;
  } else {
    const rgba = hexToRgba(v.grid_color || '#000000', parseFloat(v.grid_opacity));
    css += `.page::before{background-image:linear-gradient(${rgba} 1px,transparent 1px),linear-gradient(90deg,${rgba} 1px,transparent 1px);}`;
  }
  return css;
}

function applyPreview() {
  if (!iframe.contentDocument) return;
  let style = iframe.contentDocument.getElementById('design-preview-css');
  if (!style) {
    style = iframe.contentDocument.createElement('style');
    style.id = 'design-preview-css';
    iframe.contentDocument.head.appendChild(style);
  }
  style.textContent = buildCSS(collectValues());
}

// Keep color-picker and text in sync
document.querySelectorAll('[data-color-pair]').forEach(picker => {
  const key = picker.getAttribute('data-color-pair');
  const text = document.querySelector(`[data-design="${key}"]`);
  picker.addEventListener('input', () => { text.value = picker.value; applyPreview(); });
  text.addEventListener('input', () => {
    if (/^#[0-9a-fA-F]{6}$/.test(text.value)) picker.value = text.value;
    applyPreview();
  });
});

// Re-apply preview on any field change
document.querySelectorAll('[data-design]').forEach(el => {
  el.addEventListener('input', applyPreview);
  el.addEventListener('change', applyPreview);
});

iframe.addEventListener('load', () => { setTimeout(applyPreview, 100); });
</script>

<?php require __DIR__ . '/_footer.php'; ?>
