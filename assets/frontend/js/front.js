/**
 * Design Cart Sand Slider
 * Autor: Paweł Nosko
 * Firma: Design Cart
 * Adres: https://www.designcart.pl
 */

import { SandSlider } from './sandslider.js';

const COPY_OUT_MS = 560;

function contentIsVisible(nodes) {
  const visible = nodes.filter(
    (el) => el && !el.hidden && (el.textContent || '').trim()
  );
  if (!visible.length) return false;
  return visible.some((el) => parseFloat(getComputedStyle(el).opacity) > 0.08);
}

function createCopyController(root) {
  const slideCopy = root.querySelector('[data-dc-ss-copy]');
  const slideHeader = root.querySelector('[data-dc-ss-header]');
  const slideSubheader = root.querySelector('[data-dc-ss-subheader]');
  const slideCta = root.querySelector('[data-dc-ss-cta]');
  let copyIndex = -1;
  let copySeq = 0;

  function applySlideText(slide) {
    slideHeader.textContent = slide?.header || '';
    slideSubheader.textContent = slide?.subheader || '';

    if (slide?.cta?.label) {
      slideCta.hidden = false;
      slideCta.textContent = slide.cta.label;
      slideCta.href = slide.cta.href || '#';
      if (slide.cta.target) slideCta.target = slide.cta.target;
      else slideCta.removeAttribute('target');
    } else {
      slideCta.hidden = true;
      slideCta.removeAttribute('href');
      slideCta.textContent = '';
    }
  }

  function playCopyOut() {
    return new Promise((resolve) => {
      if (!contentIsVisible([slideHeader, slideSubheader, slideCta])) {
        slideCopy.classList.remove('is-in', 'is-out');
        resolve();
        return;
      }
      slideCopy.classList.remove('is-in');
      slideCopy.classList.add('is-out');
      window.setTimeout(() => {
        slideCopy.classList.remove('is-out');
        resolve();
      }, COPY_OUT_MS);
    });
  }

  async function setSlideCopy(slide, { animate = true } = {}) {
    const index = slide?.index ?? -1;
    if (animate && index === copyIndex && slideCopy.classList.contains('is-in')) {
      return;
    }

    const seq = ++copySeq;

    if (animate && copyIndex >= 0) {
      await playCopyOut();
      if (seq !== copySeq) return;
    }

    copyIndex = index;
    applySlideText(slide);

    if (!animate) {
      slideCopy.classList.remove('is-out');
      slideCopy.classList.add('is-in');
      return;
    }

    slideCopy.classList.remove('is-in', 'is-out');
    void slideCopy.offsetWidth;
    requestAnimationFrame(() => {
      if (seq !== copySeq) return;
      slideCopy.classList.add('is-in');
    });
  }

  return { slideCopy, setSlideCopy };
}

async function mountSlider(root) {
  const stage = root.querySelector('[data-dc-ss-stage]');
  const loader = root.querySelector('[data-dc-ss-loader]');
  const raw = root.getAttribute('data-config');
  if (!stage || !raw) return;

  let config;
  try {
    config = JSON.parse(raw);
  } catch (err) {
    if (loader) loader.textContent = 'Błąd konfiguracji';
    return;
  }

  const copy = createCopyController(root);

  try {
    const slider = await SandSlider.create(stage, {
      ...config,
      onTransition: (_from, _to, _fromSlide, toSlide) => {
        void copy.setSlideCopy(toSlide, { animate: true });
      },
    });

    if (slider.ui?.frame) {
      slider.ui.frame.prepend(copy.slideCopy);
      copy.slideCopy.hidden = false;
    }

    void copy.setSlideCopy(slider.currentSlide, { animate: true });
    loader?.classList.add('is-hidden');
    root._dcSandSlider = slider;
  } catch (err) {
    console.error(err);
    if (loader) {
      loader.textContent = 'Błąd ładowania: ' + (err?.message || err);
    }
  }
}

function init() {
  document.querySelectorAll('[data-dc-sand-slider]').forEach((el) => {
    if (el.dataset.dcSsReady === '1') return;
    el.dataset.dcSsReady = '1';
    void mountSlider(el);
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}
