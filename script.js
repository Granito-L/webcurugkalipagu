/**
 * script.js — Wisata Curug Kalipagu
 * Vanilla JavaScript: Animasi, Interaktivitas, Modal Detail
 */

'use strict';

/* ============================================================
   1. UTILITY FUNCTIONS
   ============================================================ */

/** Shortcut querySelector */
const $ = (sel, ctx = document) => ctx.querySelector(sel);
const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

/** Debounce */
function debounce(fn, delay = 200) {
    let timer;
    return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn(...args), delay); };
}

/* ============================================================
   2. NAVBAR — Scrolled state & Hamburger
   ============================================================ */
function initNavbar() {
    const navbar    = $('.navbar');
    const hamburger = $('.nav-hamburger');
    const navLinks  = $('.nav-links');

    if (!navbar) return;

    // Navbar scroll state
    const onScroll = debounce(() => {
        navbar.classList.toggle('scrolled', window.scrollY > 60);
    }, 50);
    window.addEventListener('scroll', onScroll, { passive: true });

    // Hamburger toggle
    if (hamburger && navLinks) {
        hamburger.addEventListener('click', () => {
            const isOpen = navLinks.classList.toggle('open');
            hamburger.setAttribute('aria-expanded', isOpen);
            document.body.style.overflow = isOpen ? 'hidden' : '';

            // Animate hamburger icon to X
            const spans = $$('span', hamburger);
            if (isOpen) {
                spans[0].style.cssText = 'transform:rotate(45deg) translate(5px,5px)';
                spans[1].style.cssText = 'opacity:0; transform:translateX(-8px)';
                spans[2].style.cssText = 'transform:rotate(-45deg) translate(5px,-5px)';
            } else {
                spans.forEach(s => s.style.cssText = '');
            }
        });

        // Close on nav link click
        $$('a', navLinks).forEach(a => {
            a.addEventListener('click', () => {
                navLinks.classList.remove('open');
                hamburger.setAttribute('aria-expanded', false);
                document.body.style.overflow = '';
                $$('span', hamburger).forEach(s => s.style.cssText = '');
            });
        });

        // Close on overlay click (when menu open)
        document.addEventListener('click', e => {
            if (navLinks.classList.contains('open')
                && !navbar.contains(e.target)) {
                navLinks.classList.remove('open');
                document.body.style.overflow = '';
                $$('span', hamburger).forEach(s => s.style.cssText = '');
            }
        });
    }
}

/* ============================================================
   3. HERO — Parallax bg & Ken-Burns load
   ============================================================ */
function initHero() {
    const heroBg = $('.hero-bg');
    if (!heroBg) return;

    // Trigger Ken-Burns animation after a tick
    requestAnimationFrame(() => {
        setTimeout(() => heroBg.classList.add('loaded'), 100);
    });

    // Subtle parallax on scroll
    const onParallax = () => {
        const scrolled = window.scrollY;
        if (scrolled < window.innerHeight) {
            heroBg.style.transform = `scale(1) translateY(${scrolled * 0.25}px)`;
        }
    };
    window.addEventListener('scroll', onParallax, { passive: true });
}

/* ============================================================
   4. SCROLL REVEAL — IntersectionObserver
   ============================================================ */
function initScrollReveal() {
    const els = $$('.reveal');
    if (!els.length) return;

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target); // Fire once
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
    );

    els.forEach(el => observer.observe(el));
}

/* ============================================================
   5. ANIMATED COUNTER (for hero stats)
   ============================================================ */
function animateCounter(el, target, duration = 1800) {
    const start     = performance.now();
    const isFloat   = String(target).includes('.');
    const startVal  = 0;

    const update = (now) => {
        const elapsed  = now - start;
        const progress = Math.min(elapsed / duration, 1);
        // Ease-out-expo
        const eased = 1 - Math.pow(2, -10 * progress);
        const current = startVal + (target - startVal) * eased;

        el.textContent = isFloat
            ? current.toFixed(1)
            : Math.floor(current).toLocaleString('id-ID');

        if (progress < 1) requestAnimationFrame(update);
        else el.textContent = isFloat ? target.toFixed(1) : target.toLocaleString('id-ID');
    };
    requestAnimationFrame(update);
}

function initCounters() {
    const statNums = $$('.stat-num[data-target]');
    if (!statNums.length) return;

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el  = entry.target;
                    const tgt = parseFloat(el.dataset.target);
                    animateCounter(el, tgt);
                    observer.unobserve(el);
                }
            });
        },
        { threshold: 0.5 }
    );
    statNums.forEach(el => observer.observe(el));
}

/* ============================================================
   6. DESTINATION MODAL
   ============================================================ */
