(() => {
  const body = document.body;
  const header = document.querySelector('.site-header');
  const toggle = document.querySelector('.menu-toggle');
  const nav = document.querySelector('[data-mobile-nav]');
  const mobile = matchMedia('(max-width: 1080px)');
  const focusable = 'a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])';
  let lastFocus = null;

  const syncNavA11y = () => {
    if (!nav || !toggle) return;
    if (!mobile.matches) {
      nav.inert = false;
      nav.removeAttribute('aria-hidden');
      nav.classList.remove('open');
      toggle.setAttribute('aria-expanded', 'false');
      body.classList.remove('menu-open');
      return;
    }
    const open = nav.classList.contains('open');
    nav.inert = !open;
    nav.setAttribute('aria-hidden', open ? 'false' : 'true');
  };
  const closeMenu = (restore = true) => {
    if (!nav || !toggle) return;
    nav.classList.remove('open');
    toggle.setAttribute('aria-expanded', 'false');
    body.classList.remove('menu-open');
    syncNavA11y();
    if (restore && lastFocus instanceof HTMLElement) lastFocus.focus();
  };
  const openMenu = () => {
    if (!nav || !toggle || !mobile.matches) return;
    lastFocus = document.activeElement;
    nav.classList.add('open');
    toggle.setAttribute('aria-expanded', 'true');
    body.classList.add('menu-open');
    syncNavA11y();
    const first = nav.querySelector(focusable);
    if (first) requestAnimationFrame(() => first.focus());
  };
  toggle?.addEventListener('click', () => nav?.classList.contains('open') ? closeMenu() : openMenu());
  nav?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => closeMenu(false)));
  document.addEventListener('keydown', (event) => {
    if (!nav?.classList.contains('open')) return;
    if (event.key === 'Escape') {
      event.preventDefault();
      closeMenu();
      return;
    }
    if (event.key === 'Tab') {
      const items = [...nav.querySelectorAll(focusable)].filter((el) => !el.closest('[hidden]'));
      if (!items.length) return;
      const [first] = items;
      const last = items[items.length - 1];
      if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
      else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    }
  });
  mobile.addEventListener?.('change', syncNavA11y);
  syncNavA11y();

  const progress = header?.querySelector('.scroll-progress');
  let headerTick = false;
  const onScroll = () => {
    if (headerTick) return;
    headerTick = true;
    requestAnimationFrame(() => {
      header?.classList.toggle('scrolled', scrollY > 28);
      if (progress) {
        const max = Math.max(1, document.documentElement.scrollHeight - innerHeight);
        progress.style.setProperty('--scroll-progress', String(Math.min(1, Math.max(0, scrollY / max))));
      }
      headerTick = false;
    });
  };
  onScroll();
  addEventListener('scroll', onScroll, { passive: true });

  const observer = 'IntersectionObserver' in window
    ? new IntersectionObserver((entries) => entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      }), { threshold: 0.11, rootMargin: '0px 0px -7% 0px' })
    : null;
  document.querySelectorAll('.reveal').forEach((el) => observer ? observer.observe(el) : el.classList.add('is-visible'));

  const filterWrap = document.querySelector('[data-project-filters]');
  const grid = document.querySelector('[data-project-grid]');
  if (filterWrap && grid) {
    let type = 'all';
    let year = 'all';
    const apply = () => {
      let shown = 0;
      grid.querySelectorAll('.project-card').forEach((card) => {
        const types = (card.dataset.type || '').split(/\s+/).filter(Boolean);
        const matchesType = type === 'all' || types.includes(type);
        const matchesYear = year === 'all' || card.dataset.year === year;
        const visible = matchesType && matchesYear;
        card.hidden = !visible;
        if (visible) shown += 1;
      });
      document.querySelector('.filter-empty')?.toggleAttribute('hidden', shown !== 0);
    };
    filterWrap.querySelectorAll('[data-filter]').forEach((button) => button.addEventListener('click', () => {
      type = button.dataset.filter || 'all';
      filterWrap.querySelectorAll('[data-filter]').forEach((candidate) => candidate.classList.toggle('active', candidate === button));
      apply();
    }));
    filterWrap.querySelector('[data-year-filter]')?.addEventListener('change', (event) => { year = event.target.value; apply(); });
  }

  document.querySelectorAll('[data-before-after]').forEach((wrapper) => {
    const range = wrapper.querySelector('[data-before-range]');
    const layer = wrapper.querySelector('[data-before-layer]');
    const handle = wrapper.querySelector('[data-before-handle]');
    if (!range || !layer || !handle) return;
    const update = () => {
      const value = Number(range.value);
      layer.style.clipPath = body.classList.contains('is-rtl') ? `inset(0 0 0 ${100 - value}%)` : `inset(0 ${100 - value}% 0 0)`;
      handle.style.left = `${value}%`;
    };
    range.addEventListener('input', update);
    update();
  });

  const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (matchMedia('(pointer: fine)').matches && !reduced) {
    document.querySelectorAll('.magnetic').forEach((el) => {
      el.addEventListener('pointermove', (event) => {
        const rect = el.getBoundingClientRect();
        const x = (event.clientX - rect.left - rect.width / 2) * 0.12;
        const y = (event.clientY - rect.top - rect.height / 2) * 0.12;
        el.style.transform = `translate(${x}px, ${y}px)`;
      });
      el.addEventListener('pointerleave', () => { el.style.transform = ''; });
    });
    const dot = document.querySelector('.cursor-dot');
    if (dot) {
      let pointerX = 0, pointerY = 0, pointerTick = false;
      addEventListener('pointermove', (event) => {
        pointerX = event.clientX; pointerY = event.clientY;
        if (pointerTick) return;
        pointerTick = true;
        requestAnimationFrame(() => { dot.style.transform = `translate(${pointerX}px, ${pointerY}px)`; pointerTick = false; });
      }, { passive: true });
      document.querySelectorAll('a,button,input,textarea,select').forEach((el) => {
        el.addEventListener('mouseenter', () => dot.classList.add('active'));
        el.addEventListener('mouseleave', () => dot.classList.remove('active'));
      });
    }
  }

  if (!reduced) {
    const hero = document.querySelector('.hero-image img');
    let parallaxTick = false;
    const parallax = () => {
      if (!hero || parallaxTick) return;
      parallaxTick = true;
      requestAnimationFrame(() => {
        const rect = hero.closest('.hero-image')?.getBoundingClientRect();
        const visible = rect && rect.bottom > 0 && rect.top < innerHeight;
        hero.style.setProperty('--parallax-y', visible ? `${Math.max(-10, Math.min(18, -rect.top * 0.025))}px` : '0px');
        parallaxTick = false;
      });
    };
    parallax();
    addEventListener('scroll', parallax, { passive: true });
  }
})();

