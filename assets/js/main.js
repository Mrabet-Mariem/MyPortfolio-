/**
 * main.js
 * Core application logic for Mariem Mrabet's Portfolio
 */

'use strict';

/* ============================================================
   STATE
   ============================================================ */
const State = {
  lang: 'en',
  darkMode: false,
  allProjects: [],
  currentFilter: 'all',

  /* Typed text */
  typedIndex: 0,
  typedCharIndex: 0,
  typedDeleting: false,
  typedTimeout: null,
};

/* ============================================================
   UTILITY
   ============================================================ */
function $(selector, parent = document) {
  return parent.querySelector(selector);
}

function $$(selector, parent = document) {
  return [...parent.querySelectorAll(selector)];
}

function setCookie(name, value, days = 365) {
  const expires = new Date(Date.now() + days * 864e5).toUTCString();
  document.cookie = `${name}=${encodeURIComponent(value)};expires=${expires};path=/;SameSite=Lax`;
}

function getCookie(name) {
  return document.cookie.split('; ').reduce((acc, part) => {
    const [k, v] = part.split('=');
    return k === name ? decodeURIComponent(v) : acc;
  }, null);
}

function t(key) {
  return TRANSLATIONS[State.lang]?.[key] || TRANSLATIONS.en[key] || key;
}

/* ============================================================
   LOADER
   ============================================================ */
function initLoader() {
  window.addEventListener('load', () => {
    setTimeout(() => {
      const loader = document.getElementById('loader');
      if (loader) loader.classList.add('hidden');
    }, 1200);
  });
}

/* ============================================================
   DARK MODE
   ============================================================ */
function initTheme() {
  // Load preference from cookie
  const saved = getCookie('darkMode');
  if (saved === 'true') {
    State.darkMode = true;
    document.body.classList.add('dark-mode');
    updateThemeIcon();
  }

  const btn = document.getElementById('themeToggle');
  if (btn) {
    btn.addEventListener('click', toggleTheme);
  }
}

function toggleTheme() {
  State.darkMode = !State.darkMode;
  document.body.classList.toggle('dark-mode', State.darkMode);
  setCookie('darkMode', State.darkMode);
  updateThemeIcon();
}

function updateThemeIcon() {
  const icon = document.getElementById('themeIcon');
  if (icon) {
    icon.className = State.darkMode ? 'fas fa-sun' : 'fas fa-moon';
  }
}

/* ============================================================
   LANGUAGE
   ============================================================ */
function initLanguage() {
  // Priority: cookie → browser → 'en'
  const saved = getCookie('preferredLang');
  const browserLang = navigator.language?.slice(0, 2);
  const supported = ['en', 'fr', 'ar', 'tr'];

  State.lang = supported.includes(saved) ? saved
             : supported.includes(browserLang) ? browserLang
             : 'en';

  applyLanguage(State.lang);

  // Bind buttons
  $$('.lang-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const lang = btn.dataset.lang;
      if (lang && lang !== State.lang) {
        State.lang = lang;
        setCookie('preferredLang', lang);
        applyLanguage(lang);
      }
    });
  });
}

function applyLanguage(lang) {
  // Update HTML attributes
  document.documentElement.lang = lang;
  document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
  document.body.dataset.lang = lang;

  // Translate all i18n elements
  $$('[data-i18n]').forEach(el => {
    const key = el.dataset.i18n;
    const text = TRANSLATIONS[lang]?.[key];
    if (text) el.textContent = text;
  });

  // Update active lang button
  $$('.lang-btn').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.lang === lang);
  });

  // Update placeholder text
  updatePlaceholders(lang);

  // Re-render projects with new language
  if (State.allProjects.length > 0) {
    renderProjects(State.allProjects, State.currentFilter);
  }

  // Restart typed animation
  clearTimeout(State.typedTimeout);
  State.typedIndex = 0;
  State.typedCharIndex = 0;
  State.typedDeleting = false;
  const el = document.getElementById('typedText');
  if (el) el.textContent = '';
  typeText();
}

