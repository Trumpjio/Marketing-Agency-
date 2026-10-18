const { chromium } = require("playwright");
const fs = require("node:fs");
const path = require("node:path");

const root = process.cwd();
const base = "http://127.0.0.1:8000";
const viewports = [375, 390, 768, 1280, 1440];
const pages = fs.readdirSync(root).filter((name) => name.endsWith(".html")).sort();
const failures = [];
const issues = [];

function fail(label, detail) {
  failures.push({ label, detail });
}
function recordIssue(label, detail) {
  issues.push({ label, detail });
}

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();
  page.setDefaultNavigationTimeout(12000);
  let currentErrors = [];
  let currentLocalResponses = [];

  page.on("pageerror", (error) => currentErrors.push(error.message));
  page.on("response", (response) => {
    if (response.url().startsWith(base) && response.status() >= 400) {
      currentLocalResponses.push(response.status() + " " + response.url());
    }
  });

  for (const width of viewports) {
    await page.setViewportSize({ width, height: 900 });
    for (const file of pages) {
      currentErrors = [];
      currentLocalResponses = [];
      const url = base + "/" + encodeURI(file);
      try {
        const response = await page.goto(url, { waitUntil: "domcontentloaded" });
        await page.waitForTimeout(45);
        if (!response || response.status() >= 400) {
          fail("page response", width + "px " + file + ": " + (response ? response.status() : "no response"));
          continue;
        }

        const result = await page.evaluate(() => {
          function isInsideClippingContainer(el) {
            let parent = el.parentElement;
            while (parent && parent !== document.body) {
              const style = getComputedStyle(parent);
              if (/(hidden|clip|auto|scroll)/.test(style.overflowX)) return true;
              parent = parent.parentElement;
            }
            return false;
          }
          const overflow = [];
          for (const el of document.querySelectorAll("body *")) {
            if (el.closest('[aria-hidden="true"]') || el.hidden || el.classList.contains("skip-link")) continue;
            const style = getComputedStyle(el);
            if (style.display === "none" || style.visibility === "hidden" || style.position === "fixed" && el.id !== "nav") continue;
            const rect = el.getBoundingClientRect();
            if (rect.width < 1 || rect.height < 1) continue;
            if ((rect.left < -2 || rect.right > window.innerWidth + 2) && !isInsideClippingContainer(el)) {
              overflow.push({
                tag: el.tagName.toLowerCase(),
                className: typeof el.className === "string" ? el.className.slice(0, 90) : "",
                left: Math.round(rect.left),
                right: Math.round(rect.right),
                width: Math.round(rect.width)
              });
            }
          }
          const emptyMarkers = Array.from(document.querySelectorAll('[data-op-empty="true"]')).filter((el) => {
            const section = el.closest("section") || el;
            return !section.hidden && getComputedStyle(section).display !== "none";
          }).length;
          const main = document.querySelector("main");
          const h1 = main ? main.querySelectorAll("h1").length : 0;
          return { overflow: overflow.slice(0, 8), emptyMarkers, h1 };
        });

        if (result.overflow.length) {
          fail("horizontal overflow", width + "px " + file + ": " + JSON.stringify(result.overflow));
        }
        if (result.emptyMarkers) {
          fail("empty section visible", width + "px " + file + ": " + result.emptyMarkers + " marked empty elements remain visible");
        }
        if (result.h1 !== 1) {
          recordIssue("main heading count", width + "px " + file + ": expected 1 main H1, found " + result.h1);
        }
        if (currentErrors.length) {
          fail("browser JavaScript error", width + "px " + file + ": " + currentErrors.join(" | "));
        }
        if (currentLocalResponses.length) {
          fail("local asset/request error", width + "px " + file + ": " + currentLocalResponses.join(" | "));
        }
      } catch (error) {
        fail("navigation/test exception", width + "px " + file + ": " + error.message);
      }
    }
  }

  // Exercise mobile navigation and accordion behavior on the homepage.
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto(base + "/index.html", { waitUntil: "domcontentloaded" });
  const toggle = page.locator("#mobile-toggle");
  const mobileMenu = page.locator("#mobile-menu");
  if (await toggle.count()) {
    await toggle.click();
    if (!(await mobileMenu.evaluate((el) => el.classList.contains("is-open") && el.getAttribute("aria-hidden") === "false"))) {
      fail("mobile navigation", "menu did not open or update aria-hidden");
    }
    const group = mobileMenu.locator(".nav__mobile-heading").first();
    if (await group.count()) {
      await group.click();
      if ((await group.getAttribute("aria-expanded")) !== "true") {
        fail("mobile accordion", "group did not set aria-expanded=true after activation");
      }
      await group.press("Escape");
    } else {
      await page.keyboard.press("Escape");
    }
    if (await mobileMenu.evaluate((el) => el.classList.contains("is-open"))) {
      fail("mobile navigation", "Escape did not close the panel");
    }
  } else {
    fail("mobile navigation", "#mobile-toggle was not found");
  }

  // Verify the public form is available on Contact Us only and works through
  // the same browser-facing asynchronous path visitors use.
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto(base + "/index.html", { waitUntil: "domcontentloaded" });
  const homeFormCount = await page.locator("form").count();
  if (homeFormCount !== 0) fail("form placement", "homepage must be booking-only; found " + homeFormCount + " form(s)");

  await page.goto(base + "/contact.html#contact-form-section", { waitUntil: "domcontentloaded" });
  const contactForms = page.locator("form");
  if (await contactForms.count() !== 1) {
    fail("form placement", "Contact Us must contain exactly one public form; found " + await contactForms.count());
  } else {
    const form = page.locator("#lead-form");
    if (!(await form.isVisible())) fail("Contact Us form", "lead form is not visible");
    await page.locator("#lead-name").fill("Browser Test");
    await page.locator("#lead-email").fill("browser-test@example.com");
    await page.locator("#lead-company").fill("Validation");
    await page.locator("#lead-website").fill("https://example.com");
    await page.locator("#lead-goals").fill("Verify Contact Us form submission and accessible feedback.");
    await page.locator("#lead-form button[type=submit]").click();
    try {
      await page.locator("#lead-feedback.is-success").waitFor({ state: "visible", timeout: 8000 });
    } catch {
      fail("Contact Us form", "successful browser submission did not display the success feedback");
    }
    if (!(await page.locator("#lead-feedback").innerText()).includes("received")) {
      fail("Contact Us form", "success message text was missing or unexpected");
    }
  }

  // Exercise the public form endpoint in this disposable CI checkout.
  const postLead = async (fields) => page.request.post(base + "/admin/lead.php", {
    form: fields,
    headers: { Accept: "application/json" }
  });
  try {
    const invalid = await postLead({ name: "CI Test", email: "invalid", company: "", goals: "", source: "ci" });
    if (invalid.status() !== 422) fail("lead form validation", "expected 422 for invalid fields, got " + invalid.status());

    const goodFields = { name: "CI Test", email: "qa@example.com", company: "Validation", website: "", goals: "Automated workflow test", source: "ci" };
    const success = await postLead(goodFields);
    const successData = await success.json().catch(() => ({}));
    if (success.status() !== 200 || !successData.ok) fail("lead form submission", "expected successful JSON response, got " + success.status() + " " + JSON.stringify(successData));

    // One successful browser form submission has already consumed one hit.
    for (let i = 0; i < 7; i++) {
      const response = await postLead({ ...goodFields, email: "qa+" + i + "@example.com" });
      if (response.status() !== 200) fail("lead form rate-limit setup", "request " + (i + 1) + " returned " + response.status());
      await response.body();
    }
    const limited = await postLead({ ...goodFields, email: "qa-limit@example.com" });
    if (limited.status() !== 429) fail("lead form rate limiting", "expected 429 after 10 requests, got " + limited.status());
    await limited.body();
  } catch (error) {
    fail("lead form API", error.message);
  } finally {
    // CI uses a clean checkout; delete only the temporary data written by this test.
    fs.rmSync(path.join(root, "data", "leads"), { recursive: true, force: true });
    fs.rmSync(path.join(root, "data", ".rate_lead.json"), { force: true });
  }

  // Capture real rendered views for design review; these are CI artifacts,
  // not production screenshots. Capture the hero, mid-page and final content.
  const auditRoutes = [
    "index.html",
    "services.html",
    "service-paid-media.html",
    "industry-real-estate.html",
    "real-estate-marketing-dubai.html",
    "about.html",
    "work.html",
    "insights.html",
    "contact.html"
  ];
  const screenshotDir = path.join(root, "artifacts", "design-audit");
  const layoutDiagnostics = [];
  fs.mkdirSync(screenshotDir, { recursive: true });
  for (const width of [390, 768, 834, 1024, 1440]) {
    await page.setViewportSize({ width, height: width === 390 ? 844 : width === 768 ? 900 : 1000 });
    // Phase 1 requires extra tablet/laptop screenshots for the homepage only.
    // Keep other page types on the established audit widths to avoid expanding scope.
    const routesForWidth = [834, 1024].includes(width) ? ["index.html"] : auditRoutes;
    for (const file of routesForWidth) {
      const shortName = file.replace(/\.html$/, "");
      try {
        await page.goto(base + "/" + encodeURI(file), { waitUntil: "networkidle" });
        await page.evaluate(() => document.fonts && document.fonts.ready);
        if (["index.html", "services.html", "service-paid-media.html", "industry-real-estate.html", "real-estate-marketing-dubai.html", "work.html", "insights.html", "contact.html"].includes(file) && ["index.html", "services.html", "industry-real-estate.html"].includes(file)) {
          const diagnostics = await page.evaluate(() => {
            const select = (selector) => {
              const el = document.querySelector(selector);
              if (!el) return null;
              const style = getComputedStyle(el);
              const rect = el.getBoundingClientRect();
              return {
                selector,
                top: Math.round(rect.top),
                bottom: Math.round(rect.bottom),
                left: Math.round(rect.left),
                right: Math.round(rect.right),
                width: Math.round(rect.width),
                height: Math.round(rect.height),
                position: style.position,
                paddingTop: style.paddingTop,
                paddingBottom: style.paddingBottom,
                marginTop: style.marginTop,
                fontSize: style.fontSize,
                lineHeight: style.lineHeight,
                fontFamily: style.fontFamily,
                color: style.color,
                background: style.backgroundColor,
                minHeight: style.minHeight,
                borderRadius: style.borderRadius
              };
            };
            return {
              viewport: { width: innerWidth, height: innerHeight, scrollY: Math.round(scrollY) },
              document: { height: document.documentElement.scrollHeight, bodyHeight: document.body.scrollHeight },
              header: select("header.nav"),
              navInner: select(".nav__inner"),
              hero: select("main > section.hero"),
              h1: select("main > section.hero h1"),
              heroSub: select("main > section.hero .hero__sub"),
              heroAction: select("main > section.hero .hero__actions a"),
              scarcity: select("main > section.hero .hero__scarcity"),
              growthVisual: select(".OrcaPulse-home-visual"),
              footerInner: select(".footer__inner"),
              footerCols: select(".footer__cols")
            };
          });
          layoutDiagnostics.push({ page: file, width, diagnostics });
          const d = diagnostics;
          const top = d.navInner && d.navInner.bottom;
          if (d.h1 && typeof top === "number" && d.h1.top < top + 10) {
            fail("desktop header/hero collision", width + "px " + file + ": H1 begins at y=" + d.h1.top + " while navigation ends at y=" + top);
          }
          if (width >= 1100 && d.heroAction && d.heroAction.width > 480) {
            fail("oversized hero CTA", width + "px " + file + ": primary hero CTA is " + d.heroAction.width + "px wide");
          }
          if (width >= 1100 && d.footerInner && d.footerCols && d.footerCols.width < d.footerInner.width * 0.55) {
            fail("compressed desktop footer", width + "px " + file + ": footer links occupy " + d.footerCols.width + "px of " + d.footerInner.width + "px footer width");
          }
          if (width >= 1100 && !d.heroAction && d.hero && d.hero.height > 560) {
            fail("excessive text-only hero height", width + "px " + file + ": hero reserves " + d.hero.height + "px despite having no primary hero CTA");
          }
        }
        const maxScroll = await page.evaluate(() => Math.max(0, document.documentElement.scrollHeight - window.innerHeight));
        const points = [
          ["hero", 0],
          ["middle", Math.round(maxScroll * 0.48)],
          ["end", maxScroll]
        ];
        for (const [label, y] of points) {
          await page.evaluate((scrollY) => window.scrollTo(0, scrollY), y);
          await page.waitForTimeout(180);
          const filename = shortName + "-" + width + "-" + label + ".png";
          await page.screenshot({ path: path.join(screenshotDir, filename), fullPage: false });
        }
        // Full-page desktop overview for the main pages so the vertical rhythm
        // can be inspected in one image where the source page is not extreme.
        if (width === 1440 && ["index.html", "services.html", "service-paid-media.html", "industry-real-estate.html", "work.html", "insights.html"].includes(file)) {
          const maxHeight = await page.evaluate(() => document.documentElement.scrollHeight);
          if (maxHeight <= 9500) {
            await page.evaluate(() => window.scrollTo(0, 0));
            await page.screenshot({ path: path.join(screenshotDir, shortName + "-1440-full.png"), fullPage: true });
          }
        }
      } catch (error) {
        recordIssue("design screenshot failed", file + " at " + width + "px: " + error.message);
      }
    }
  }

  fs.writeFileSync(path.join(screenshotDir, "layout-diagnostics.json"), JSON.stringify(layoutDiagnostics, null, 2));
  await browser.close();

  console.log("Saved computed layout diagnostics for " + layoutDiagnostics.length + " route/viewport combinations.");
  console.log("Responsive viewport sweep: " + pages.length + " pages × " + viewports.length + " widths = " + (pages.length * viewports.length) + " checks.");
  console.log("Additional checks: mobile nav/accordion, form success/validation/rate-limit, JS errors, local request errors.");
  for (const issue of issues) console.log("REVIEW: " + issue.label + " — " + issue.detail);
  if (failures.length) {
    console.error("RESPONSIVE SMOKE TEST FAILED: " + failures.length + " failure(s).");
    for (const failure of failures) console.error("FAIL: " + failure.label + " — " + failure.detail);
    process.exit(1);
  }
  console.log("Responsive smoke test passed with " + issues.length + " review note(s).");
})().catch((error) => {
  console.error(error);
  process.exit(1);
});
