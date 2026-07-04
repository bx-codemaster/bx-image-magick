<?php
/**
 * JavaScript-Helfer für die Admin-Oberfläche von bx_image_magick.
 *
 * Dieses Script synchronisiert die Tab-Navigation, Slider, Farbfelder und
 * Transform-Strings der Bildgrößen-Konfiguration im Adminbereich. Außerdem
 * stellt es sicher, dass die zuletzt verwendete Registerkarte kurzzeitig im
 * Browser gespeichert und beim nächsten Laden wiederhergestellt wird.
 *
 * @file        bx_image_magick.php
 * @package     bx-image-magick
 * @author      bx-codemaster (benax)
 * @website     www.bx-coding.de
 * @license     GNU General Public License (GPL)
 * @since       2026-06-10
 */
  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  if (defined('MODULE_BX_IMAGE_MAGICK_STATUS') && 'True' == MODULE_BX_IMAGE_MAGICK_STATUS && basename($_SERVER['PHP_SELF']) == 'bx_image_magick.php') {
?>
<script>
  "use strict";

  /**
   * Blendet die feste Message-Stack-Box kurz ein und automatisch wieder aus.
   */
  function autoHideFixedMessageStack() {
    $(".fixed_messageStack").slideDown("slow", function() {
      setTimeout(function() {
        $(".fixed_messageStack").slideUp("slow");
      }, 2000);
    });
  }
  $(document).ready(autoHideFixedMessageStack);

  /**
   * Initialisiert die JavaScript-Logik der bx_image_magick Adminseite.
   */
  function initializeBxImageMagickAdmin() {
    const tabs             = document.querySelectorAll('.tab-nav .tab-link');
    const leftContents     = document.querySelectorAll('.magick-tabs .tab-content > div');
    const STORAGE_KEY      = 'bxImageMagickActiveTab';
    const EXPIRATION_MS    = 1000 * 60 * 60;
    const PREVIEW_ENDPOINT = "<?php echo html_entity_decode(xtc_href_link('bx_image_magick_preview.php'), ENT_QUOTES, 'UTF-8'); ?>";
    const PREVIEW_SAMPLE_STORAGE_PREFIX = 'bxImageMagickPreviewSample_';

    function escapeCssSelector(value) {
      if (window.CSS && typeof window.CSS.escape === 'function') {
        return window.CSS.escape(String(value));
      }

      return String(value).replace(/([\0-\x1f\x7f\"'#.:<>+~*^$\[\](){},=|\\/\s])/g, '\\$1');
    }

    function debounce(callback, waitMs) {
      let timeoutId = null;

      return function() {
        const context = this;
        const args = arguments;

        if (timeoutId !== null) {
          window.clearTimeout(timeoutId);
        }

        timeoutId = window.setTimeout(function() {
          timeoutId = null;
          callback.apply(context, args);
        }, waitMs);
      };
    }

    function extractTabIdFromLeftSelector(selector) {
      const match = String(selector || '').match(/^#tab\-(.+)\-left$/);
      return match ? match[1] : '';
    }

    function getPreviewSampleStorageKey(tabId) {
      return PREVIEW_SAMPLE_STORAGE_PREFIX + String(tabId || '');
    }

    function getStoredPreviewSample(tabId) {
      return localStorage.getItem(getPreviewSampleStorageKey(tabId)) || '';
    }

    function getDefaultPreviewSample(tabId) {
      const selector = `.js-magick-preview-sample[data-tab-id="${escapeCssSelector(tabId)}"]`;
      const sampleButton = document.querySelector(selector);
      return sampleButton ? (sampleButton.getAttribute('data-preview-src') || '') : '';
    }

    function isAvailablePreviewSample(tabId, previewSrc) {
      const normalizedSrc = String(previewSrc || '').trim();
      if (normalizedSrc === '') {
        return false;
      }

      const selector = `.js-magick-preview-sample[data-tab-id="${escapeCssSelector(tabId)}"][data-preview-src="${escapeCssSelector(normalizedSrc)}"]`;
      return document.querySelector(selector) !== null;
    }

    function syncPreviewSampleButtonState(container, selectedSrc) {
      if (!container) return;

      container.querySelectorAll('.js-magick-preview-sample').forEach(function(button) {
        const buttonSrc = button.getAttribute('data-preview-src') || '';
        const isActive = selectedSrc !== '' && buttonSrc === selectedSrc;
        button.classList.toggle('is-active', isActive);
        button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      });
    }

    function applyPreviewSample(tabId, previewSrc, triggerPreview) {
      const targetContainer = document.querySelector('#tab-' + escapeCssSelector(tabId) + '-left');
      if (!targetContainer) return;

      const img = targetContainer.querySelector('.magick-preview-image-area img');
      if (!img) return;

      const normalizedSrc = String(previewSrc || '').trim();
      if (normalizedSrc === '') return;

      img.setAttribute('data-original-src', normalizedSrc);
      img.src = normalizedSrc;

      const tabLink = document.querySelector('.tab-link[data-tab-id="' + escapeCssSelector(tabId) + '"]');
      const forcedWidth = tabLink ? tabLink.getAttribute('data-preview-width') : null;
      const forcedHeight = tabLink ? tabLink.getAttribute('data-preview-height') : null;
      img.addEventListener('load', function() {
        updateImageDimensions(targetContainer, forcedWidth, forcedHeight);
      }, { once: true });

      syncPreviewSampleButtonState(targetContainer, normalizedSrc);
      localStorage.setItem(getPreviewSampleStorageKey(tabId), normalizedSrc);

      if (triggerPreview !== false) {
        previewImageEffectsOnTheFly(tabId);
      }
    }

    function restorePreviewSampleForTab(tabId) {
      const storedSample = getStoredPreviewSample(tabId);
      const fallbackSample = getDefaultPreviewSample(tabId);

      if (storedSample !== '' && isAvailablePreviewSample(tabId, storedSample)) {
        applyPreviewSample(tabId, storedSample, false);
        return;
      }

      if (storedSample !== '') {
        localStorage.removeItem(getPreviewSampleStorageKey(tabId));
      }

      applyPreviewSample(tabId, fallbackSample, false);
    }

    function activateTab(tabLink) {
      // 1. Alle aktiven Klassen und Sichtbarkeiten aufheben
      tabs.forEach(t => t.classList.remove('active'));
      
      // Alle linken Panels zuruecksetzen
      leftContents.forEach(p => {
        p.classList.remove('active');
        p.setAttribute('hidden', 'hidden');
      });

      // 2. Klick-Tab aktivieren
      tabLink.classList.add('active');

      // 3. Auslesen der exakten IDs aus den Data-Attributen
      const leftSelector  = tabLink.getAttribute('data-target-left');
      const tabIdFromLink = String(tabLink.getAttribute('data-tab-id') || '').trim();

      // HIER: Die Dimensionen aus den Data-Attributen holen
      const pWidth        = tabLink.getAttribute('data-preview-width');
      const pHeight       = tabLink.getAttribute('data-preview-height');

      // 4. Elemente anzeigen
      const leftTarget = document.querySelector(leftSelector);
      if (leftTarget) {
        leftTarget.classList.add('active');
        leftTarget.removeAttribute('hidden');
        updateImageDimensions(leftTarget, pWidth, pHeight);
      }

      const tabId = tabIdFromLink || extractTabIdFromLeftSelector(leftSelector);
      if (tabId !== '') {
        restorePreviewSampleForTab(tabId);
        previewImageEffectsOnTheFly(tabId);
      }
    }

    // Der Event-Listener wird nun einfach an die Klasse gebunden:
    //const tabs = document.querySelectorAll('.tab-link');
    tabs.forEach(function(tab) {
      tab.addEventListener('click', function(event) {
        event.preventDefault();
        
        // Wir übergeben dem Aktivierer jetzt das ganze Element (this) statt nur der ID
        activateTab(this);

        // Für deinen LocalStorage-Wiederaufruf nutzen wir das href-Attribut als Key
        const tabHref = this.getAttribute('href');
        const data = { tabId: tabHref, timestamp: Date.now() };
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
      });
    });

    // Beim Neuladen (LocalStorage-Check):
    const stored = localStorage.getItem(STORAGE_KEY);
    let tabToActivate = null;

    if (stored) {
      try {
        const data = JSON.parse(stored);
        // Prüfen, ob der Eintrag noch gültig ist
        if (Date.now() - data.timestamp < EXPIRATION_MS) {
          tabToActivate = document.querySelector(`.tab-link[href="${escapeCssSelector(data.tabId)}"]`);
        } else {
          // Abgelaufen? Alten Key direkt aufräumen
          localStorage.removeItem(STORAGE_KEY);
        }
      } catch (error) {
        localStorage.removeItem(STORAGE_KEY);
      }
    }

    // FALLBACK-LOGIK:
    // Wenn kein gültiger Tab im LocalStorage gefunden wurde (weil neu oder abgelaufen)
    if (!tabToActivate) {
      // Option A: Wir nehmen einfach den allerersten Tab-Link auf der Seite
      tabToActivate = document.querySelector('.tab-link');
      
      // Option B: Falls du einen ganz spezifischen Info-Tab erzwingen willst (z.B. href="#info")
      // tabToActivate = document.querySelector('.tab-link[href="#info"]');
    }

    // Am Ende den ermittelten Tab aktivieren
    if (tabToActivate) {
      activateTab(tabToActivate);
    }

    // --- BILD-DIMENSIONS-FUNKTION (sauber gekapselt) ---
    function updateImageDimensions(container, forcedWidth, forcedHeight) {
      if (!container) return;

      // Suche den Placeholder im aktiven linken Tab-Panel
      const placeholder = container.querySelector('.magick-preview-placeholder');
      const imageArea = container.querySelector('.magick-preview-image-area');
      if (!placeholder || !imageArea) return;
      
      const img = imageArea.querySelector('img');
      if (!img) return;

      const setProperties = () => {
        // Wenn über den Tab feste PHP-Dimensionen mitgegeben wurden, nutze diese.
        // Ansonsten nimm die echten Abmessungen des Bildes als Fallback.
        const finalWidth   = forcedWidth           ? parseInt(forcedWidth, 10)  : img.naturalWidth;
        const finalHeight  = forcedHeight          ? parseInt(forcedHeight, 10) : img.naturalHeight;
        const actualWidth  = img.naturalWidth > 0  ? img.naturalWidth           : img.clientWidth;
        const actualHeight = img.naturalHeight > 0 ? img.naturalHeight          : img.clientHeight;

        // Ermittle die echten (nativen) Maße des geladenen Bildes
        const nativeWidth  = img.naturalWidth  || 0;
        const nativeHeight = img.naturalHeight || 0;

        // Ermittle die tatsächlich im Browser gerenderten Maße (CSS-Layout)
        const renderedWidth  = img.clientWidth  || 0;
        const renderedHeight = img.clientHeight || 0;

        placeholder.style.setProperty('--img-width', `"${finalWidth}px"`);
        placeholder.style.setProperty('--img-height', `"${finalHeight}px"`);
        placeholder.style.setProperty('--img-width-label', `"Max: ${finalWidth}px | Ist: ${actualWidth}px"`);
        placeholder.style.setProperty('--img-height-label', `"Max: ${finalHeight}px | Ist: ${actualHeight}px"`);

        // Nur markieren, wenn die Anzeige im Browser von der Originalgröße abweicht
        // (Toleranz von 1px abfangen, um Rundungsfehler im Browser auszugleichen)
        if (nativeWidth > 0 && (Math.abs(nativeWidth - renderedWidth) > 1 || Math.abs(nativeHeight - renderedHeight) > 1)) {
          placeholder.classList.add('is-scaled');
        } else {
          placeholder.classList.remove('is-scaled');
        }
      };

      if (img.complete) {
        setProperties();
      } else {
        img.addEventListener('load', setProperties, { once: true });
      }
    }

    // Hilfsfunktionen
    function splitTopLevelExpressions(value) {
      const parts = []; let buffer = ''; let depth = 0; let quote = null;
      for (let i = 0; i < value.length; i++) {
        const ch = value[i];
        if (quote !== null) {
          if (ch === '\\' && i + 1 < value.length) { buffer += ch + value[i + 1]; i++; continue; }
          if (ch === quote) { quote = null; }
          buffer += ch; continue;
        }
        if (ch === '\'' || ch === '"') { quote = ch; buffer += ch; continue; }
        if (ch === '(') { depth++; buffer += ch; continue; }
        if (ch === ')') { depth = Math.max(0, depth - 1); buffer += ch; continue; }
        if (ch === ',' && depth === 0) {
          const token = buffer.trim(); if (token !== '') { parts.push(token); }
          buffer = ''; continue;
        }
        buffer += ch;
      }
      const tail = buffer.trim(); if (tail !== '') { parts.push(tail); }
      return parts;
    }

    function parseEffectInt(transformValue, effectName) {
      const regex = new RegExp(effectName + '\\s*\\(\\s*(-?\\d+)\\s*(?:,|\\))', 'i');
      const match = String(transformValue || '').match(regex);
      return match ? parseInt(match[1], 10) : null;
    }

    function normalizeHexColor(value, fallback) {
      const raw = String(value || '').trim().replace(/^#/, '').toUpperCase();
      if (/^[0-9A-F]{3}$/.test(raw)) { return raw[0] + raw[0] + raw[1] + raw[1] + raw[2] + raw[2]; }
      if (/^[0-9A-F]{6}$/.test(raw)) { return raw; }
      return String(fallback || '000000').replace(/^#/, '').toUpperCase();
    }

    function parseDropShadowConfig(transformValue) {
      const match = String(transformValue || '').match(/drop_shadow\s*\(\s*(-?\d+)\s*(?:,\s*([#A-Fa-f0-9]{3,6})\s*)?(?:,\s*([#A-Fa-f0-9]{3,6})\s*)?(?:,\s*(-?\d{1,3})\s*)?\)/i);
      if (!match) return null;
      return {
        width: parseInt(match[1], 10),
        color: normalizeHexColor(match[2] || '', '000000'),
        background: normalizeHexColor(match[3] || '', 'FFFFFF'),
        fade: Math.max(20, Math.min(100, parseInt(match[4] || '65', 10)))
      };
    }

    function parseRoundEdgesConfig(transformValue) {
      const match = String(transformValue || '').match(/round_edges\s*\(\s*(-?\d+)\s*(?:,\s*([#A-Fa-f0-9]{3,6})\s*)?(?:,\s*(-?\d+)\s*)?\)/i);
      if (!match) return null;
      return { radius: parseInt(match[1], 10), background: normalizeHexColor(match[2] || '', 'FFFFFF') };
    }

    function clampToSliderRange(value, slider) {
      const min = parseInt(slider.min || '0', 10);
      const max = parseInt(slider.max || '100', 10);
      let next = Number.isFinite(value) ? value : min;
      return Math.max(min, Math.min(max, next));
    }

    function parseMergeString(mergeValue) {
      const defaults = {
        file: '',
        x: 0,
        y: 0,
        opacity: '60',
        color: 'FF0000'
      };

      const raw = String(mergeValue || '').trim();
      const match = raw.match(/^\(\s*([^,]+)\s*,\s*(-?\d+)\s*,\s*(-?\d+)\s*,\s*([^,]+)\s*,\s*([^)]+)\s*\)$/);
      if (!match) {
        return defaults;
      }

      const parsedFile = String(match[1] || '').trim();
      const normalizedFile = /^none$/i.test(parsedFile) ? '' : parsedFile;

      return {
        file: normalizedFile,
        x: parseInt(match[2], 10) || 0,
        y: parseInt(match[3], 10) || 0,
        opacity: String(match[4] || defaults.opacity).trim() || defaults.opacity,
        color: String(match[5] || defaults.color).trim() || defaults.color
      };
    }

    function buildMergeString(config, x, y) {
      const file = String(config.file || '').trim();
      const opacity = String(config.opacity || '60').trim() || '60';
      const color = String(config.color || 'FF0000').trim() || 'FF0000';

      if (file === '' || /^none$/i.test(file)) {
        return '';
      }

      return '(' + file + ',' + x + ',' + y + ',' + opacity + ',' + color + ')';
    }

    function bindMergePositioners() {
      const positioners = document.querySelectorAll('.js-merge-positioner');
      if (!positioners.length) return;

      const MAX_DISPLAY_WIDTH  = 600;
      const MAX_DISPLAY_HEIGHT = 600;

      positioners.forEach(function(positioner) {
        const tabId = positioner.getAttribute('data-tab-id') || '';
        if (tabId === '') return;

        const tabContainer = document.querySelector('#tab-' + escapeCssSelector(tabId) + '-left');
        const fallbackCanvas = tabContainer ? tabContainer.querySelector('.magick-preview-image-area') : null;
        const mergeInput    = document.querySelector('input[name="merge_' + tabId + '"]');
        const canvas        = positioner.querySelector('.magick-merge-positioner-canvas') || fallbackCanvas;
        const previewImage  = positioner.querySelector('.js-merge-positioner-preview') || (canvas ? canvas.querySelector('img') : null);
        const overlaySelect = positioner.querySelector('.js-merge-overlay-select');
        const handle        = positioner.querySelector('.magick-merge-positioner-handle');
        const sliderX       = positioner.querySelector('.js-merge-positioner-x');
        const sliderY       = positioner.querySelector('.js-merge-positioner-y');
        const valueX        = positioner.querySelector('.js-merge-positioner-x-value');
        const valueY        = positioner.querySelector('.js-merge-positioner-y-value');
        if (!mergeInput || !canvas || !handle || !sliderX || !sliderY || !valueX || !valueY) return;

        if (!positioner.querySelector('.magick-merge-positioner-canvas') && fallbackCanvas) {
          fallbackCanvas.classList.add('magick-merge-positioner-canvas-proxy');
        }

        if (handle.parentElement !== canvas) {
          canvas.appendChild(handle);
        }

        const handleDefaultText = handle.textContent;

        let modelWidth = parseInt(positioner.getAttribute('data-canvas-width') || '0', 10) || 180;
        let modelHeight = parseInt(positioner.getAttribute('data-canvas-height') || '0', 10) || 120;
        let displayWidth = modelWidth;
        let displayHeight = modelHeight;
        let scaleX = 1;
        let scaleY = 1;
        let defaultOverlayModelWidth = 96;
        let defaultOverlayModelHeight = 64;
        let overlayModelWidth = 96;
        let overlayModelHeight = 64;
        let overlayDisplayWidth = 96;
        let overlayDisplayHeight = 64;
        let overlayDimensionRequestId = 0;
        let currentX = 0;
        let currentY = 0;

        const getScaledDisplaySize = function(width, height) {
          const safeWidth = Math.max(1, width);
          const safeHeight = Math.max(1, height);
          const scale = Math.min(MAX_DISPLAY_WIDTH / safeWidth, MAX_DISPLAY_HEIGHT / safeHeight, 1);

          return {
            width: Math.max(1, Math.round(safeWidth * scale)),
            height: Math.max(1, Math.round(safeHeight * scale))
          };
        };

        const updateDisplayDimensions = function() {
          const nextDisplay = getScaledDisplaySize(modelWidth, modelHeight);
          displayWidth = nextDisplay.width;
          displayHeight = nextDisplay.height;

          canvas.style.width = String(displayWidth) + 'px';
          canvas.style.height = String(displayHeight) + 'px';

          scaleX = modelWidth / displayWidth;
          scaleY = modelHeight / displayHeight;
        };

        const updateOverlayDisplaySize = function() {
          overlayDisplayWidth = Math.max(12, Math.round(overlayModelWidth / scaleX));
          overlayDisplayHeight = Math.max(12, Math.round(overlayModelHeight / scaleY));

          handle.style.width = String(overlayDisplayWidth) + 'px';
          handle.style.height = String(overlayDisplayHeight) + 'px';
        };

        const setOverlayModelSize = function(nextWidth, nextHeight) {
          overlayModelWidth = Math.max(1, Math.round(nextWidth));
          overlayModelHeight = Math.max(1, Math.round(nextHeight));
          updateOverlayDisplaySize();
          syncSliderBounds();
          setPosition(currentX, currentY, false);
        };

        const initializeDefaultOverlaySize = function() {
          defaultOverlayModelWidth = Math.max(1, Math.round(handle.offsetWidth * scaleX));
          defaultOverlayModelHeight = Math.max(1, Math.round(handle.offsetHeight * scaleY));
          overlayModelWidth = defaultOverlayModelWidth;
          overlayModelHeight = defaultOverlayModelHeight;
          updateOverlayDisplaySize();
        };

        const loadOverlayDimensions = function(previewSrc) {
          const normalizedSrc = String(previewSrc || '').trim();
          const requestId = ++overlayDimensionRequestId;

          if (normalizedSrc === '') {
            setOverlayModelSize(defaultOverlayModelWidth, defaultOverlayModelHeight);
            return;
          }

          const image = new Image();
          image.addEventListener('load', function() {
            if (requestId !== overlayDimensionRequestId) {
              return;
            }

            setOverlayModelSize(image.naturalWidth || defaultOverlayModelWidth, image.naturalHeight || defaultOverlayModelHeight);
          });

          image.addEventListener('error', function() {
            if (requestId !== overlayDimensionRequestId) {
              return;
            }

            setOverlayModelSize(defaultOverlayModelWidth, defaultOverlayModelHeight);
          });

          image.src = normalizedSrc;
        };

        const getOverlayModelBounds = function() {
          return {
            maxX: Math.max(0, modelWidth - overlayModelWidth),
            maxY: Math.max(0, modelHeight - overlayModelHeight)
          };
        };

        const syncSliderBounds = function() {
          const bounds = getOverlayModelBounds();
          sliderX.max = String(bounds.maxX);
          sliderY.max = String(bounds.maxY);
        };

        const setPosition = function(nextX, nextY, writeToMergeInput) {
          const bounds = getOverlayModelBounds();
          currentX = Math.max(0, Math.min(bounds.maxX, Number.isFinite(nextX) ? nextX : 0));
          currentY = Math.max(0, Math.min(bounds.maxY, Number.isFinite(nextY) ? nextY : 0));

          const displayX = Math.max(0, Math.min(canvas.clientWidth - overlayDisplayWidth, Math.round(currentX / scaleX)));
          const displayY = Math.max(0, Math.min(canvas.clientHeight - overlayDisplayHeight, Math.round(currentY / scaleY)));

          handle.style.left = String(displayX) + 'px';
          handle.style.top = String(displayY) + 'px';

          sliderX.value = String(currentX);
          sliderY.value = String(currentY);
          valueX.textContent = String(currentX);
          valueY.textContent = String(currentY);

          if (writeToMergeInput) {
            const mergeConfig = parseMergeString(mergeInput.value);
            if (overlaySelect) {
              mergeConfig.file = String(overlaySelect.value || '').trim();
            }
            mergeInput.value = buildMergeString(mergeConfig, currentX, currentY);
          }
        };

        const syncOverlayFromMergeInput = function() {
          if (!overlaySelect) {
            return;
          }

          const mergeConfig = parseMergeString(mergeInput.value);
          const mergedFile = String(mergeConfig.file || '').trim();
          const optionExists = Array.prototype.some.call(overlaySelect.options, function(option) {
            return String(option.value) === mergedFile;
          });
          if (optionExists) {
            overlaySelect.value = mergedFile;
          } else {
            overlaySelect.value = '';
          }
        };

        const syncHandlePreviewFromOverlay = function() {
          if (!overlaySelect) {
            return;
          }

          const selectedOption = overlaySelect.options[overlaySelect.selectedIndex] || null;
          const previewSrc = selectedOption ? String(selectedOption.getAttribute('data-preview-src') || '').trim() : '';
          const selectedValue = selectedOption ? String(selectedOption.value || '').trim() : '';

          if (selectedValue === '') {
            handle.style.backgroundImage = 'none';
            handle.style.backgroundColor = 'transparent';
            handle.textContent = '';
            handle.style.opacity = '0';
            handle.style.pointerEvents = 'none';
            loadOverlayDimensions('');
            return;
          }

          handle.style.opacity = '1';
          handle.style.pointerEvents = 'auto';

          if (previewSrc !== '') {
            handle.style.backgroundImage = 'url("' + previewSrc.replace(/"/g, '%22') + '")';
            handle.style.backgroundRepeat = 'no-repeat';
            handle.style.backgroundPosition = 'center center';
            handle.style.backgroundSize = '100% 100%';
            handle.style.backgroundColor = 'transparent';
            handle.textContent = '';
            loadOverlayDimensions(previewSrc);
            return;
          }

          handle.style.backgroundImage = 'none';
          handle.style.backgroundColor = 'transparent';
          handle.textContent = handleDefaultText;
          loadOverlayDimensions('');
        };

        const syncFromMergeInput = function() {
          const mergeConfig = parseMergeString(mergeInput.value);
          setPosition(mergeConfig.x, mergeConfig.y, false);
          syncOverlayFromMergeInput();
          syncHandlePreviewFromOverlay();
        };

        let isDragging = false;
        let dragStartClientX = 0;
        let dragStartClientY = 0;
        let dragStartDisplayX = 0;
        let dragStartDisplayY = 0;

        handle.addEventListener('pointerdown', function(event) {
          if (overlaySelect && String(overlaySelect.value || '').trim() === '') {
            return;
          }

          isDragging = true;
          dragStartClientX = event.clientX;
          dragStartClientY = event.clientY;
          dragStartDisplayX = parseInt(handle.style.left || '0', 10) || 0;
          dragStartDisplayY = parseInt(handle.style.top || '0', 10) || 0;

          if (typeof handle.setPointerCapture === 'function') {
            handle.setPointerCapture(event.pointerId);
          }

          event.preventDefault();
        });

        handle.addEventListener('pointermove', function(event) {
          if (!isDragging) return;

          const deltaX = event.clientX - dragStartClientX;
          const deltaY = event.clientY - dragStartClientY;
          const nextDisplayX = Math.max(0, Math.min(canvas.clientWidth - overlayDisplayWidth, dragStartDisplayX + deltaX));
          const nextDisplayY = Math.max(0, Math.min(canvas.clientHeight - overlayDisplayHeight, dragStartDisplayY + deltaY));

          setPosition(Math.round(nextDisplayX * scaleX), Math.round(nextDisplayY * scaleY), true);
          event.preventDefault();
        });

        const stopDrag = function() {
          isDragging = false;
        };

        handle.addEventListener('pointerup', stopDrag);
        handle.addEventListener('pointercancel', stopDrag);
        handle.addEventListener('lostpointercapture', stopDrag);

        sliderX.addEventListener('input', function() {
          setPosition(parseInt(sliderX.value, 10) || 0, currentY, true);
        });

        sliderY.addEventListener('input', function() {
          setPosition(currentX, parseInt(sliderY.value, 10) || 0, true);
        });

        mergeInput.addEventListener('change', syncFromMergeInput);
        mergeInput.addEventListener('blur', syncFromMergeInput);

        if (overlaySelect) {
          overlaySelect.addEventListener('change', function() {
            const mergeConfig = parseMergeString(mergeInput.value);
            mergeConfig.file = String(overlaySelect.value || '').trim();
            if (mergeConfig.file === '') {
              mergeInput.value = '';
              setPosition(0, 0, false);
            } else {
              mergeInput.value = buildMergeString(mergeConfig, currentX, currentY);
            }
            syncHandlePreviewFromOverlay();
          });
        }

        const applyPreviewDimensions = function(width, height) {
          if (!Number.isFinite(width) || !Number.isFinite(height) || width <= 0 || height <= 0) {
            return;
          }

          modelWidth = Math.round(width);
          modelHeight = Math.round(height);
          updateDisplayDimensions();
          syncSliderBounds();
          setPosition(currentX, currentY, false);
        };

        if (previewImage) {
          previewImage.addEventListener('load', function() {
            applyPreviewDimensions(previewImage.naturalWidth, previewImage.naturalHeight);
          });

          previewImage.addEventListener('error', function() {
            previewImage.style.display = 'none';
          });

          if (previewImage.complete && previewImage.naturalWidth > 0 && previewImage.naturalHeight > 0) {
            applyPreviewDimensions(previewImage.naturalWidth, previewImage.naturalHeight);
          }
        }

        window.addEventListener('resize', function() {
          updateDisplayDimensions();
          updateOverlayDisplaySize();
          syncSliderBounds();
          setPosition(currentX, currentY, false);
        });

        updateDisplayDimensions();
        initializeDefaultOverlaySize();
        syncSliderBounds();
        syncFromMergeInput();
        syncHandlePreviewFromOverlay();
      });
    }

    function buildTransformWithSliders(originalValue, roundValue, roundBackgroundColor, shadowValue, shadowColor, shadowBackgroundColor, shadowFade) {
      const tokens = splitTopLevelExpressions(String(originalValue || ''));
      const nextTokens = [];
      let effectInsertIndex = -1;

      tokens.forEach(function(token) {
        if (/^round_edges\s*\(/i.test(token) || /^drop_shadow\s*\(/i.test(token)) {
          if (effectInsertIndex === -1) { effectInsertIndex = nextTokens.length; }
          return;
        }
        nextTokens.push(token);
      });

      if (effectInsertIndex === -1) { effectInsertIndex = nextTokens.length; }

      const orderedEffects = [];
      if (roundValue > 0) {
        orderedEffects.push('round_edges(' + roundValue + ',' + normalizeHexColor(roundBackgroundColor, 'FFFFFF') + ')');
      }
      if (shadowValue > 0) {
        const normalizedFade = Math.max(20, Math.min(100, parseInt(shadowFade, 10) || 65));
        orderedEffects.push('drop_shadow(' + shadowValue + ',' + normalizeHexColor(shadowColor, '000000') + ',' + normalizeHexColor(shadowBackgroundColor, 'FFFFFF') + ',' + normalizedFade + ')');
      }

      nextTokens.splice(effectInsertIndex, 0, ...orderedEffects);
      return nextTokens.join(',');
    }

    function normalizeGreyscaleValue(value) {
      const raw = String(value || '').trim();
      if (/^none$/i.test(raw)) return 'none';
      const match = raw.match(/^\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*$/);
      if (!match) return '';
      const r = Math.max(0, Math.min(255, parseInt(match[1], 10)));
      const g = Math.max(0, Math.min(255, parseInt(match[2], 10)));
      const b = Math.max(0, Math.min(255, parseInt(match[3], 10)));
      return r + ',' + g + ',' + b;
    }

    function buildTransformWithGreyscale(originalValue, greyscaleValue) {
      const normalized = normalizeGreyscaleValue(greyscaleValue);
      const tokens = splitTopLevelExpressions(String(originalValue || ''));
      const nextTokens = [];
      let insertAt = -1;

      tokens.forEach(function(token) {
        if (/^greyscale\s*\(/i.test(token)) {
          if (insertAt === -1) { insertAt = nextTokens.length; }
          return;
        }
        nextTokens.push(token);
      });

      if (insertAt === -1) { insertAt = nextTokens.length; }
      if (normalized !== '' && normalized !== 'none') {
        nextTokens.splice(insertAt, 0, 'greyscale(' + normalized + ')');
      }
      return nextTokens.join(',');
    }

    function bindRangeValueDisplay() {
      const outputs = document.querySelectorAll('.range-current[data-for]');
      outputs.forEach(function(output) {
        const targetId = output.getAttribute('data-for');
        if (!targetId) return;
        const slider = document.getElementById(targetId);
        if (!slider) return;
        const updateOutput = function() { output.textContent = String(slider.value); };
        slider.addEventListener('input', updateOutput);
        slider.addEventListener('change', updateOutput);
        updateOutput();
      });
    }

    function bindSliderTransformSync() {
      const transformInputs = document.querySelectorAll('input[name^="transform_"]');
      transformInputs.forEach(function(transformInput) {
        const tabId = transformInput.name.replace(/^transform_/, '');
        const roundSlider = document.getElementById('round_edges_id_' + tabId);
        const roundColorInput = document.getElementById('round_edges_color_' + tabId);
        const shadowSlider = document.getElementById('drop_shadow_id_' + tabId);
        const shadowFadeSlider = document.getElementById('drop_shadow_fade_id_' + tabId);
        const shadowColorInput = document.getElementById('drop_shadow_color_' + tabId);
        const shadowBgColorInput = document.getElementById('drop_shadow_bg_color_' + tabId);

        if (!roundSlider || !roundColorInput || !shadowSlider || !shadowFadeSlider || !shadowColorInput || !shadowBgColorInput) return;

        // Diese Flagge verhindert die Endlosschleife während der Synchronisation
        let isSyncing = false;

        const syncSlidersFromTransform = function() {
          if (isSyncing) return;
          isSyncing = true;

          const transformValue = transformInput.value.trim();
          
          // NUR parsen und Slider setzen, wenn überhaupt ein Transform-String existiert!
          if (transformValue !== '') {
            const roundConfig = parseRoundEdgesConfig(transformValue);
            const shadowConfig = parseDropShadowConfig(transformValue);

            if (roundConfig && Number.isInteger(roundConfig.radius)) {
              roundSlider.value = String(clampToSliderRange(roundConfig.radius, roundSlider));
              roundColorInput.value = '#' + normalizeHexColor(roundConfig.background, 'FFFFFF').toLowerCase();
            }

            if (shadowConfig && Number.isInteger(shadowConfig.width)) {
              shadowSlider.value = String(clampToSliderRange(shadowConfig.width, shadowSlider));
              shadowFadeSlider.value = String(clampToSliderRange(shadowConfig.fade, shadowFadeSlider));
              shadowColorInput.value = '#' + normalizeHexColor(shadowConfig.color, '000000').toLowerCase();
              shadowBgColorInput.value = '#' + normalizeHexColor(shadowConfig.background, 'FFFFFF').toLowerCase();
            }
          }

          // Aktualisiere die UI-Anzeige der Text-Zahlen (z.B. "65 %") direkt, 
          // ohne künstliche 'change'-Events ins System zu jagen, die syncTransform triggern
          const roundOutput = document.querySelector(`.range-current[data-for="${roundSlider.id}"]`);
          if (roundOutput) roundOutput.textContent = roundSlider.value;
          
          const shadowOutput = document.querySelector(`.range-current[data-for="${shadowSlider.id}"]`);
          if (shadowOutput) shadowOutput.textContent = shadowSlider.value;

          const fadeOutput = document.querySelector(`.range-current[data-for="${shadowFadeSlider.id}"]`);
          if (fadeOutput) fadeOutput.textContent = shadowFadeSlider.value;

          isSyncing = false;
        };

        const syncTransform = function() {
          if (isSyncing) return;
          isSyncing = true;

          const roundValue = parseInt(roundSlider.value, 10) || 0;
          const shadowValue = parseInt(shadowSlider.value, 10) || 0;
          const shadowFade = parseInt(shadowFadeSlider.value, 10) || 65;
          
          transformInput.value = buildTransformWithSliders(
            transformInput.value, 
            roundValue, 
            roundColorInput.value, 
            shadowValue, 
            shadowColorInput.value, 
            shadowBgColorInput.value, 
            shadowFade
          );

          isSyncing = false;
        };

        // 1. Initiales Befüllen beim Laden der Seite
        syncSlidersFromTransform();

        // 2. Event-Listener binden
        transformInput.addEventListener('input', syncSlidersFromTransform);
        transformInput.addEventListener('change', syncSlidersFromTransform);
        transformInput.addEventListener('blur', syncSlidersFromTransform);
        
        roundSlider.addEventListener('input', syncTransform);
        roundSlider.addEventListener('change', syncTransform);
        roundColorInput.addEventListener('change', syncTransform);
        
        shadowSlider.addEventListener('input', syncTransform);
        shadowSlider.addEventListener('change', syncTransform);
        shadowFadeSlider.addEventListener('input', syncTransform);
        shadowFadeSlider.addEventListener('change', syncTransform);
        shadowColorInput.addEventListener('change', syncTransform);
        shadowBgColorInput.addEventListener('change', syncTransform);

        const debouncedPreview = debounce(function() {
          previewImageEffectsOnTheFly(tabId);
        }, 300);

        roundSlider.addEventListener('input', debouncedPreview);
        shadowSlider.addEventListener('input', debouncedPreview);
        shadowFadeSlider.addEventListener('input', debouncedPreview);
        roundSlider.addEventListener('change', debouncedPreview);
        shadowSlider.addEventListener('change', debouncedPreview);
        shadowFadeSlider.addEventListener('change', debouncedPreview);

        // Auch bei Farbwählern oder wenn sich der Transform-String direkt ändert
        roundColorInput.addEventListener('change', debouncedPreview);
        shadowColorInput.addEventListener('change', debouncedPreview);
        shadowBgColorInput.addEventListener('change', debouncedPreview);
      });
    }

    function bindGreyscaleSelectColor() {
      const greyscaleSelects = document.querySelectorAll('select[name^="greyscale_"]');
      greyscaleSelects.forEach(function(select) {
        const updateSelectColor = function() {
          const value = String(select.value || '');
          const match = value.match(/^\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*$/);
          if (!match) { select.style.backgroundColor = ''; select.style.color = ''; return; }

          const r = Math.max(0, Math.min(255, parseInt(match[1], 10)));
          const g = Math.max(0, Math.min(255, parseInt(match[2], 10)));
          const b = Math.max(0, Math.min(255, parseInt(match[3], 10)));
          const avg = Math.round((r + g + b) / 3);

          select.style.backgroundColor = 'rgb(' + r + ',' + g + ',' + b + ')';
          select.style.color = avg < 128 ? '#ffffff' : '#000000';
        };
        select.addEventListener('change', updateSelectColor);
        updateSelectColor();
      });
    }

    function bindGreyscaleTransformSync() {
      const greyscaleSelects = document.querySelectorAll('select[name^="greyscale_"]');
      greyscaleSelects.forEach(function(select) {
        const tabId = select.name.replace(/^greyscale_/, '');
        const transformInput = document.querySelector('input[name="transform_' + tabId + '"]');
        if (!transformInput) return;
        const debouncedPreview = debounce(function() {
          previewImageEffectsOnTheFly(tabId);
        }, 300);

        select.addEventListener('change', function() {
          transformInput.value = buildTransformWithGreyscale(transformInput.value, select.value);
          debouncedPreview();
        });
      });
    }

    function bindPreviewSampleButtons() {
      document.querySelectorAll('.js-magick-preview-sample').forEach(function(button) {
        button.addEventListener('click', function(event) {
          event.preventDefault();

          const tabId = button.getAttribute('data-tab-id') || '';
          const previewSrc = button.getAttribute('data-preview-src') || '';
          if (tabId === '' || previewSrc === '') return;

          // Beim Sample-Wechsel immer die aktive Effektkette auf die Vorschau anwenden.
          applyPreviewSample(tabId, previewSrc, true);
        });
      });
    }

    function resetImageMagickForm() {
      const activeTab = document.querySelector('.tab-nav .tab-link.active');
      if (!activeTab) return;

      const activeLeftSelector = activeTab.getAttribute('data-target-left');
      const activeLeftContainer = activeLeftSelector ? document.querySelector(activeLeftSelector) : null;
      if (!activeLeftContainer) return;

      activeLeftContainer.querySelectorAll('input[name^="merge_"]').forEach(function(input) {
        input.value = '';
        input.dispatchEvent(new Event('change', { bubbles: true }));
      });

      activeLeftContainer.querySelectorAll('.js-merge-overlay-select').forEach(function(select) {
        select.value = '';
        select.dispatchEvent(new Event('change', { bubbles: true }));
      });

      activeLeftContainer.querySelectorAll('input[name^="transform_"]').forEach(function(input) {
        input.value = '';
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
      });

      activeLeftContainer.querySelectorAll('input[id^="round_edges_id_"]').forEach(function(input) {
        input.value = '0';
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
      });

      activeLeftContainer.querySelectorAll('input[id^="drop_shadow_id_"]').forEach(function(input) {
        input.value = '0';
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
      });

      activeLeftContainer.querySelectorAll('input[id^="drop_shadow_fade_id_"]').forEach(function(input) {
        input.value = '0';
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
      });

      activeLeftContainer.querySelectorAll('input[id^="round_edges_color_"]').forEach(function(input) {
        input.value = '#ffffff';
        input.dispatchEvent(new Event('change', { bubbles: true }));
      });

      activeLeftContainer.querySelectorAll('input[id^="drop_shadow_color_"]').forEach(function(input) {
        input.value = '#000000';
        input.dispatchEvent(new Event('change', { bubbles: true }));
      });

      activeLeftContainer.querySelectorAll('input[id^="drop_shadow_bg_color_"]').forEach(function(input) {
        input.value = '#ffffff';
        input.dispatchEvent(new Event('change', { bubbles: true }));
      });

      activeLeftContainer.querySelectorAll('select[name^="greyscale_"]').forEach(function(select) {
        select.value = 'none';
        select.dispatchEvent(new Event('change', { bubbles: true }));
      });

      const activeTabId = String(activeTab.getAttribute('data-tab-id') || '').trim() || extractTabIdFromLeftSelector(activeTab.getAttribute('data-target-left'));
      if (activeTabId !== '') {
        previewImageEffectsOnTheFly(activeTabId);
      }
    }

    function bindResetButton() {
      document.querySelectorAll('.js-magick-reset-form').forEach(function(button) {
        button.addEventListener('click', function(event) {
          event.preventDefault();
          resetImageMagickForm();
        });
      });
    }

    function previewImageEffectsOnTheFly(tabId) {
      const transformInput       = document.querySelector(`input[name="transform_${tabId}"]`);
      const activeLeftContainer = document.querySelector(`#tab-${tabId}-left`);
      const tabLink = document.querySelector(`.tab-link[data-tab-id="${escapeCssSelector(tabId)}"]`);
      
      if (!transformInput || !activeLeftContainer || !tabLink) return;

      const img = activeLeftContainer.querySelector('.magick-preview-image-area img');
      if (!img) return;

      const previewWidth = parseInt(tabLink.getAttribute('data-preview-width') || '0', 10);
      const previewHeight = parseInt(tabLink.getAttribute('data-preview-height') || '0', 10);
      if (previewWidth <= 0 || previewHeight <= 0) return;

      // Wir nutzen die originale Bild-URL (musst du als data-src im HTML hinterlegen)
      // oder manipulieren die bestehende URL mit den neuen Parametern
      const baseSrc = img.getAttribute('data-original-src') || img.src.split('?')[0];
      
      // Wenn noch kein data-Attribut da ist, sichern wir uns das Original beim ersten Mal
      if (!img.getAttribute('data-original-src')) {
        img.setAttribute('data-original-src', baseSrc);
      }

      // AJAX-Request an dein PHP-Backend (z.B. eine ajax_preview.php oder die shopseitige admin/bx_image_magick.php)
      const transformValue = encodeURIComponent(transformInput.value);
      
      // Wir hängen den Transform-String als GET-Parameter an ein Vorschau-Skript
      const separator = PREVIEW_ENDPOINT.indexOf('?') === -1 ? '?' : '&';
      img.addEventListener('load', function() {
        updateImageDimensions(activeLeftContainer, previewWidth, previewHeight);
      }, { once: true });

      img.src = `${PREVIEW_ENDPOINT}${separator}src=${encodeURIComponent(baseSrc)}&transform=${transformValue}&width=${previewWidth}&height=${previewHeight}&t=${Date.now()}`;
    }

    function bindPreviewFileWorkspace() {
      const workspace = document.querySelector('.js-preview-file-workspace');
      if (!workspace) return;

      const stageImage = document.getElementById('preview-file-stage-image');
      const stageEmpty = document.getElementById('preview-file-stage-empty');
      const stageCaption = document.getElementById('preview-file-stage-caption');
      const sourceToggles = workspace.querySelectorAll('.js-preview-file-toggle');
      const previewItems = workspace.querySelectorAll('.js-preview-file-item');

      if (!stageImage || !stageEmpty || !stageCaption || !sourceToggles.length) return;

      let selectedMode = 'original';
      let selectedOriginalSrc = String(workspace.getAttribute('data-original-preview-src') || '').trim();
      let selectedOriginalName = String(workspace.getAttribute('data-original-preview-name') || '').trim();
      const generatedSrc = String(workspace.getAttribute('data-generated-preview-src') || '').trim();

      const setActiveToggle = function(mode) {
        sourceToggles.forEach(function(button) {
          const isActive = button.getAttribute('data-preview-source') === mode;
          button.classList.toggle('is-active', isActive);
        });
      };

      const setStageContent = function(src, caption, emptyText) {
        const normalizedSrc = String(src || '').trim();
        if (normalizedSrc === '') {
          stageImage.setAttribute('src', '');
          stageImage.setAttribute('hidden', 'hidden');
          stageEmpty.textContent = emptyText;
          stageEmpty.removeAttribute('hidden');
          stageCaption.textContent = '';
          return;
        }

        stageImage.removeAttribute('hidden');
        stageImage.setAttribute('src', normalizedSrc);
        stageImage.setAttribute('alt', caption);
        stageEmpty.setAttribute('hidden', 'hidden');
        stageCaption.textContent = caption;
      };

      const highlightSelectedItem = function() {
        previewItems.forEach(function(item) {
          const itemSrc = String(item.getAttribute('data-preview-src') || '').trim();
          item.classList.toggle('is-selected', selectedMode === 'original' && selectedOriginalSrc !== '' && itemSrc === selectedOriginalSrc);
        });
      };

      const renderPreview = function() {
        if (selectedMode === 'generated') {
          setStageContent(generatedSrc, 'Generierte Vorschau', 'Noch keine generierte Vorschau vorhanden. Bitte zuerst "Generieren" ausführen.');
          highlightSelectedItem();
          return;
        }

        const caption = selectedOriginalName !== '' ? ('Original: ' + selectedOriginalName) : 'Original';
        setStageContent(selectedOriginalSrc, caption, 'Bitte ein Bild hochladen oder aus der Galerie auswählen.');
        highlightSelectedItem();
      };

      sourceToggles.forEach(function(button) {
        button.addEventListener('click', function() {
          selectedMode = button.getAttribute('data-preview-source') || 'original';
          setActiveToggle(selectedMode);
          renderPreview();
        });
      });

      previewItems.forEach(function(item) {
        const thumbButton = item.querySelector('.magick-gallery-thumb');
        if (!thumbButton) return;

        thumbButton.addEventListener('click', function() {
          selectedMode = 'original';
          selectedOriginalSrc = String(item.getAttribute('data-preview-src') || '').trim();
          selectedOriginalName = String(item.getAttribute('data-preview-name') || '').trim();
          setActiveToggle(selectedMode);
          renderPreview();
        });
      });

      renderPreview();

      const fileInput = document.getElementById('file-input');
      if (!fileInput) return;

      fileInput.addEventListener('change', function() {
        const file = this.files && this.files[0] ? this.files[0] : null;
        if (!file) {
          renderPreview();
          return;
        }

        const reader = new FileReader();
        reader.addEventListener('load', function() {
          selectedMode = 'original';
          selectedOriginalSrc = String(this.result || '');
          selectedOriginalName = String(file.name || '').trim();
          setActiveToggle(selectedMode);
          renderPreview();
        });
        reader.readAsDataURL(file);
      });
    }

    bindPreviewFileWorkspace();
    bindSliderTransformSync();
    bindMergePositioners();
    bindRangeValueDisplay();
    bindGreyscaleSelectColor();
    bindGreyscaleTransformSync();
    bindPreviewSampleButtons();
    bindResetButton();
  }

  // Zentraler Startpunkt nach dem Parsen des HTML
  document.addEventListener('DOMContentLoaded', initializeBxImageMagickAdmin);
</script>
<?php
  }
?>