function updatePlaceholders(lang) {
  const map = {
    en: { name: 'Mariem Mrabet', email: 'you@example.com', subject: 'Internship Opportunity', msg: 'Your message...' },
    fr: { name: 'Mariem Mrabet', email: 'vous@exemple.com', subject: 'Opportunité de stage',  msg: 'Votre message...' },
    ar: { name: 'مريم مرابط',    email: 'you@example.com', subject: 'فرصة تدريب',              msg: 'رسالتك...' },
    tr: { name: 'Mariem Mrabet', email: 'siz@ornek.com',   subject: 'Staj Fırsatı',           msg: 'Mesajınız...' }
  };
  const ph = map[lang] || map.en;
  const nameEl    = document.getElementById('contactName');
  const emailEl   = document.getElementById('contactEmail');
  const subjectEl = document.getElementById('contactSubject');
  const msgEl     = document.getElementById('contactMessage');

  if (nameEl)    nameEl.placeholder    = ph.name;
  if (emailEl)   emailEl.placeholder   = ph.email;
  if (subjectEl) subjectEl.placeholder = ph.subject;
  if (msgEl)     msgEl.placeholder     = ph.msg;
}

/* ============================================================
   TYPED ANIMATION
   ============================================================ */
function typeText() {
  const el = document.getElementById('typedText');
  if (!el) return;

  const roles = TYPED_ROLES[State.lang] || TYPED_ROLES.en;
  const current = roles[State.typedIndex];

  if (State.typedDeleting) {
    el.textContent = current.slice(0, State.typedCharIndex - 1);
    State.typedCharIndex--;
    if (State.typedCharIndex === 0) {
      State.typedDeleting = false;
      State.typedIndex = (State.typedIndex + 1) % roles.length;
      State.typedTimeout = setTimeout(typeText, 400);
      return;
    }
    State.typedTimeout = setTimeout(typeText, 60);
  } else {
    el.textContent = current.slice(0, State.typedCharIndex + 1);
    State.typedCharIndex++;
    if (State.typedCharIndex === current.length) {
      State.typedDeleting = true;
      State.typedTimeout = setTimeout(typeText, 2000);
      return;
    }
    State.typedTimeout = setTimeout(typeText, 100);
  }
}

/* ============================================================
   NAVBAR
   ============================================================ */
function initNavbar() {
  const navbar = document.getElementById('navbar');
  const hamburger = document.getElementById('hamburger');
  const navLinks = document.getElementById('navLinks');

  // Scroll effect
  window.addEventListener('scroll', () => {
    if (navbar) navbar.classList.toggle('scrolled', window.scrollY > 50);
    const scrollTop = document.getElementById('scrollTop');
    if (scrollTop) scrollTop.classList.toggle('visible', window.scrollY > 400);
  }, { passive: true });

  // Hamburger toggle
  if (hamburger && navLinks) {
    hamburger.addEventListener('click', () => {
      hamburger.classList.toggle('active');
      navLinks.classList.toggle('open');
    });
  }

  // Close mobile menu on link click
  $$('.nav-links a').forEach(link => {
    link.addEventListener('click', () => {
      hamburger?.classList.remove('active');
      navLinks?.classList.remove('open');
    });
  });

  // Active section highlight
  window.addEventListener('scroll', highlightNav, { passive: true });
}

function highlightNav() {
  const sections = $$('section[id]');
  const scrollY = window.scrollY + 100;

  sections.forEach(section => {
    const top    = section.offsetTop;
    const height = section.offsetHeight;
    const id     = section.getAttribute('id');
    const link   = $(`.nav-links a[href="#${id}"]`);
    if (link) {
      link.classList.toggle('active-link', scrollY >= top && scrollY < top + height);
    }
  });
}

