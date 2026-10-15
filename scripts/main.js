// OrcaPulse — universal site scripts
(function () {
  "use strict";
  if ("scrollRestoration" in history) history.scrollRestoration = "manual";
  if (!window.location.hash) window.scrollTo(0, 0);

  const nav = document.querySelector(".nav");
  const onScroll = () => { if (nav) nav.classList.toggle("is-scrolled", window.scrollY > 10); };
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  const dropdownItems = document.querySelectorAll(".nav__item");
  dropdownItems.forEach((item) => {
    const trigger = item.querySelector(".nav__trigger");
    if (!trigger) return;
    trigger.addEventListener("click", (e) => {
      e.preventDefault(); e.stopPropagation();
      const wasOpen = item.classList.contains("is-open");
      dropdownItems.forEach((other) => {
        other.classList.remove("is-open");
        const t = other.querySelector(".nav__trigger");
        if (t) t.setAttribute("aria-expanded", "false");
      });
      if (!wasOpen) { item.classList.add("is-open"); trigger.setAttribute("aria-expanded", "true"); }
    });
  });
  document.addEventListener("click", (e) => {
    if (!e.target.closest(".nav__item")) dropdownItems.forEach((item) => {
      item.classList.remove("is-open");
      const t = item.querySelector(".nav__trigger");
      if (t) t.setAttribute("aria-expanded", "false");
    });
  });

  const burger = document.getElementById("mobile-toggle");
  const panel = document.getElementById("mobile-menu");
  function setMobilePanel(open) {
    if (!burger || !panel) return;
    panel.classList.toggle("is-open", open);
    panel.setAttribute("aria-hidden", String(!open));
    burger.setAttribute("aria-expanded", String(open));
    burger.setAttribute("aria-label", open ? "Close menu" : "Open menu");
    document.body.classList.toggle("is-nav-open", open);
  }
  if (burger && panel) {
    burger.addEventListener("click", (e) => {
      e.stopPropagation(); setMobilePanel(!panel.classList.contains("is-open"));
    });
    panel.querySelectorAll(".nav__mobile-section").forEach((section, index) => {
      const oldHeading = section.querySelector(".nav__mobile-heading");
      if (!oldHeading) return;
      const links = Array.from(section.children).filter((n) => n !== oldHeading && n.tagName === "A");
      if (!links.length) return;
      const id = "mobile-nav-group-" + index;
      const button = document.createElement("button");
      button.type = "button";
      button.className = oldHeading.className;
      button.textContent = oldHeading.textContent;
      button.setAttribute("aria-expanded", "false");
      button.setAttribute("aria-controls", id);
      const wrap = document.createElement("div");
      wrap.className = "nav__mobile-section__items";
      wrap.id = id;
      wrap.hidden = true;
      links.forEach((link) => wrap.appendChild(link));
      oldHeading.replaceWith(button);
      section.appendChild(wrap);
      button.addEventListener("click", () => {
        const expanded = button.getAttribute("aria-expanded") === "true";
        button.setAttribute("aria-expanded", String(!expanded));
        wrap.hidden = expanded;
        section.classList.toggle("is-open", !expanded);
      });
      button.addEventListener("keydown", (e) => {
        if (e.key === "Escape") { button.setAttribute("aria-expanded", "false"); wrap.hidden = true; section.classList.remove("is-open"); button.focus(); }
      });
    });
    panel.addEventListener("click", (e) => { if (e.target.closest("a")) setMobilePanel(false); });
    document.addEventListener("keydown", (e) => { if (e.key === "Escape") setMobilePanel(false); });
    window.addEventListener("resize", () => { if (window.innerWidth >= 901) setMobilePanel(false); });
  }

  const revealEls = document.querySelectorAll(".reveal");
  if ("IntersectionObserver" in window) {
    const io = new IntersectionObserver((entries) => entries.forEach((entry) => {
      if (entry.isIntersecting) { entry.target.classList.add("is-visible"); io.unobserve(entry.target); }
    }), { threshold: 0.1, rootMargin: "0px 0px -8% 0px" });
    revealEls.forEach((el) => io.observe(el));
  } else revealEls.forEach((el) => el.classList.add("is-visible"));

  document.querySelectorAll('a[href^="#"]').forEach((a) => a.addEventListener("click", (e) => {
    const id = a.getAttribute("href");
    if (!id || id === "#") return;
    const el = document.querySelector(id);
    if (!el) return;
    e.preventDefault();
    el.scrollIntoView({ behavior: matchMedia("(prefers-reduced-motion: reduce)").matches ? "auto" : "smooth", block: "start" });
  }));

  const bar = document.createElement("div");
  bar.setAttribute("aria-hidden", "true");
  bar.style.cssText = "position:fixed;top:0;left:0;height:2px;width:0;background:linear-gradient(90deg,#635bff,#18bfff);z-index:200;transition:width 0.1s linear;pointer-events:none;";
  document.body.appendChild(bar);
  const updateProgress = () => {
    const h = document.documentElement, max = h.scrollHeight - h.clientHeight;
    bar.style.width = (max > 0 ? h.scrollTop / max * 100 : 0) + "%";
  };
  window.addEventListener("scroll", updateProgress, { passive: true });
  updateProgress();
  // OrcaPulse Guide behavior is retained in a separate bundle where available.
})();
