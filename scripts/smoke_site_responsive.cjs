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
            if (el.closest('[aria-hidden="true"]') || el.hidden) continue;
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
          recordIssue("potential horizontal overflow", width + "px " + file + ": " + JSON.stringify(result.overflow));
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

  // Exercise the public form endpoint in this disposable CI checkout.
  const postLead = async (fields) => page.request.post(base + "/admin/lead.php", {
    data: new URLSearchParams(fields),
    headers: { Accept: "application/json" }
  });
  try {
    const invalid = await postLead({ name: "CI Test", email: "invalid", company: "", goals: "", source: "ci" });
    if (invalid.status() !== 422) fail("lead form validation", "expected 422 for invalid fields, got " + invalid.status());

    const goodFields = { name: "CI Test", email: "qa@example.com", company: "Validation", website: "", goals: "Automated workflow test", source: "ci" };
    const success = await postLead(goodFields);
    const successData = await success.json().catch(() => ({}));
    if (success.status() !== 200 || !successData.ok) fail("lead form submission", "expected successful JSON response, got " + success.status() + " " + JSON.stringify(successData));

    for (let i = 0; i < 8; i++) {
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

  await browser.close();

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