/* ============================================================
   SCROLL TOP
   ============================================================ */
function initScrollTop() {
  const btn = document.getElementById('scrollTop');
  if (btn) {
    btn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }
}

/* ============================================================
   INTERSECTION OBSERVER - Animations
   ============================================================ */
function initAnimations() {
  // Add animation classes
  $$('section > .container, .project-card, .leadership-card, .cert-card').forEach((el, i) => {
    el.classList.add('fade-in');
    el.style.transitionDelay = `${(i % 4) * 0.1}s`;
  });

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        // Animate skill bars
        entry.target.querySelectorAll('.skill-bar-fill').forEach(bar => {
          bar.style.width = bar.dataset.width + '%';
        });
      }
    });
  }, { threshold: 0.1 });

  $$('.fade-in').forEach(el => observer.observe(el));

  // Observe skill bars separately
  $$('.skills-card').forEach(card => observer.observe(card));
}

/* ============================================================
   PROJECTS - AJAX LOAD
   ============================================================ */
function initProjects() {
  loadProjects();

  // Filter buttons
  $$('.filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      $$('.filter-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      State.currentFilter = btn.dataset.filter;
      renderProjects(State.allProjects, State.currentFilter);
    });
  });
}

async function loadProjects() {
  const grid = document.getElementById('projectsGrid');
  if (!grid) return;

  grid.innerHTML = '<div class="loading-spinner"><i class="fas fa-circle-notch fa-spin"></i></div>';

  try {
    const response = await fetch('api/projects.php');
    if (!response.ok) throw new Error('Network error');
    const data = await response.json();

    if (data.success && Array.isArray(data.projects)) {
      State.allProjects = data.projects;
      renderProjects(data.projects, 'all');
    } else {
      throw new Error(data.message || 'Failed to load projects');
    }
  } catch (err) {
    console.error('Projects load error:', err);
    // Fallback static projects if API fails
    State.allProjects = getFallbackProjects();
    renderProjects(State.allProjects, 'all');
  }
}

function getFallbackProjects() {
  return [
    {
      id: 1,
      title_en: 'Smart Food Pick-up System',
      title_fr: 'Système de commande de repas maison',
      title_ar: 'نظام طلب وتوزيع الوجبات المنزلية',
      title_tr: 'Akıllı Yemek Sipariş Sistemi',
      desc_en: 'A meal order management system with complete process tracking.',
      desc_fr: 'Système de gestion des commandes de repas avec suivi du processus.',
      desc_ar: 'نظام لإدارة طلبات الوجبات مع تتبع العملية الكاملة.',
      desc_tr: 'Tam süreç takibi içeren yemek sipariş yönetim sistemi.',
      tech: 'MySQL, PHP',
      github_url: 'https://github.com/mariemmrabet',
      live_url: null,
      icon: 'fa-utensils'
    },
    {
      id: 2,
      title_en: 'Machine Learning & Visual Programming',
      title_fr: 'Programmation visuelle et machine learning',
      title_ar: 'البرمجة المرئية والتعلم الآلي',
      title_tr: 'Görsel Programlama ve Makine Öğrenmesi',
      desc_en: 'Dataset analysis with machine learning steps using Python.',
      desc_fr: 'Analyse de dataset avec les étapes de machine learning.',
      desc_ar: 'تحليل مجموعة بيانات مع خطوات التعلم الآلي.',
      desc_tr: 'Python ile makine öğrenmesi adımları kullanılarak veri seti analizi.',
      tech: 'Python, Machine Learning',
      github_url: 'https://github.com/mariemmrabet',
      live_url: null,
      icon: 'fa-brain'
    },
    {
      id: 3,
      title_en: 'Inventory Management System',
      title_fr: 'Système de gestion de stock',
      title_ar: 'نظام إدارة المخزون',
      title_tr: 'Stok Yönetim Sistemi',
      desc_en: 'Relational database design for inventory management.',
      desc_fr: 'Conception d\'une base de données relationnelle pour la gestion des stocks.',
      desc_ar: 'تصميم قاعدة بيانات علائقية لإدارة المخزون.',
      desc_tr: 'Envanter yönetimi için ilişkisel veritabanı tasarımı.',
      tech: 'MySQL, SQL',
      github_url: 'https://github.com/mariemmrabet',
      live_url: null,
      icon: 'fa-boxes-stacked'
    },
    {
      id: 4,
      title_en: 'Personal Portfolio Website',
      title_fr: 'Portfolio personnel',
      title_ar: 'موقع المحفظة الشخصية',
      title_tr: 'Kişisel Portfolyo Web Sitesi',
      desc_en: 'Full-stack portfolio built with HTML, CSS, JavaScript, PHP, MySQL.',
      desc_fr: 'Portfolio full-stack avec HTML, CSS, JavaScript, PHP, MySQL.',
      desc_ar: 'محفظة متكاملة مبنية بـ HTML وCSS وJavaScript وPHP وMySQL.',
      desc_tr: 'HTML, CSS, JavaScript, PHP ve MySQL ile geliştirilmiş full-stack portfolyo.',
      tech: 'HTML, CSS, JavaScript, PHP, MySQL',
      github_url: 'https://github.com/mariemmrabet',
      live_url: null,
      icon: 'fa-globe'
    }
  ];
}