(() => {
  document.querySelectorAll('[data-contact-form]').forEach((form) => {
    const copy = window.NexoraFront || {};
    const fields = {
      name: form.elements.namedItem('name'),
      email: form.elements.namedItem('email'),
      projectType: form.elements.namedItem('project_type'),
      message: form.elements.namedItem('message'),
      human: form.elements.namedItem('human_answer'),
      privacy: form.elements.namedItem('privacy_consent'),
    };
    Object.values(fields).filter(Boolean).forEach((field) => field.addEventListener('input', () => field.setCustomValidity('')));
    form.addEventListener('submit', (event) => {
      Object.values(fields).filter(Boolean).forEach((field) => field.setCustomValidity(''));
      if (fields.name && fields.name.value.trim().length < 2) fields.name.setCustomValidity(copy.nameRequired || '');
      if (fields.email) {
        if (!fields.email.value.trim()) fields.email.setCustomValidity(copy.emailRequired || '');
        else if (fields.email.validity.typeMismatch) fields.email.setCustomValidity(copy.emailInvalid || '');
      }
      if (fields.projectType && !fields.projectType.value) fields.projectType.setCustomValidity(copy.projectTypeRequired || '');
      if (fields.message && fields.message.value.trim().length < 20) fields.message.setCustomValidity(copy.messageRequired || '');
      if (fields.human && !fields.human.value.trim()) fields.human.setCustomValidity(copy.humanRequired || '');
      if (fields.privacy && !fields.privacy.checked) fields.privacy.setCustomValidity(copy.privacyRequired || '');
      if (!form.checkValidity()) {
        event.preventDefault();
        form.reportValidity();
        return;
      }
      const button = form.querySelector('button[type="submit"]');
      if (!button || button.disabled) { event.preventDefault(); return; }
      button.disabled = true;
      button.setAttribute('aria-busy', 'true');
      button.textContent = button.dataset.loadingLabel || copy.sending || '';
    });
  });
})();

