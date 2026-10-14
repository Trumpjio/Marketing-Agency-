<?php
/**
 * Preview of the unpublished working copy of a page.
 *  preview.php?file=about.html  → toolbar + device-size frame showing the unpublished working copy
 * Login required. Never indexed.
 */
require_once __DIR__ . '/config.php';
require_login();
require_once __DIR__ . '/_pagelib.php';

header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');

$file = basename($_GET['file'] ?? '');
if (!page_path($file)) { http_response_code(404); echo 'Page not found.'; exit; }
$has_pending = page_has_pending($file);

// The page itself is placed in the frame as inline content (srcdoc), so no server framing rules can block it.
$html = page_working_html($file);
// Relative links and images must resolve against the site root, not /admin/.
$html = preg_replace('/<head([^>]*)>/i', '<head$1><base href="../"><meta name="robots" content="noindex,nofollow">', $html, 1);
// Scroll-reveal would hide sections that have not been scrolled to yet.
$html = preg_replace('#</head>#i', '<style>.reveal{opacity:1!important;transform:none!important}</style></head>', $html, 1);
$title = preg_replace('/\s*\|\s*OrcaPulse\s*$/i', '', parse_title(page_working_html($file)));
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Preview: <?= h($title) ?></title>
<style>
  * { box-sizing: border-box; }
  body { margin: 0; font-family: 'Inter', -apple-system, sans-serif; background: #e5e7eb; color: #0a0e27; display: flex; flex-direction: column; height: 100vh; }
  .bar { display: flex; flex-wrap: wrap; gap: .6rem; align-items: center; padding: .6rem 1rem; background: #fff; border-bottom: 1px solid #d1d5db; font-size: .85rem; }
  .bar strong { margin-right: .4rem; }
  .tag { padding: .15rem .6rem; border-radius: 999px; font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
  .tag--draft { background: #fef3c7; color: #92400e; }
  .tag--live { background: #dcfce7; color: #166534; }
  .sizes { display: flex; gap: .25rem; margin-left: auto; margin-right: auto; }
  .sizes button, .bar a { padding: .4rem .8rem; font: inherit; font-size: .8rem; font-weight: 600; background: #fff; border: 1px solid #d1d5db; border-radius: 6px; cursor: pointer; color: #0a0e27; text-decoration: none; }
  .sizes button.is-on { background: #635bff; border-color: #635bff; color: #fff; }
  .bar a.primary { background: #635bff; border-color: #635bff; color: #fff; }
  .stage { flex: 1; overflow: auto; display: flex; justify-content: center; padding: 1rem; }
  iframe { border: 0; background: #fff; height: 100%; width: 100%; max-width: 100%; box-shadow: 0 4px 24px rgba(0,0,0,.15); border-radius: 6px; transition: width .2s; }
</style>
</head>
<body>
  <div class="bar">
    <strong>Preview</strong>
    <span class="tag <?= $has_pending ? 'tag--draft' : 'tag--live' ?>"><?= $has_pending ? 'Unpublished draft' : 'Same as the live page' ?></span>
    <div class="sizes" role="group" aria-label="Screen size">
      <button type="button" data-w="100%" class="is-on">Desktop</button>
      <button type="button" data-w="820px">Tablet</button>
      <button type="button" data-w="390px">Mobile</button>
    </div>
    <a href="page-edit.php?file=<?= urlencode($file) ?>">← Back to editing</a>
    <?php if ($has_pending): ?><a class="primary" href="page-edit.php?file=<?= urlencode($file) ?>#publish">Go to Publish</a><?php endif; ?>
  </div>
  <div class="stage"><iframe id="frame" title="Page preview" srcdoc="<?= htmlspecialchars($html, ENT_QUOTES, 'UTF-8') ?>"></iframe></div>
<script>
  var f = document.getElementById('frame');
  document.querySelectorAll('.sizes button').forEach(function (b) {
    b.addEventListener('click', function () {
      document.querySelectorAll('.sizes button').forEach(function (x) { x.classList.remove('is-on'); });
      b.classList.add('is-on'); f.style.width = b.dataset.w;
    });
  });
</script>
</body>
</html>