function renderProjects(projects, filter) {
  const grid = document.getElementById('projectsGrid');
  if (!grid) return;

  const lang = State.lang;

  const filtered = filter === 'all'
    ? projects
    : projects.filter(p => p.tech.toLowerCase().includes(filter.toLowerCase()));

  if (filtered.length === 0) {
    grid.innerHTML = `<div class="loading-spinner" style="color:var(--clr-text-muted)">
      <i class="fas fa-folder-open" style="font-size:2.5rem;margin-right:.5rem"></i>
      <span>No projects found</span>
    </div>`;
    return;
  }

  const icons = {
    'MySQL': 'fa-database',
    'Python': 'fa-python',
    'HTML': 'fa-globe',
    'Machine': 'fa-brain'
  };

  grid.innerHTML = filtered.map(p => {
    const title = p[`title_${lang}`] || p.title_en;
    const desc  = p[`desc_${lang}`]  || p.desc_en;
    const tags  = p.tech.split(',').map(t => t.trim());
    const iconClass = p.icon || Object.entries(icons).find(([k]) =>
      p.tech.includes(k))?.[1] || 'fa-code';

    return `
      <div class="project-card fade-in"
           data-id="${p.id}"
           data-tech="${p.tech}"
           onclick="openProjectModal(${p.id})">
        <div class="project-img">
          ${p.image && !p.image.includes('default')
            ? `<img src="${p.image}" alt="${title}" loading="lazy" />`
            : `<i class="fas ${iconClass}"></i>`
          }
          <div class="project-img-overlay">
            ${p.github_url ? `<a href="${p.github_url}" target="_blank"
                onclick="event.stopPropagation()" aria-label="GitHub">
                <i class="fab fa-github"></i></a>` : ''}
            ${p.live_url ? `<a href="${p.live_url}" target="_blank"
                onclick="event.stopPropagation()" aria-label="Live Demo">
                <i class="fas fa-external-link-alt"></i></a>` : ''}
          </div>
        </div>
        <div class="project-body">
          <h3>${title}</h3>
          <p>${desc}</p>
          <div class="project-tech">
            ${tags.map(tag => `<span class="tech-tag">${tag}</span>`).join('')}
          </div>
        </div>
      </div>
    `;
  }).join('');

  // Trigger animations
  setTimeout(() => {
    $$('#projectsGrid .fade-in').forEach(el => el.classList.add('visible'));
  }, 50);
}

/* ============================================================
   PROJECT MODAL
   ============================================================ */
