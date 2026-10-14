<?php require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/_sections.php';
require_once __DIR__ . '/_library.php';

$file = basename($_GET['file'] ?? '');
$filepath = page_path($file);
if (!$filepath) {
    $_SESSION['flash'] = ['type' => 'err', 'msg' => 'Page not found.'];
    header('Location: pages.php'); exit;
}
$html = page_working_html($file);          // the draft if there is one, otherwise the live page
$has_pending = page_has_pending($file);
$self = 'page-edit.php?file=' . urlencode($file);

/* ---------- Handle POST ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? 'save';

    if ($action === 'discard') {
        page_discard($file);
        $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'Draft discarded. The live page was not changed.'];
        header('Location: ' . $self); exit;
    }
    if ($action === 'undo') {
        $_SESSION['flash'] = (!$has_pending && page_undo($filepath))
            ? ['type' => 'ok', 'msg' => 'Went back to the version of this page from before the last publish.']
            : ['type' => 'err', 'msg' => 'There is no earlier version to go back to (or you have an unpublished draft).'];
        header('Location: ' . $self); exit;
    }

    if (($_POST['page_hash'] ?? '') !== md5($html)) {
        $_SESSION['flash'] = ['type' => 'err', 'msg' => 'This page was changed somewhere else after you opened it, so nothing was saved. Your screen has been refreshed. Please make your edit again.'];
        header('Location: ' . $self); exit;
    }

    $cur = [
        'title' => parse_title($html), 'desc' => parse_desc($html), 'h1' => parse_h1($html),
        'sub' => parse_hero_sub($html), 'cta_url' => parse_cta_url($html), 'cta_label' => parse_cta_label($html),
    ];
    $new = [
        'title' => trim($_POST['title'] ?? ''), 'desc' => trim($_POST['desc'] ?? ''),
        'h1' => trim(str_replace("\r", '', $_POST['h1'] ?? '')), 'sub' => trim($_POST['hero_sub'] ?? ''),
        'cta_url' => trim($_POST['cta_url'] ?? ''), 'cta_label' => trim($_POST['cta_label'] ?? ''),
    ];

    // 1. Sections first (their markup may contain the headline/CTA), then the simple fields.
    if (!empty($_POST['sections_submitted'])) {
        [$html2, $err] = sec_process_post($html, $_POST);
        if ($err) {
            $_SESSION['flash'] = ['type' => 'err', 'msg' => h($err)];
            header('Location: ' . $self); exit;
        }
        $html = lib_render_page($html2, $file);
    }
    $curAfter = ['cta_url' => parse_cta_url($html), 'cta_label' => parse_cta_label($html)];
    if ($new['title'] !== '' && $new['title'] !== $cur['title']) $html = replace_title($html, $new['title']);
    if ($new['desc'] !== '' && $new['desc'] !== $cur['desc'])    $html = replace_desc($html, $new['desc']);
    if ($new['h1'] !== '' && $new['h1'] !== $cur['h1'])          $html = replace_h1($html, $new['h1']);
    if ($new['sub'] !== '' && $new['sub'] !== $cur['sub'])       $html = replace_hero_sub($html, $new['sub']);
    if ($new['cta_url'] !== '' && $new['cta_url'] !== $cur['cta_url'])       $html = replace_cta_url($html, $curAfter['cta_url'], $new['cta_url']);
    if ($new['cta_label'] !== '' && $new['cta_label'] !== $cur['cta_label']) $html = replace_cta_label($html, $curAfter['cta_label'], $new['cta_label']);

    page_pending_save($file, $html);

    if ($action === 'publish') {
        if (str_contains($html, 'uploads/placeholder.svg')) {
            $_SESSION['flash'] = ['type' => 'err', 'msg' => 'Draft saved, but not published: a section still uses the placeholder picture. Replace it (Design &amp; image) and publish again.'];
        } elseif (page_publish($file)) {
            $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'Published. <a href="../' . h($file) . '" target="_blank">View the live page →</a>'];
        } else {
            $_SESSION['flash'] = ['type' => 'err', 'msg' => 'Could not publish. Check that the site folder is writable.'];
        }
    } elseif ($action === 'preview') {
        header('Location: preview.php?file=' . urlencode($file)); exit;
    } else {
        $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'Draft saved. Visitors still see the old version until you press Publish.'];
    }
    header('Location: ' . $self); exit;
}

/* ---------- Current values for the form ---------- */
$cur_title = parse_title($html);
$cur_desc  = parse_desc($html);
$cur_h1    = parse_h1($html);
$cur_sub   = parse_hero_sub($html);
$cur_cta_url = parse_cta_url($html);
$cur_cta_label = parse_cta_label($html);
$split = sec_split($html);
$has_undo = !$has_pending && is_file(page_backup_path($filepath));
$page_name = preg_replace('/\s*\|\s*OrcaPulse\s*$/i', '', $cur_title) ?: $file;
$tpl_type = preg_match('/<body[^>]*data-template="([^"]+)"/i', $html, $tm) ? $tm[1] : '';
$templates = sec_templates();
$lib_names = ['testimonials' => 'Testimonials', 'case_studies' => 'Case studies', 'team' => 'Team', 'awards' => 'Awards', 'services' => 'Services', 'blog' => 'Blog posts'];

