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
})();
