// OrcaPulse — universal site scripts (v2 — desktop + mobile nav)
(function () {
  "use strict";

  // ---- Scroll restoration ----
  if ("scrollRestoration" in history) history.scrollRestoration = "manual";
  if (!window.location.hash) window.scrollTo(0, 0);

  // ---- Sticky nav shrink on scroll ----
  const nav = document.querySelector(".nav");
  const onScroll = () => {
    if (!nav) return;
    if (window.scrollY > 10) nav.classList.add("is-scrolled");
    else nav.classList.remove("is-scrolled");
  };
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  // ---- DESKTOP: dropdown triggers (click OR hover) ----
  const dropdownItems = document.querySelectorAll(".nav__item");
  dropdownItems.forEach((item) => {
    const trigger = item.querySelector(".nav__trigger");
    if (!trigger) return;

    trigger.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      const wasOpen = item.classList.contains("is-open");
      // Close all other dropdowns
      dropdownItems.forEach((other) => {
        other.classList.remove("is-open");
        const t = other.querySelector(".nav__trigger");
        if (t) t.setAttribute("aria-expanded", "false");
      });
      if (!wasOpen) {
        item.classList.add("is-open");
        trigger.setAttribute("aria-expanded", "true");
      }
    });
  });

  // Click outside closes dropdowns
  document.addEventListener("click", (e) => {
    if (!e.target.closest(".nav__item")) {
      dropdownItems.forEach((item) => {
        item.classList.remove("is-open");
        const t = item.querySelector(".nav__trigger");
        if (t) t.setAttribute("aria-expanded", "false");
      });
    }
  });

  // Escape closes dropdowns
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      dropdownItems.forEach((item) => {
        item.classList.remove("is-open");
        const t = item.querySelector(".nav__trigger");
        if (t) t.setAttribute("aria-expanded", "false");
      });
      closeMobilePanel();
    }
  });

  // ---- MOBILE: burger + panel ----
  const burger = document.getElementById("mobile-toggle");
  const panel = document.getElementById("mobile-menu");

  function openMobilePanel() {
    if (!burger || !panel) return;
    panel.classList.add("is-open");
    panel.setAttribute("aria-hidden", "false");
    burger.setAttribute("aria-expanded", "true");
    document.body.classList.add("is-nav-open");
  }
  function closeMobilePanel() {
    if (!burger || !panel) return;
    panel.classList.remove("is-open");
    panel.setAttribute("aria-hidden", "true");
    burger.setAttribute("aria-expanded", "false");
    document.body.classList.remove("is-nav-open");
  }
  if (burger && panel) {
    burger.addEventListener("click", (e) => {
      e.stopPropagation();
      if (panel.classList.contains("is-open")) closeMobilePanel();
      else openMobilePanel();
    });
    // Enhance sections into accordions (wrap <a> siblings of heading into .items div)
    panel.querySelectorAll(".nav__mobile-section").forEach((section) => {
      const heading = section.querySelector(".nav__mobile-heading");
      if (!heading || section.dataset.enhanced) return;
      section.dataset.enhanced = "1";
      // Collect sibling <a> elements after the heading
      const items = Array.from(section.children).filter((n) => n !== heading && n.tagName === "A");
      if (items.length === 0) return;
      const wrap = document.createElement("div");
      wrap.className = "nav__mobile-section__items";
      items.forEach((it) => wrap.appendChild(it));
      section.appendChild(wrap);
      // Click heading to toggle
      heading.addEventListener("click", (e) => {
        e.stopPropagation();
        section.classList.toggle("is-open");
      });
    });
    // Close panel when a direct link is clicked (not the heading)
    panel.addEventListener("click", (e) => {
      if (e.target.tagName === "A") closeMobilePanel();
    });
    // Close when resizing to desktop
    window.addEventListener("resize", () => {
      if (window.innerWidth >= 901) closeMobilePanel();
    });
  }

  // ---- Reveal-on-scroll animations ----
  const revealEls = document.querySelectorAll(".reveal");
  if ("IntersectionObserver" in window) {
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.1, rootMargin: "0px 0px -8% 0px" }
    );
    revealEls.forEach((el) => io.observe(el));
  } else {
    revealEls.forEach((el) => el.classList.add("is-visible"));
  }

  // ---- Smooth anchor scroll ----
  document.querySelectorAll('a[href^="#"]').forEach((a) => {
    a.addEventListener("click", (e) => {
      const id = a.getAttribute("href");
      if (!id || id === "#") return;
      const el = document.querySelector(id);
      if (!el) return;
      e.preventDefault();
      el.scrollIntoView({ behavior: "smooth", block: "start" });
    });
  });

  // ---- Scroll progress bar ----
  const bar = document.createElement("div");
  bar.style.cssText = "position:fixed;top:0;left:0;height:2px;width:0;background:linear-gradient(90deg,#635bff,#18bfff);z-index:200;transition:width 0.1s linear;pointer-events:none;";
  document.body.appendChild(bar);
  window.addEventListener("scroll", () => {
    const h = document.documentElement;
    const scrolled = (h.scrollTop / (h.scrollHeight - h.clientHeight)) * 100;
    bar.style.width = scrolled + "%";
  }, { passive: true });


  /* ==========================================================================
     ORCAPULSE GUIDE — floating animated face + context-aware hints
     --------------------------------------------------------------------------
     - SVG face: eyes blink and change expression; mouth animates between
       smile shapes; occasional joyful jump on help.
     - Positions bottom-right, flips to left to avoid floating CTAs or forms.
     - On scroll-stop ≥2s, if the current section has a hint, shows a tooltip.
     - Click-to-help works immediately.
     - Dismissed hints don't reappear this session. Disabling it is permanent
       (localStorage).
     ========================================================================== */
  (function initGuide() {
    const REDUCED_MOTION = matchMedia("(prefers-reduced-motion: reduce)").matches;
    const LS_DISABLED = "op-guide:disabled";
    const SS_DISMISSED = "op-guide:dismissed";

    // Config from scripts/custom-config.js (set by the admin Design panel).
    const CFG = window.OP_GUIDE || {};
    // Back-compat with the old simple flag
    if (window.OP_GUIDE_ENABLED === false) return;
    if (CFG.enabled === false) return;
    if (CFG.where === "off") return;
    if (localStorage.getItem(LS_DISABLED) === "1") return;

    // Where-rule: show only on home / insights / everywhere.
    const path = (location.pathname || "").toLowerCase().split("/").pop() || "index.html";
    const isHome = (path === "" || path === "index.html");
    const isInsight = (path === "insights.html" || path.startsWith("insight-"));
    if (CFG.where === "home" && !isHome) return;
    if (CFG.where === "insights" && !isInsight) return;

    const HINTS_ENABLED = CFG.hintsEnabled !== false; // default true
    const TRIGGER = CFG.trigger || "inactivity";       // inactivity | click | error

    // ---------- Section hints: use data-face-hint attribute, else derive from h2.
    function hintFor(section) {
      const explicit = section.getAttribute("data-face-hint");
      if (explicit) return { kicker: "This section", body: explicit };
      const h2 = section.querySelector("h2, h1");
      if (!h2) return null;
      const text = h2.textContent.trim().replace(/\s+/g, " ").replace(/\.$/, "");
      if (!text || text.length < 6) return null;
      return { kicker: "On this section", body: `About: “${text}”. Click to explore related reading, or scroll for the detail.` };
    }

    // ---------- Build the face.
    // Note: avatar JSON gives us head + eyes but no mouth; we add our own.
    const face = document.createElement("button");
    face.className = "op-guide__face";
    face.type = "button";
    face.setAttribute("aria-label", "OrcaPulse guide — click for a hint about this section");
    const faceColor = CFG.faceColor || "#F8F7F2";
    const eyeColor  = CFG.eyeColor  || "#111316";
    face.style.background = faceColor;
    // Apply configured size: sm | md | lg
    const sizeMap = { sm: 36, md: 48, lg: 60 };
    const px = sizeMap[CFG.size] || sizeMap.md;
    face.style.width = px + "px";
    face.style.height = px + "px";
    const showTail = CFG.tail !== false;
    face.innerHTML = `
      <svg viewBox="0 0 100 100" aria-hidden="true" style="overflow:visible">
        ${showTail ? `<path class="op-guide__tail" d="M 50 95 L 44 108 L 56 108 Z" fill="${faceColor}" stroke="rgba(0,0,0,0.08)" stroke-width="1" />` : ''}
        <circle class="op-guide__cheek" cx="26" cy="62" r="7"/>
        <circle class="op-guide__cheek" cx="74" cy="62" r="7"/>
        <ellipse class="op-guide__eye op-guide__eye--l" cx="37" cy="46" rx="5" ry="9" fill="${eyeColor}"/>
        <ellipse class="op-guide__eye op-guide__eye--r" cx="63" cy="46" rx="5" ry="9" fill="${eyeColor}"/>
        <path class="op-guide__mouth" d="M 36 68 Q 50 76 64 68" fill="none" stroke="${eyeColor}" stroke-width="3" stroke-linecap="round"/>
      </svg>`;

    const tip = document.createElement("div");
    tip.className = "op-guide__tip";
    tip.setAttribute("data-style", CFG.tipStyle || "card");
    tip.setAttribute("role", "status");
    tip.setAttribute("aria-live", "polite");
    tip.setAttribute("data-open", "false");
    tip.innerHTML = `
      <div class="op-guide__tip-kicker"></div>
      <div class="op-guide__tip-body"></div>
      <div class="op-guide__tip-actions">
        <button class="op-guide__tip-disable" type="button">Don't show these</button>
        <button class="op-guide__tip-close" type="button" aria-label="Close hint">Close</button>
      </div>`;

    const guide = document.createElement("div");
    guide.className = "op-guide";
    guide.setAttribute("data-side", "right");
    guide.setAttribute("data-anchor", "br");
    // Wrap the face + tip so the idle float animation can run on the wrap
    // (otherwise it would fight the anchor transition on the outer element).
    const wrap = document.createElement("div");
    wrap.className = "op-guide__wrap";
    wrap.appendChild(tip);
    wrap.appendChild(face);
    guide.appendChild(wrap);
    document.body.appendChild(guide);

    const eyeL = face.querySelector(".op-guide__eye--l");
    const eyeR = face.querySelector(".op-guide__eye--r");
    const mouth = face.querySelector(".op-guide__mouth");
    const tipKicker = tip.querySelector(".op-guide__tip-kicker");
    const tipBody = tip.querySelector(".op-guide__tip-body");
    const btnClose = tip.querySelector(".op-guide__tip-close");
    const btnDisable = tip.querySelector(".op-guide__tip-disable");

    // ---------- Expression setters.
    const EXPR = {
      neutral:  { eye: { rx: 5,   ry: 9  }, mouth: "M 36 68 Q 50 76 64 68" },
      happy:    { eye: { rx: 5.5, ry: 6  }, mouth: "M 32 65 Q 50 82 68 65" },  // bigger smile, happy eyes
      excited:  { eye: { rx: 6,   ry: 10 }, mouth: "M 32 64 Q 50 86 68 64" },  // open smile
      curious:  { eye: { rx: 5,   ry: 9  }, mouth: "M 38 70 Q 50 72 62 70" },  // small smile
      playful:  { eye: { rx: 5,   ry: 8  }, mouth: "M 34 66 Q 50 80 66 66" },
    };
    function setExpression(name) {
      const e = EXPR[name] || EXPR.neutral;
      eyeL.setAttribute("rx", e.eye.rx); eyeL.setAttribute("ry", e.eye.ry);
      eyeR.setAttribute("rx", e.eye.rx); eyeR.setAttribute("ry", e.eye.ry);
      mouth.setAttribute("d", e.mouth);
    }
    setExpression("neutral");

    // ---------- Blink loop (irregular, polite).
    function blink() {
      if (REDUCED_MOTION) return;
      const prevRy = eyeL.getAttribute("ry");
      eyeL.setAttribute("ry", 1.5); eyeR.setAttribute("ry", 1.5);
      setTimeout(() => { eyeL.setAttribute("ry", prevRy); eyeR.setAttribute("ry", prevRy); }, 140);
    }
    function scheduleBlink() {
      const wait = 2800 + Math.random() * 4200;
      setTimeout(() => { blink(); scheduleBlink(); }, wait);
    }
    setTimeout(scheduleBlink, 1200);

    // ---------- Jump.
    function jump(expression) {
      if (REDUCED_MOTION) return;
      setExpression(expression || "excited");
      face.setAttribute("data-anim", "jump");
      setTimeout(() => { face.removeAttribute("data-anim"); setExpression("neutral"); }, 900);
    }

    // ---------- Tooltip open/close.
    let openSection = null;
    function openTip(hint, section) {
      tipKicker.textContent = hint.kicker;
      tipBody.textContent = hint.body;
      tip.setAttribute("data-open", "true");
      openSection = section;
      jump("happy");
    }
    function closeTip() {
      tip.setAttribute("data-open", "false");
      openSection = null;
    }

    btnClose.addEventListener("click", () => {
      if (openSection) {
        const dismissed = JSON.parse(sessionStorage.getItem(SS_DISMISSED) || "[]");
        const id = sectionId(openSection);
        if (id && !dismissed.includes(id)) {
          dismissed.push(id);
          sessionStorage.setItem(SS_DISMISSED, JSON.stringify(dismissed));
        }
      }
      closeTip();
    });
    btnDisable.addEventListener("click", () => {
      localStorage.setItem(LS_DISABLED, "1");
      guide.setAttribute("data-hidden", "true");
    });

    // ---------- Click face: open help on current section immediately.
    face.addEventListener("click", (e) => {
      e.stopPropagation();
      if (tip.getAttribute("data-open") === "true") { closeTip(); return; }
      const section = currentSection();
      if (!section) { jump("playful"); return; }
      const h = hintFor(section);
      if (!h) { jump("playful"); return; }
      openTip(h, section);
    });

    // ---------- Keyboard access on tooltip.
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && tip.getAttribute("data-open") === "true") closeTip();
    });

    // ---------- Section detection: find the section whose midpoint is nearest viewport midpoint.
    function sectionId(section) {
      const h = section.querySelector("h2, h1");
      if (h) return (section.id || "s:") + (h.textContent.trim().slice(0, 80));
      return section.id || null;
    }
    function allSections() {
      return Array.from(document.querySelectorAll("section"));
    }
    // Return the nearest section-in-viewport that actually has a hint.
    // Falls back to the nearest hint-bearing section above the viewport if
    // the one in view (e.g. a bare stat tile row) has no h2.
    function currentSection() {
      const sections = allSections();
      const vpMid = window.scrollY + window.innerHeight / 2;
      // candidates: sections overlapping viewport AND with a hint
      let best = null, bestDist = Infinity;
      for (const s of sections) {
        const r = s.getBoundingClientRect();
        if (r.bottom < 0 || r.top > window.innerHeight) continue;
        if (!hintFor(s)) continue;
        const mid = window.scrollY + r.top + r.height / 2;
        const d = Math.abs(vpMid - mid);
        if (d < bestDist) { bestDist = d; best = s; }
      }
      if (best) return best;
      // Fallback: nearest hint-bearing section above the viewport
      let fallback = null, fallbackTop = -Infinity;
      for (const s of sections) {
        if (!hintFor(s)) continue;
        const r = s.getBoundingClientRect();
        const absTop = window.scrollY + r.top;
        if (absTop <= vpMid && absTop > fallbackTop) {
          fallbackTop = absTop; fallback = s;
        }
      }
      return fallback;
    }

    // ---------- Positioning: the face actually wanders across the screen.
    //
    // 6 anchors: top-right, top-left, mid-right, mid-left, bottom-right, bottom-left.
    // On each scroll-stop, pick the anchor assigned to the current section (index
    // mod 6 — produces a satisfying left/right/up/down rhythm as the viewer
    // scrolls down the page). If that anchor is heavily blocked by content,
    // fall through the fallback list until we find a usable one.
    const EDGE = 20;
    const MOBILE_EDGE = 14;
    const ANCHOR_ROTATION = ["tr", "ml", "br", "tl", "mr", "bl"];
    const MOBILE_ROTATION = ["br", "bl", "tr", "tl"]; // keep face away from mid-screen on narrow

    function anchorStyle(anchor) {
      const edge = window.innerWidth < 560 ? MOBILE_EDGE : EDGE;
      // Top anchors sit below the sticky nav (~70px tall) with a bit of breathing room
      const topY = (window.innerWidth < 560 ? 72 : 84) + "px";
      const styles = {
        tr: { top: topY,     bottom: "auto",      left: "auto",      right: edge + "px" },
        tl: { top: topY,     bottom: "auto",      left: edge + "px", right: "auto"      },
        mr: { top: "50%",    bottom: "auto",      left: "auto",      right: edge + "px" },
        ml: { top: "50%",    bottom: "auto",      left: edge + "px", right: "auto"      },
        br: { top: "auto",   bottom: edge + "px", left: "auto",      right: edge + "px" },
        bl: { top: "auto",   bottom: edge + "px", left: edge + "px", right: "auto"      },
      };
      return styles[anchor] || styles.br;
    }
    function anchorSide(anchor) {
      return (anchor === "bl" || anchor === "ml" || anchor === "tl") ? "left" : "right";
    }

    // How blocked is a given 220×220 probe region at (xCenter, yCenter)?
    // Lower is better. Scores: 0 = fully clear, up to ~10.
    function blockage(xCenter, yCenter) {
      const PROBE = 90; // half-size
      let hits = 0;
      // Sample 5 points: centre + 4 corners
      const pts = [
        [xCenter, yCenter],
        [xCenter - PROBE, yCenter - PROBE], [xCenter + PROBE, yCenter - PROBE],
        [xCenter - PROBE, yCenter + PROBE], [xCenter + PROBE, yCenter + PROBE],
      ];
      for (const [x, y] of pts) {
        if (x < 0 || y < 0 || x > innerWidth || y > innerHeight) { hits += 2; continue; }
        const els = document.elementsFromPoint(x, y);
        const hit = els.some(el =>
          el !== face && el !== tip && el !== guide && el !== wrap && !guide.contains(el) &&
          (el.closest(".cta") || el.closest(".cta-block") || el.closest("form") ||
           el.closest(".nav__mobile-panel.is-open") || el.closest("button") ||
           el.matches("h1, h2, img") || el.closest(".hero__actions") ||
           el.closest(".btn") || el.closest("input") || el.closest("textarea") ||
           el.closest("[role=dialog]"))
        );
        if (hit) hits++;
      }
      return hits;
    }

    // Returns the index of the current section among all sections.
    function sectionIndex() {
      const sections = allSections();
      const cur = currentSection();
      if (!cur) {
        // No section yet — base position on scroll depth (0..5 bands)
        const d = Math.floor((window.scrollY / Math.max(1, document.documentElement.scrollHeight - innerHeight)) * 6);
        return Math.max(0, Math.min(5, d));
      }
      return sections.indexOf(cur);
    }

    function centersFor(vw, vh, edge) {
      // Centres of the 220×220 probe for each anchor
      const topY = (vw < 560 ? 72 : 84) + 90;
      return {
        tr: [vw - edge - 110, topY],
        tl: [edge + 110,      topY],
        mr: [vw - edge - 110, vh / 2],
        ml: [edge + 110,      vh / 2],
        br: [vw - edge - 110, vh - edge - 110],
        bl: [edge + 110,      vh - edge - 110],
      };
    }

    function reposition() {
      const vw = window.innerWidth, vh = window.innerHeight;
      const edge = vw < 560 ? MOBILE_EDGE : EDGE;
      const rotation = vw < 560 ? MOBILE_ROTATION : ANCHOR_ROTATION;
      const idx = sectionIndex();
      // Preferred anchor by section index (deterministic rhythm). We rotate
      // the fallback order starting from the preferred one.
      const preferred = rotation[((idx % rotation.length) + rotation.length) % rotation.length];
      const ordered = [preferred];
      for (const a of rotation) if (a !== preferred) ordered.push(a);
      const centres = centersFor(vw, vh, edge);
      // Try the preferred first; if its blockage is > 1 (any real overlap),
      // walk the rotation and pick the first with blockage <= 1 (clearly clear).
      // If none is clear, pick the least-blocked one in the rotation.
      let chosen = ordered[0], chosenScore = blockage(...centres[chosen]);
      if (chosenScore > 1) {
        let bestFallback = chosen, bestScore = chosenScore;
        for (let i = 1; i < ordered.length; i++) {
          const a = ordered[i];
          const s = blockage(...centres[a]);
          if (s <= 1) { chosen = a; chosenScore = s; break; }
          if (s < bestScore) { bestScore = s; bestFallback = a; }
        }
        if (chosenScore > 1) { chosen = bestFallback; chosenScore = bestScore; }
      }
      const style = anchorStyle(chosen);
      guide.style.top = style.top;
      guide.style.bottom = style.bottom;
      guide.style.left = style.left;
      guide.style.right = style.right;
      guide.setAttribute("data-anchor", chosen);
      guide.setAttribute("data-side", anchorSide(chosen));
    }

    // ---------- Scroll-stop handling: always reposition, sometimes show a hint.
    let scrollTimer = null;
    let repositionTimer = null;
    let lastShown = 0;
    const COOLDOWN_MS = 20000; // minimum between auto-shows
    function onScrollStop() {
      // Always consider repositioning when scrolling has calmed down,
      // regardless of whether we show a tooltip.
      reposition();

      if (!HINTS_ENABLED) return;
      // Only auto-show on inactivity when the trigger mode allows it
      if (TRIGGER !== "inactivity") return;
      if (localStorage.getItem(LS_DISABLED) === "1") return;
      if (tip.getAttribute("data-open") === "true") return;
      if (Date.now() - lastShown < COOLDOWN_MS) return;
      const section = currentSection();
      if (!section) return;
      const id = sectionId(section);
      const dismissed = JSON.parse(sessionStorage.getItem(SS_DISMISSED) || "[]");
      if (id && dismissed.includes(id)) return;
      const h = hintFor(section);
      if (!h) return;
      lastShown = Date.now();
      openTip(h, section);
    }
    // Light repositioning during active scroll (every ~250ms, max).
    // Keeps the face from floating over a block that just scrolled under it.
    function throttledReposition() {
      if (repositionTimer) return;
      repositionTimer = setTimeout(() => {
        repositionTimer = null;
        if (tip.getAttribute("data-open") !== "true") reposition();
      }, 300);
    }
    window.addEventListener("scroll", () => {
      if (tip.getAttribute("data-open") === "true") { closeTip(); }
      throttledReposition();
      clearTimeout(scrollTimer);
      scrollTimer = setTimeout(onScrollStop, 2000);
    }, { passive: true });

    // Initial reposition after layout settles.
    setTimeout(reposition, 400);
    window.addEventListener("resize", reposition, { passive: true });

    // ---------- Trigger: error-only mode ----------
    // When the admin sets guide_trigger = 'error', the face stays quiet until
    // a form field reports a validation error (native :invalid or an element
    // gains an .is-error / .has-error class). Then it jumps and offers help.
    if (TRIGGER === "error") {
      function showErrorHint() {
        if (!HINTS_ENABLED) return;
        if (tip.getAttribute("data-open") === "true") return;
        openTip({ kicker: "Need a hand?", body: "Looks like a field needs fixing. Email help@orcapulse.ai and we'll help you through." }, null);
      }
      document.addEventListener("invalid", showErrorHint, true);
      // Observe any element toggling an error class
      const observer = new MutationObserver(muts => {
        for (const m of muts) {
          if (m.type !== "attributes") continue;
          const cls = m.target.className || "";
          if (/\b(is-error|has-error|error)\b/.test(cls)) {
            showErrorHint();
            return;
          }
        }
      });
      observer.observe(document.body, { subtree: true, attributes: true, attributeFilter: ["class"] });
    }
  })();
})();