function faq_answer_for_textarea($a) {
    $a = preg_replace('#</p>\s*<p>#i', "\n\n", $a);
    $a = preg_replace('#^\s*<p>|</p>\s*$#i', '', trim($a));
    return trim(preg_replace('#<br\s*/?>#i', "\n", $a));
}
function sec_color_field($name, $label, $val, $id) { ?>
  <div class="dfield">
    <label><?= h($label) ?></label>
    <div class="dpair">
      <input type="color" value="<?= h($val ?: '#ffffff') ?>" data-color-for="<?= h($id) ?>">
      <input type="text" id="<?= h($id) ?>" name="<?= h($name) ?>" value="<?= h($val) ?>" placeholder="default" maxlength="7">
      <button type="button" class="mini" data-clear="<?= h($id) ?>">Reset</button>
    </div>
  </div>
<?php }

/** One section row. $tok is "s3" for existing sections or "n1" for new ones. */
function sec_row($tok, $title, $meta, $b = null, $tplKey = '') {
    global $lib_names, $tpl_type;
    $isNew = $b === null;
    $st = $isNew ? ['bg' => '', 'ink' => '', 'pad' => 'md', 'align' => '', 'imgpos' => 'above', 'hidden' => false, 'source' => ''] : sec_settings($b['attrs']);
    [$editable, $slots, $media] = $isNew ? ['', ['faq' => '', 'lib' => '', 'map' => ''], ['src' => '', 'alt' => '']] : sec_extract($b['inner']);
    $faq = $slots['faq'] !== '' ? faq_parse($slots['faq']) : [];
    $hasFaq = $slots['faq'] !== '';
    $hasMap = $slots['map'] !== '' || $st['source'] === 'map';
    $map = map_parse($slots['map']);
    $lib = $st['source'] && isset($lib_names[$st['source']]) ? $st['source'] : '';
    $n = preg_replace('/\W/', '', $tok);
    ?>
    <div class="sec-row <?= $st['hidden'] ? 'is-hidden' : '' ?> <?= $isNew ? 'is-new' : '' ?>" data-token="<?= h($tok) ?>">
      <input type="hidden" name="order[]" value="<?= h($tok) ?>">
      <?php if ($isNew): ?><input type="hidden" name="sec[<?= h($tok) ?>][tpl]" value="<?= h($tplKey) ?>"><?php endif; ?>
      <div class="sec-row__head">
        <label class="switch" title="Show or hide this section on the live page">
          <input type="checkbox" name="section_visible[]" value="<?= h($tok) ?>" <?= $st['hidden'] ? '' : 'checked' ?>>
          <span class="slider"></span>
        </label>
        <div class="sec-row__info">
          <div class="sec-row__title"><span class="sec-no"></span><?= h($title) ?><?= $isNew ? ' <em class="newtag">new</em>' : '' ?></div>
          <div class="sec-row__meta"><?= h($meta) ?><?= $st['hidden'] ? ' · hidden' : '' ?></div>
        </div>
        <div class="sec-row__actions">
          <button type="button" class="mini" data-move="-1" title="Move up">↑</button>
          <button type="button" class="mini" data-move="1" title="Move down">↓</button>
          <?php if (!$isNew): ?>
            <button type="button" class="mini" data-open="content">Edit text</button>
            <?php if ($hasFaq): ?><button type="button" class="mini" data-open="faq">Questions</button><?php endif; ?>
            <?php if ($hasMap): ?><button type="button" class="mini" data-open="map">Map</button><?php endif; ?>
            <button type="button" class="mini" data-open="design">Design &amp; image</button>
          <?php endif; ?>
          <button type="button" class="mini mini--danger" data-delete>Delete</button>
        </div>
      </div>
      <?php if ($isNew): ?>
        <div class="sec-note">Created with placeholder text when you press <strong>Save draft</strong>. Then use <em>Edit text</em> to write it.</div>
      <?php else: ?>
        <?php if ($lib === 'blog'): ?>
          <div class="sec-note">This section fills itself from your <a href="blogs.php">Blog Posts</a> (featured, topics, newest, popular). Change the heading and text here. Add or change posts there and this page updates by itself.</div>
        <?php elseif ($lib): ?>
          <div class="sec-note">This section shows entries from <a href="library.php?c=<?= h($lib) ?>">Content library → <?= h($lib_names[$lib]) ?></a>. It stays hidden on the live site until you add at least one entry.</div>
        <?php endif; ?>

        <div class="sec-panel" data-panel="content" hidden>
          <textarea id="sec_<?= $n ?>" name="sec[<?= h($tok) ?>][html]"><?= h($editable) ?></textarea>
          <input type="hidden" id="dirty_<?= $n ?>" name="sec[<?= h($tok) ?>][dirty]" value="0">
        </div>

        <?php if ($hasFaq): ?>
        <div class="sec-panel" data-panel="faq" hidden>
          <p class="help" style="margin:0 0 .6rem;">Each question and answer below becomes an FAQ on the page. Search engines read them too, so the page's FAQ data updates automatically. Blank line = new paragraph. Links: <code>&lt;a href="https://..."&gt;text&lt;/a&gt;</code>.</p>
          <div class="faq-rows">
            <?php foreach ($faq as $it): ?>
              <div class="faq-row">
                <input type="text" name="sec[<?= h($tok) ?>][faq_q][]" value="<?= h($it['q']) ?>" placeholder="Question">
                <textarea name="sec[<?= h($tok) ?>][faq_a][]" rows="3" placeholder="Answer"><?= h(faq_answer_for_textarea($it['a'])) ?></textarea>
                <button type="button" class="mini mini--danger" data-faq-remove>Remove</button>
              </div>
            <?php endforeach; ?>
          </div>
          <button type="button" class="mini" data-faq-add="<?= h($tok) ?>">+ Add a question</button>
        </div>
        <?php endif; ?>

        <?php if ($hasMap): ?>
        <div class="sec-panel" data-panel="map" hidden>
          <p class="help" style="margin:0 0 .6rem;">Type a latitude and longitude (right-click a place in Google Maps and copy the numbers), or paste an embed link. Clear all boxes and save to remove the map.</p>
          <div class="grid-fields">
            <div class="dfield"><label>Place name</label><input type="text" name="sec[<?= h($tok) ?>][map_title]" value="<?= h($map['title']) ?>"></div>
            <div class="dfield"><label>Latitude</label><input type="text" name="sec[<?= h($tok) ?>][map_lat]" value="<?= h($map['lat']) ?>" placeholder="25.2048"></div>
            <div class="dfield"><label>Longitude</label><input type="text" name="sec[<?= h($tok) ?>][map_lon]" value="<?= h($map['lon']) ?>" placeholder="55.2708"></div>
            <div class="dfield"><label>Zoom (3 far, 17 close)</label><input type="number" min="3" max="17" name="sec[<?= h($tok) ?>][map_zoom]" value="<?= h($map['zoom']) ?>"></div>
          </div>
          <div class="dfield"><label>Or a custom embed link (OpenStreetMap or Google Maps "Embed a map" link)</label><input type="text" name="sec[<?= h($tok) ?>][map_custom]" value="<?= h($map['custom']) ?>" placeholder="https://www.google.com/maps/embed?pb=..."></div>
        </div>
        <?php endif; ?>

        <div class="sec-panel" data-panel="design" hidden>
          <div class="scope-note">These settings change <strong>this section on this page only</strong>.<?php if ($tpl_type): ?> To change the design of every “<?= h($tpl_type) ?>” page at once, use <a href="templates.php?t=<?= h($tpl_type) ?>">Templates</a>.<?php endif; ?></div>
          <div class="grid-fields">
            <?php sec_color_field("sec[$tok][bg]", 'Background colour', $st['bg'], "bg_$n"); ?>
            <?php sec_color_field("sec[$tok][ink]", 'Text colour', $st['ink'], "ink_$n"); ?>
            <div class="dfield"><label>Spacing</label>
              <select name="sec[<?= h($tok) ?>][pad]">
                <?php foreach (['' => 'Same as the template', 'sm' => 'Compact', 'md' => 'Normal', 'lg' => 'Spacious'] as $v => $l): ?><option value="<?= $v ?>" <?= $st['pad'] === $v ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?>
              </select></div>
            <div class="dfield"><label>Text alignment</label>
              <select name="sec[<?= h($tok) ?>][align]">
                <?php foreach (['' => 'Same as the template', 'left' => 'Left', 'center' => 'Centred'] as $v => $l): ?><option value="<?= $v ?>" <?= $st['align'] === $v ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?>
              </select></div>
          </div>
          <div class="img-box">
            <div class="img-box__prev"><img data-preview-for="img_<?= $n ?>" src="<?= $media['src'] ? '../' . h(ltrim($media['src'], '/')) : '' ?>" alt="" onerror="this.style.visibility='hidden'"></div>
            <div class="img-box__fields">
              <div class="dfield"><label>Section image</label>
                <div class="dpair">
                  <input type="text" id="img_<?= $n ?>" name="sec[<?= h($tok) ?>][img]" value="<?= h($media['src']) ?>" placeholder="No image">
                  <button type="button" class="mini" onclick="openMedia('img_<?= $n ?>')">Pick / upload</button>
                  <button type="button" class="mini" data-clear="img_<?= $n ?>">Remove</button>
                </div></div>
              <div class="grid-fields">
                <div class="dfield"><label>Describe the image (for accessibility and search)</label><input type="text" name="sec[<?= h($tok) ?>][imgalt]" value="<?= h($media['alt']) ?>"></div>
                <div class="dfield"><label>Image position</label>
                  <select name="sec[<?= h($tok) ?>][imgpos]">
                    <?php foreach (['above' => 'Above the text', 'below' => 'Below the text', 'left' => 'Left of the text', 'right' => 'Right of the text'] as $v => $l): ?><option value="<?= $v ?>" <?= $st['imgpos'] === $v ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?>
                  </select></div>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
<?php }