function initModal() {
    const overlay  = document.getElementById('destModal');
    const closeBtn = overlay ? overlay.querySelector('.modal-close') : null;
    if (!overlay) return;

    // ── Open modal: ambil semua data dari atribut data-* kartu ──
    function openModal(card) {
        // Ambil data dari atribut data-* di elemen kartu
        const title       = card.dataset.title       || '';
        const subtitle    = card.dataset.subtitle    || '';
        const description = card.dataset.description || '';
        const history     = card.dataset.history     || '';
        const highlights  = card.dataset.highlights  || '';
        const location    = card.dataset.location    || '';
        const altitude    = card.dataset.altitude    || '';
        const bestTime    = card.dataset.bestTime    || ''; // data-best-time → dataset.bestTime
        const imagePath   = card.dataset.image       || '';

        // Gambar — cegah src="undefined" atau src kosong
        const imgEl = document.getElementById('modal-img');
        if (imgEl) {
            if (imagePath) {
                imgEl.src = imagePath;
                imgEl.alt = 'Foto ' + title;
                imgEl.style.display = '';
            } else {
                imgEl.src = '';
                imgEl.style.display = 'none';
            }
        }

        // Judul & subtitle di hero modal
        const titleEl    = document.getElementById('modal-title');
        const subtitleEl = document.getElementById('modal-subtitle');
        if (titleEl)    titleEl.textContent    = title;
        if (subtitleEl) subtitleEl.textContent = subtitle;

        // Deskripsi
        const descEl = document.getElementById('modal-desc');
        if (descEl) descEl.textContent = description;

        // Sejarah (bisa mengandung HTML sederhana dari DB)
        const historyEl = document.getElementById('modal-history');
        if (historyEl) historyEl.innerHTML = history;

        // Highlights — pisah dengan pipe "|" atau koma
        const highlightsEl      = document.getElementById('modal-highlights');
        const highlightsSectionEl = document.getElementById('modal-highlights-section');
        if (highlightsEl && highlightsSectionEl) {
            if (highlights) {
                const items = highlights.split(/[|,]/).map(s => s.trim()).filter(Boolean);
                highlightsEl.innerHTML = items
                    .map(item => `<span class="highlight-tag">✦ ${item}</span>`)
                    .join('');
                highlightsSectionEl.style.display = '';
            } else {
                highlightsSectionEl.style.display = 'none';
            }
        }

        // Meta chips — lokasi, ketinggian, waktu terbaik
        const chipsEl = document.getElementById('modal-chips');
        if (chipsEl) {
            chipsEl.innerHTML = '';
            const chipData = [
                { icon: '📍', val: location },
                { icon: '💧', val: altitude },
                { icon: '🌤', val: bestTime },
            ];
            chipData.forEach(c => {
                if (!c.val) return;
                const span = document.createElement('span');
                span.className = 'chip';
                span.textContent = `${c.icon} ${c.val}`;
                chipsEl.appendChild(span);
            });
        }

        // Buka modal
        overlay.style.display = 'flex';
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
        const modalBox = overlay.querySelector('.modal-box');
        if (modalBox) modalBox.scrollTop = 0;
        if (closeBtn) closeBtn.focus();
    }

    function closeModal() {
        overlay.classList.remove('open');
        overlay.style.display = 'none';
        document.body.style.overflow = '';
    }

    // Event delegation — klik kartu destinasi
    document.addEventListener('click', e => {
        const card = e.target.closest('.dest-card[data-title]');
        if (card) openModal(card);
    });

    // Tombol tutup (×)
    if (closeBtn) closeBtn.addEventListener('click', closeModal);

    // Klik di luar modal-box
    overlay.addEventListener('click', e => {
        if (e.target === overlay) closeModal();
    });

    // Tombol Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && overlay.classList.contains('open')) closeModal();
    });
}

/* ============================================================
   7. SMOOTH SCROLL for anchor links
   ============================================================ */
function initSmoothScroll() {
    document.addEventListener('click', e => {
        const a = e.target.closest('a[href^="#"]');
        if (!a) return;
        const target = document.getElementById(a.getAttribute('href').slice(1));
        if (!target) return;
        e.preventDefault();
        const offset = 80; // navbar height
        const top = target.getBoundingClientRect().top + window.scrollY - offset;
        window.scrollTo({ top, behavior: 'smooth' });
    });
}

/* ============================================================
   8. IMAGE LAZY LOADING with fallback
   ============================================================ */
function initLazyImages() {
    $$('img[loading="lazy"]').forEach(img => {
        img.addEventListener('error', function () {
            // Replace broken img with placeholder div
            const placeholder = document.createElement('div');
            placeholder.className = 'img-placeholder';
            placeholder.innerHTML = `<span>🌊</span><span>${this.alt || 'Curug Kalipagu'}</span>`;
            this.replaceWith(placeholder);
        });
    });
}

/* ============================================================
   9. INFINITE STRIP — duplicate items for seamless loop
   ============================================================ */
function initNatureStrip() {
    const strip = $('.nature-strip-inner');
    if (!strip) return;

    // Duplicate children for seamless CSS animation
    const items = $$('.strip-item', strip);
    items.forEach(item => {
        const clone = item.cloneNode(true);
        clone.setAttribute('aria-hidden', 'true');
        strip.appendChild(clone);
    });

    // Pause on hover
    strip.addEventListener('mouseenter', () => strip.style.animationPlayState = 'paused');
    strip.addEventListener('mouseleave', () => strip.style.animationPlayState = 'running');
}

/* ============================================================
   10. BACK TO TOP
   ============================================================ */
function initBackToTop() {
    const btn = $('#backToTop');
    if (!btn) return;

    const onScroll = debounce(() => {
        btn.classList.toggle('visible', window.scrollY > 500);
    }, 100);

    window.addEventListener('scroll', onScroll, { passive: true });
    btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}

/* ============================================================
   INIT — DOMContentLoaded
   ============================================================ */
document.addEventListener('DOMContentLoaded', () => {
    initNavbar();
    initHero();
    initScrollReveal();
    initCounters();
    initModal();
    initSmoothScroll();
    initLazyImages();
    initNatureStrip();
    initBackToTop();

    console.info('🌊 Wisata Curug Kalipagu — Loaded successfully');
});