(() => {
  const input = document.querySelector('[data-search-suggest]');
  const panel = document.querySelector('[data-search-suggestions]');
  if (!input || !panel || !window.NexoraFront?.searchUrl) return;
  let timer = 0;
  let controller = null;
  let activeIndex = -1;
  const labels = window.NexoraFront.searchTypes || {};
  const options = () => [...panel.querySelectorAll('[role="option"]')];
  const setActive = (index) => {
    const items = options();
    if (!items.length) { activeIndex = -1; input.removeAttribute('aria-activedescendant'); return; }
    activeIndex = Math.max(0, Math.min(index, items.length - 1));
    items.forEach((item, i) => { const active = i === activeIndex; item.classList.toggle('active', active); item.setAttribute('aria-selected', active ? 'true' : 'false'); });
    input.setAttribute('aria-activedescendant', items[activeIndex].id);
    items[activeIndex].scrollIntoView({ block: 'nearest' });
  };
  const close = () => {
    panel.hidden = true;
    panel.replaceChildren();
    input.setAttribute('aria-expanded', 'false');
    input.removeAttribute('aria-activedescendant');
    activeIndex = -1;
  };
  const render = (items) => {
    panel.replaceChildren();
    activeIndex = -1;
    if (!items.length) {
      const empty = document.createElement('span');
      empty.setAttribute('role', 'status');
      empty.textContent = window.NexoraFront.noSuggestions || '';
      panel.append(empty);
    } else {
      items.forEach((item, index) => {
        const link = document.createElement('a');
        link.href = item.url;
        link.id = `nexora-suggestion-${index}`;
        link.setAttribute('role', 'option');
        link.setAttribute('aria-selected', 'false');
        const title = document.createElement('b'); title.textContent = item.title;
        const type = document.createElement('small'); type.textContent = labels[item.type] || item.type;
        link.append(title, type); panel.append(link);
      });
    }
    panel.hidden = false;
    input.setAttribute('aria-expanded', 'true');
  };
  input.addEventListener('input', () => {
    clearTimeout(timer);
    const term = input.value.trim();
    if (term.length < 2) { close(); return; }
    timer = setTimeout(async () => {
      try {
        controller?.abort();
        controller = new AbortController();
        const url = new URL(window.NexoraFront.searchUrl);
        url.searchParams.set('term', term.slice(0, 80));
        url.searchParams.set('lang', window.NexoraFront.searchLang || 'fa');
        const response = await fetch(url, { signal: controller.signal, credentials: 'same-origin' });
        if (!response.ok) throw new Error('search');
        render(await response.json());
      } catch (error) {
        if (error.name !== 'AbortError') close();
      }
    }, 250);
  });
  input.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') { close(); return; }
    if (panel.hidden) return;
    if (event.key === 'ArrowDown') { event.preventDefault(); setActive(activeIndex + 1); }
    else if (event.key === 'ArrowUp') { event.preventDefault(); setActive(activeIndex <= 0 ? options().length - 1 : activeIndex - 1); }
    else if (event.key === 'Enter' && activeIndex >= 0) { event.preventDefault(); options()[activeIndex]?.click(); }
  });
  panel.addEventListener('mousemove', (event) => {
    const item = event.target.closest('[role="option"]');
    if (!item) return;
    setActive(options().indexOf(item));
  });
  document.addEventListener('click', (event) => { if (!panel.contains(event.target) && event.target !== input) close(); });
})();