$pages_all = glob(SITE_ROOT . '/*.html');
$same_type = 0;
if ($tpl_type) foreach ($pages_all as $pf) { if (preg_match('/<body[^>]*data-template="' . preg_quote($tpl_type, '/') . '"/i', file_get_contents($pf))) $same_type++; }
?>
<style>
  .sec-row { border: 1px solid var(--line); border-radius: 10px; background: #fff; margin-bottom: .55rem; }
  .sec-row.is-hidden .sec-row__head { opacity: .6; }
  .sec-row.is-new { border-style: dashed; background: #fafbff; }
  .sec-row.is-deleted { display: none; }
  .sec-row__head { display: flex; align-items: center; gap: .85rem; padding: .75rem .9rem; flex-wrap: wrap; }
  .sec-row__info { flex: 1; min-width: 160px; }
  .sec-row__title { font-weight: 600; font-size: .95rem; }
  .sec-no { display: inline-block; min-width: 1.6rem; color: var(--muted); font-weight: 500; }
  .sec-row__meta { color: var(--muted); font-size: .75rem; }
  .sec-row__actions { display: flex; gap: .3rem; flex-wrap: wrap; }
  .mini { padding: .32rem .6rem; font: inherit; font-size: .78rem; font-weight: 600; background: #fff; border: 1px solid var(--line); border-radius: 6px; cursor: pointer; color: var(--ink); }
  .mini:hover { border-color: var(--blue); color: var(--blue); }
  .mini--danger:hover { border-color: var(--danger); color: var(--danger); }
  .mini.is-on { background: var(--blue); color: #fff; border-color: var(--blue); }
  .newtag { font-style: normal; font-size: .65rem; background: #ede9fe; color: #5b21b6; padding: .08rem .45rem; border-radius: 999px; margin-left: .35rem; text-transform: uppercase; letter-spacing: .04em; }
  .sec-panel { padding: .25rem .9rem 1rem; border-top: 1px solid var(--line); margin-top: .1rem; padding-top: .9rem; }
  .sec-panel[hidden] { display: none; }
  .sec-note { padding: 0 .9rem .8rem; font-size: .8rem; color: var(--muted); }
  .scope-note { background: #eff6ff; border-left: 3px solid var(--blue); border-radius: 6px; padding: .55rem .8rem; font-size: .8rem; color: #1e3a8a; margin-bottom: .9rem; }
  .grid-fields { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: .8rem; margin-bottom: .8rem; }
  .dfield label { display: block; font-size: .75rem; font-weight: 600; color: var(--muted); margin-bottom: .25rem; }
  .dfield input[type=text], .dfield input[type=number], .dfield select { width: 100%; padding: .5rem .6rem; font: inherit; font-size: .85rem; border: 1px solid var(--line); border-radius: 6px; background: #fff; }
  .dpair { display: flex; gap: .4rem; align-items: center; }
  .dpair input[type=color] { width: 38px; height: 34px; padding: 0; border: 1px solid var(--line); border-radius: 6px; background: #fff; cursor: pointer; flex-shrink: 0; }
  .img-box { display: grid; grid-template-columns: 120px 1fr; gap: 1rem; align-items: start; }
  .img-box__prev img { width: 120px; height: 84px; object-fit: cover; border: 1px solid var(--line); border-radius: 8px; background: #f3f4f6; }
  @media (max-width: 640px) { .img-box { grid-template-columns: 1fr; } }
  .faq-row { display: grid; grid-template-columns: 1fr auto; gap: .4rem .6rem; margin-bottom: .75rem; padding: .7rem; border: 1px solid var(--line); border-radius: 8px; background: #fafafa; }
  .faq-row input, .faq-row textarea { width: 100%; padding: .5rem .6rem; font: inherit; font-size: .85rem; border: 1px solid var(--line); border-radius: 6px; }
  .faq-row textarea { grid-column: 1; }
  .faq-row .mini { grid-column: 2; grid-row: 1; align-self: start; }
  .workbar { display: flex; flex-wrap: wrap; align-items: center; gap: .75rem; padding: .75rem 1rem; border-radius: 10px; margin-bottom: 1.25rem; font-size: .875rem; }
  .workbar--live { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
  .workbar--draft { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
  .add-box { display: flex; flex-wrap: wrap; gap: .6rem; align-items: end; padding: .9rem; border: 1px dashed #c7d2fe; border-radius: 10px; background: #fafbff; margin-bottom: 1rem; }
  .add-box .dfield { min-width: 200px; flex: 1; }
  .add-desc { font-size: .75rem; color: var(--muted); flex-basis: 100%; }
</style>

<h2 style="margin-bottom:0.25rem;">Edit page: <?= h($page_name) ?></h2>
<p style="color:#6b7280;margin-top:0;margin-bottom:1rem;">
  <a href="pages.php">← back to pages</a> &nbsp;·&nbsp;
  <a href="../<?= h($file) ?>" target="_blank">view live →</a> &nbsp;·&nbsp;
  <a href="page-icons.php?file=<?= urlencode($file) ?>">Edit icons on this page →</a>
  <?php if ($tpl_type): ?>&nbsp;·&nbsp; <a href="templates.php?t=<?= h($tpl_type) ?>">Shared design for “<?= h($tpl_type) ?>” pages →</a><?php endif; ?>
</p>

<div class="workbar <?= $has_pending ? 'workbar--draft' : 'workbar--live' ?>">
  <?php if ($has_pending): ?>
    <strong>You have unpublished changes.</strong> Visitors still see the old version. Preview, then publish when you are happy.
  <?php else: ?>
    <strong>No unpublished changes.</strong> What you edit below is saved as a draft first. Nothing goes live until you press Publish.
  <?php endif; ?>
</div>

<form method="post" id="page-form">
  <?= csrf_field() ?>
  <input type="hidden" name="page_hash" value="<?= md5($html) ?>">
  <input type="hidden" name="sections_submitted" value="1">

  <div class="card">
    <h3 style="margin-top:0;">SEO (Google)</h3>
    <div class="field">
      <label>Page title (browser tab and Google)</label>
      <input type="text" name="title" value="<?= h($cur_title) ?>" required>
      <div class="help">Aim for under 60 characters. Now: <?= mb_strlen($cur_title) ?></div>
    </div>
    <div class="field">
      <label>Short description (the grey text under the title in Google)</label>
      <textarea name="desc" rows="2" required style="font-family:inherit;font-size:0.9rem;"><?= h($cur_desc) ?></textarea>
      <div class="help">Aim for 140 to 160 characters. Now: <?= mb_strlen($cur_desc) ?></div>
    </div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">Main headline and button</h3>
    <div class="field">
      <label>Big headline at the top of the page</label>
      <textarea name="h1" rows="2" required style="font-family:inherit;font-size:1rem;font-weight:600;"><?= h($cur_h1) ?></textarea>
    </div>
    <div class="field">
      <label>Line of text under the headline</label>
      <textarea name="hero_sub" rows="2"><?= h($cur_sub) ?></textarea>
    </div>
    <div class="row">
      <div class="field"><label>Main button link</label><input type="text" name="cta_url" value="<?= h($cur_cta_url) ?>"></div>
      <div class="field"><label>Words on the main button</label><input type="text" name="cta_label" value="<?= h($cur_cta_label) ?>"></div>
    </div>
    <div class="help">The “Book a Call” button in the top menu is changed in Site Settings.</div>
  </div>

  <div class="card">
    <h3 style="margin-top:0;">Sections</h3>
    <p style="color:#6b7280;font-size:.875rem;margin-bottom:1rem;">Use the switch to show or hide a section. Use the arrows to reorder. <strong>Edit text</strong> changes headings, paragraphs, buttons and links. <strong>Design &amp; image</strong> changes colours, spacing and the picture. Nothing is deleted from the live site until you publish.</p>

    <div class="add-box">
      <div class="dfield"><label>Add a section</label>
        <select id="add-tpl"><?php foreach ($templates as $k => $t): ?><option value="<?= h($k) ?>" data-desc="<?= h($t['desc']) ?>"><?= h($t['label']) ?></option><?php endforeach; ?></select></div>
      <div class="dfield"><label>Where</label>
        <select id="add-where"><option value="end">At the end</option><option value="start">At the very top</option></select></div>
      <button type="button" class="btn" id="add-btn">Add section</button>
      <div class="add-desc" id="add-desc"></div>
    </div>

    <div id="section-list">
      <?php foreach ($split['blocks'] as $i => $b) sec_row('s' . $i, sec_title($b['attrs'], $b['inner']), 'Section ' . ($i + 1), $b); ?>
    </div>
    <?php if (!$split['blocks']): ?><p style="color:#6b7280;">This page has no sections to manage.</p><?php endif; ?>
  </div>

  <div style="display:flex;gap:0.5rem;position:sticky;bottom:0;background:var(--bg);padding:1rem 0;border-top:1px solid var(--line);flex-wrap:wrap;z-index:5;">
    <button type="submit" name="action" value="save" class="btn btn--ghost" style="background:#fff;">Save draft</button>
    <button type="submit" name="action" value="preview" class="btn btn--ghost" style="background:#fff;">Save &amp; preview</button>
    <button type="submit" name="action" value="publish" class="btn" onclick="return confirm('Publish this page? Visitors will see the changes straight away.');">Publish</button>
    <a href="pages.php" class="btn btn--ghost">Cancel</a>
  </div>
</form>

<div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.5rem;">
  <?php if ($has_pending): ?>
    <form method="post" onsubmit="return confirm('Throw away the unpublished changes? The live page stays as it is.');"><?= csrf_field() ?><input type="hidden" name="action" value="discard"><button class="btn btn--ghost" type="submit">Discard draft</button></form>
  <?php endif; ?>
  <?php if ($has_undo): ?>
    <form method="post" onsubmit="return confirm('Go back to the version of this page from before the last publish?');"><?= csrf_field() ?><input type="hidden" name="action" value="undo"><button class="btn btn--ghost" type="submit">↩ Undo last publish</button></form>
  <?php endif; ?>
</div>

<script>
(function () {
  var list = document.getElementById('section-list');
  var form = document.getElementById('page-form');
  var newCount = 0;

  function renumber() {
    var n = 0;
    list.querySelectorAll('.sec-row:not(.is-deleted)').forEach(function (r) { n++; var s = r.querySelector('.sec-no'); if (s) s.textContent = n + '.'; });
  }
  renumber();

  // Open / close panels
  list.addEventListener('click', function (e) {
    var t = e.target.closest('button'); if (!t) return;
    var row = t.closest('.sec-row'); if (!row) return;
    if (t.dataset.open) {
      var panel = row.querySelector('[data-panel="' + t.dataset.open + '"]');
      var opening = panel.hidden;
      row.querySelectorAll('.sec-panel').forEach(function (p) { p.hidden = true; });
      row.querySelectorAll('[data-open]').forEach(function (b) { b.classList.remove('is-on'); });
      if (opening) {
        panel.hidden = false; t.classList.add('is-on');
        if (t.dataset.open === 'content') {
          var ta = panel.querySelector('textarea');
          if (ta && !tinymce.get(ta.id)) initEditor(ta.id, panel.querySelector('input[type=hidden]').id);
        }
      }
    } else if (t.dataset.move) {
      var d = parseInt(t.dataset.move, 10);
      if (d < 0 && row.previousElementSibling) list.insertBefore(row, row.previousElementSibling);
      if (d > 0 && row.nextElementSibling) list.insertBefore(row.nextElementSibling, row);
      renumber();
    } else if (t.hasAttribute('data-delete')) {
      var title = row.querySelector('.sec-row__title').textContent.replace(/^\s*\d+\.\s*/, '').trim();
      if (!confirm('Delete the section “' + title + '” from this page? You can bring the old version back by discarding the draft.')) return;
      row.classList.add('is-deleted');
      row.querySelectorAll('input, textarea, select').forEach(function (i) { i.disabled = true; });
      renumber();
    } else if (t.hasAttribute('data-faq-remove')) {
      t.closest('.faq-row').remove();
    } else if (t.dataset.faqAdd) {
      var tok = t.dataset.faqAdd, wrap = row.querySelector('.faq-rows');
      var d2 = document.createElement('div'); d2.className = 'faq-row';
      d2.innerHTML = '<input type="text" name="sec[' + tok + '][faq_q][]" placeholder="Question"><textarea name="sec[' + tok + '][faq_a][]" rows="3" placeholder="Answer"></textarea><button type="button" class="mini mini--danger" data-faq-remove>Remove</button>';
      wrap.appendChild(d2);
    } else if (t.dataset.clear) {
      var el = document.getElementById(t.dataset.clear); el.value = ''; el.dispatchEvent(new Event('change'));
      var pv = document.querySelector('[data-preview-for="' + t.dataset.clear + '"]'); if (pv) pv.style.visibility = 'hidden';
      var cp = document.querySelector('[data-color-for="' + t.dataset.clear + '"]'); if (cp) cp.value = '#ffffff';
    }
  });

  // Colour pickers stay in step with their text boxes
  list.addEventListener('input', function (e) {
    var cp = e.target.closest('[data-color-for]');
    if (cp) { document.getElementById(cp.dataset.colorFor).value = cp.value; }
  });
  list.addEventListener('input', function (e) {
    if (e.target.matches('.dpair input[type=text][id^="bg_"], .dpair input[type=text][id^="ink_"]') && /^#[0-9a-fA-F]{6}$/.test(e.target.value)) {
      var cp = document.querySelector('[data-color-for="' + e.target.id + '"]'); if (cp) cp.value = e.target.value;
    }
  });

  // Add a section
  var tplSel = document.getElementById('add-tpl'), desc = document.getElementById('add-desc');
  function showDesc() { desc.textContent = tplSel.options[tplSel.selectedIndex].dataset.desc || ''; }
  tplSel.addEventListener('change', showDesc); showDesc();
  document.getElementById('add-btn').addEventListener('click', function () {
    newCount++;
    var tok = 'n' + newCount, label = tplSel.options[tplSel.selectedIndex].text, key = tplSel.value;
    var row = document.createElement('div');
    row.className = 'sec-row is-new'; row.dataset.token = tok;
    row.innerHTML =
      '<input type="hidden" name="order[]" value="' + tok + '"><input type="hidden" name="sec[' + tok + '][tpl]" value="' + key + '">' +
      '<div class="sec-row__head"><label class="switch"><input type="checkbox" name="section_visible[]" value="' + tok + '" checked><span class="slider"></span></label>' +
      '<div class="sec-row__info"><div class="sec-row__title"><span class="sec-no"></span>' + label.replace(/</g, '&lt;') + ' <em class="newtag">new</em></div><div class="sec-row__meta">Created when you save the draft</div></div>' +
      '<div class="sec-row__actions"><button type="button" class="mini" data-move="-1">↑</button><button type="button" class="mini" data-move="1">↓</button><button type="button" class="mini mini--danger" data-delete>Delete</button></div></div>' +
      '<div class="sec-note">It is created with placeholder text when you press <strong>Save draft</strong>. Then use <em>Edit text</em> to write it.</div>';
    if (document.getElementById('add-where').value === 'start') list.insertBefore(row, list.firstChild); else list.appendChild(row);
    renumber();
    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
  });

  function initEditor(id, dirtyId) {
    tinymce.init({
      selector: '#' + id, license_key: 'gpl', height: 420, menubar: false,
      plugins: 'autolink lists link image media table code',
      toolbar: 'undo redo | blocks | bold italic underline | forecolor | alignleft aligncenter alignright | bullist numlist | link image | removeformat | code',
      verify_html: false, convert_urls: false, entity_encoding: 'raw',
      content_css: '../styles/glass.css',
      content_style: 'body{padding:16px;background:#f7f9fc;} .reveal{opacity:1!important;transform:none!important;} section{padding-block:0!important;}',
      branding: false, promotion: false, file_picker_types: 'image',
      file_picker_callback: function (cb) { window._tinyPickerCb = cb; openMediaForTiny(); },
      setup: function (ed) { ed.on('input change undo redo', function () { document.getElementById(dirtyId).value = '1'; }); }
    });
  }
  form.addEventListener('submit', function () { tinymce.triggerSave(); });
})();
</script>

<?php require __DIR__ . '/_footer.php'; ?>