function openProjectModal(id) {
  const project = State.allProjects.find(p => p.id === id || p.id === String(id));
  if (!project) return;

  const lang  = State.lang;
  const title = project[`title_${lang}`] || project.title_en;
  const desc  = project[`desc_${lang}`]  || project.desc_en;
  const tags  = project.tech.split(',').map(t => t.trim());

  const content = document.getElementById('modalContent');
  content.innerHTML = `
    <h2 style="margin-bottom:1rem;color:var(--clr-primary)">${title}</h2>
    <p style="color:var(--clr-text-muted);margin-bottom:1.5rem;line-height:1.8">${desc}</p>
    <div style="margin-bottom:1.5rem">
      <strong style="display:block;margin-bottom:.5rem">${t('project_tech_label')}</strong>
      <div class="project-tech">
        ${tags.map(tag => `<span class="tech-tag">${tag}</span>`).join('')}
      </div>
    </div>
    <div style="display:flex;gap:1rem;flex-wrap:wrap">
      ${project.github_url ? `
        <a href="${project.github_url}" target="_blank" class="btn btn-primary">
          <i class="fab fa-github"></i> ${t('project_github')}
        </a>` : ''}
      ${project.live_url ? `
        <a href="${project.live_url}" target="_blank" class="btn btn-outline">
          <i class="fas fa-external-link-alt"></i> ${t('project_live')}
        </a>` : ''}
    </div>
  `;

  const modal = document.getElementById('projectModal');
  if (modal) modal.classList.add('active');
  document.body.style.overflow = 'hidden';
}

function initModal() {
  const modal = document.getElementById('projectModal');
  const closeBtn = document.getElementById('modalClose');

  if (closeBtn) {
    closeBtn.addEventListener('click', closeModal);
  }

  if (modal) {
    modal.addEventListener('click', (e) => {
      if (e.target === modal) closeModal();
    });
  }

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
  });
}

function closeModal() {
  const modal = document.getElementById('projectModal');
  if (modal) modal.classList.remove('active');
  document.body.style.overflow = '';
}

/* ============================================================
   CONTACT FORM - AJAX SUBMIT
   ============================================================ */
function initContactForm() {
  const form = document.getElementById('contactForm');
  if (!form) return;

  // Attach real-time validation
  Validator.attachRealTime(State.lang);

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    // JS Validation
    if (!Validator.validateContactForm(State.lang)) return;

    const submitBtn  = document.getElementById('submitBtn');
    const feedback   = document.getElementById('formFeedback');

    // Loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Sending...';
    feedback.className = 'form-feedback';
    feedback.style.display = 'none';

    const formData = new FormData(form);

    try {
      const res = await fetch('api/contact.php', {
        method: 'POST',
        body: formData
      });

      const data = await res.json();

      if (data.success) {
        feedback.className = 'form-feedback success';
        feedback.textContent = t('form_success');
        form.reset();
        Validator.clearErrors();
      } else {
        feedback.className = 'form-feedback error';
        feedback.textContent = data.message || t('form_error_server');
      }
    } catch (err) {
      feedback.className = 'form-feedback error';
      feedback.textContent = t('form_error_server');
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = `<i class="fas fa-paper-plane"></i> <span>${t('form_send')}</span>`;
    }
  });
}

/* ============================================================
   FOOTER YEAR
   ============================================================ */
function initFooter() {
  const yearEl = document.getElementById('currentYear');
  if (yearEl) yearEl.textContent = new Date().getFullYear();
}

/* ============================================================
   INIT
   ============================================================ */
document.addEventListener('DOMContentLoaded', () => {
  initLoader();
  initTheme();
  initLanguage();
  initNavbar();
  initScrollTop();
  initProjects();
  initModal();
  initContactForm();
  initFooter();
  typeText();

  // Animations after a brief delay
  setTimeout(initAnimations, 300);
});
