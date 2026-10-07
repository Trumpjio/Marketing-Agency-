// OrcaPulse — universal site scripts
(function () {
  "use strict";

  // Scroll restoration: always start at top
  if ("scrollRestoration" in history) history.scrollRestoration = "manual";
  if (!window.location.hash) window.scrollTo(0, 0);

  // Sticky nav shrink on scroll
  const nav = document.querySelector(".nav");
  const onScroll = () => {
    if (!nav) return;
    if (window.scrollY > 10) nav.classList.add("is-scrolled");
    else nav.classList.remove("is-scrolled");
  };
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  // ============ Mobile hamburger menu ============
  const toggle = document.getElementById("mobile-toggle");
  const panel = document.getElementById("mobile-menu");

  function openPanel() {
    if (!panel || !toggle) return;
    panel.classList.add("is-open");
    toggle.setAttribute("aria-expanded", "true");
    document.body.style.overflow = "hidden";
    // Wrap sub-items into collapsible containers (first-run only)
    if (!panel.dataset.enhanced) {
      enhancePanel();
      panel.dataset.enhanced = "1";
    }
  }

  function closePanel() {
    if (!panel || !toggle) return;
    panel.classList.remove("is-open");
    toggle.setAttribute("aria-expanded", "false");
    document.body.style.overflow = "";
  }

  function enhancePanel() {
    // Add a close "X" button
    if (!panel.querySelector(".nav__mobile-close")) {
      const closeBtn = document.createElement("button");
      closeBtn.className = "nav__mobile-close";
      closeBtn.setAttribute("aria-label", "Close menu");
      closeBtn.innerHTML = "&times;";
      closeBtn.addEventListener("click", closePanel);
      panel.appendChild(closeBtn);
    }

    // Convert groups into accordion: wrap <a> siblings of group-title into .items div
    panel.querySelectorAll(".nav__mobile-group").forEach((group) => {
      if (group.dataset.enhanced) return;
      group.dataset.enhanced = "1";
      const title = group.querySelector(".nav__mobile-group-title");
      if (!title) return;
      const items = Array.from(group.children).filter((n) => n !== title);
      if (items.length === 0) return;
      const wrap = document.createElement("div");
      wrap.className = "nav__mobile-group__items";
      items.forEach((it) => wrap.appendChild(it));
      group.appendChild(wrap);

      // Click to toggle
      title.addEventListener("click", (e) => {
        e.preventDefault();
        const isOpen = group.classList.contains("is-open");
        // Close siblings (only one open at a time, cleaner UX)
        panel.querySelectorAll(".nav__mobile-group.is-open").forEach((g) => {
          if (g !== group) g.classList.remove("is-open");
        });
        group.classList.toggle("is-open", !isOpen);
      });
    });
  }

  if (toggle && panel) {
    toggle.addEventListener("click", () => {
      if (panel.classList.contains("is-open")) closePanel();
      else openPanel();
    });
    // Clicking a link inside the panel closes it
    panel.addEventListener("click", (e) => {
      if (e.target.tagName === "A" && !e.target.closest(".nav__mobile-group-title")) {
        closePanel();
      }
    });
    // Escape key closes
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") closePanel();
    });
  }

  // ============ Reveal-on-scroll animations ============
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

  // ============ Smooth anchor scroll for internal #links ============
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

  // ============ Scroll progress indicator at top of page ============
  const bar = document.createElement("div");
  bar.className = "scroll-progress";
  bar.style.cssText = "position:fixed;top:0;left:0;height:3px;width:0;background:linear-gradient(90deg,#635bff,#18bfff);z-index:200;transition:width 0.1s linear;pointer-events:none;";
  document.body.appendChild(bar);
  window.addEventListener("scroll", () => {
    const h = document.documentElement;
    const scrolled = (h.scrollTop / (h.scrollHeight - h.clientHeight)) * 100;
    bar.style.width = scrolled + "%";
  }, { passive: true });
})();
