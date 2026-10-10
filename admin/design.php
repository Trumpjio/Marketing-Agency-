<?php require_once __DIR__ . '/_layout.php';

$design = design_load();
$defaults = design_defaults();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    <h3 style="margin-top:0;">Background grid</h3>
    <div class="field" style="display:flex;align-items:center;gap:0.75rem;">
      <label class="switch">
        <input type="checkbox" name="grid_enabled" value="1" <?= $d['grid_enabled']?'checked':'' ?> data-design="grid_enabled" />
        <span class="slider"></span>
      </label>
      <span>Show thin grid lines across the page background</span>
    </div>
    <div class="field">
      <label>Grid opacity — <span data-echo="grid_opacity"><?= $d['grid_opacity'] ?></span></label>
      <input type="range" min="0" max="0.1" step="0.002" name="grid_opacity" value="<?= $d['grid_opacity'] ?>" data-design="grid_opacity" oninput="document.querySelector('[data-echo=grid_opacity]').textContent=this.value" />
    </div>
    <div class="field" style="display:flex;align-items:center;gap:0.75rem;">
      <label style="width:160px;margin:0;font-size:0.825rem;">Grid line colour</label>
      <input type="color" value="<?= h($d['grid_color']) ?>" data-color-pair="grid_color" style="width:46px;height:38px;border:1px solid var(--line);padding:0;border-radius:4px;background:#fff;cursor:pointer;flex-shrink:0;" />
      <input type="text" name="grid_color" value="<?= h($d['grid_color']) ?>" style="flex:1;font-family:monospace;font-size:0.825rem;" data-design="grid_color" />
    </div>
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
