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
    const tabs          = document.querySelectorAll('.tab-nav .tab-link');
    const leftContents  = document.querySelectorAll('.magick-tabs .tab-content > div');
    const rightContents = document.querySelectorAll('.boxRight .tab-content > div');
    const STORAGE_KEY   = 'bxImageMagickActiveTab';
    const EXPIRATION_MS = 1000 * 60 * 60;

    function activateTab(tabLink) {
      // 1. Alle aktiven Klassen und Sichtbarkeiten aufheben
      tabs.forEach(t => t.classList.remove('active'));
      
      // Sucht alle linken und rechten Boxen anhand der Struktur
      leftContents.forEach(p => {
        p.classList.remove('active');
        p.setAttribute('hidden', 'hidden');
      });
      rightContents.forEach(p => {
        p.classList.remove('active');
        p.setAttribute('hidden', 'hidden');
      });

      // 2. Klick-Tab aktivieren
      tabLink.classList.add('active');

      // 3. Auslesen der exakten IDs aus den Data-Attributen
      const leftSelector  = tabLink.getAttribute('data-target-left');
      const rightSelector = tabLink.getAttribute('data-target-right');

      // HIER: Die Dimensionen aus den Data-Attributen holen
      const pWidth        = tabLink.getAttribute('data-preview-width');
      const pHeight       = tabLink.getAttribute('data-preview-height');

      // 4. Elemente anzeigen
      const leftTarget = document.querySelector(leftSelector);
      if (leftTarget) {
        leftTarget.classList.add('active');
        leftTarget.removeAttribute('hidden');
      }

      const rightTarget = document.querySelector(rightSelector);
      if (rightTarget) {
        rightTarget.classList.add('active');
        rightTarget.removeAttribute('hidden');
        // Übergabe der Box und der dynamischen PHP-Vorgaben für diesen Tab
        updateImageDimensions(rightTarget, pWidth, pHeight);
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
          tabToActivate = document.querySelector(`.tab-link[href="${data.tabId}"]`);
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

      // Suche den Placeholder NUR in der rechten Box (tab-info-right)
      const placeholder = container.querySelector('.magick-preview-placeholder');
      if (!placeholder) return;
      
      const img = placeholder.querySelector('img');
      if (!img) return;

      const setProperties = () => {
        // Wenn über den Tab feste PHP-Dimensionen mitgegeben wurden, nutze diese.
        // Ansonsten nimm die echten Abmessungen des Bildes als Fallback.
        const finalWidth = forcedWidth ? parseInt(forcedWidth, 10) : img.naturalWidth;
        const finalHeight = forcedHeight ? parseInt(forcedHeight, 10) : img.naturalHeight;

        placeholder.style.setProperty('--img-width', `"${finalWidth}px"`);
        placeholder.style.setProperty('--img-height', `"${finalHeight}px"`);

        // Wenn das Bild kleiner gerendert wird als seine echten (oder erzwungenen) Maße,
        // dann stößt es gerade an die Container-Grenzen (max-width: 100% greift)
        if (finalWidth > placeholder.clientWidth || finalHeight > placeholder.clientHeight) {
          placeholder.classList.add('is-scaled');
          //console.log('Das Bild ist größer als der Container und wird herunterskaliert!');
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

    // Hilfsfunktionen (unverändert für deine Logik)
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
        select.addEventListener('change', function() {
          transformInput.value = buildTransformWithGreyscale(transformInput.value, select.value);
        });
      });
    }

    bindSliderTransformSync();
    bindRangeValueDisplay();
    bindGreyscaleSelectColor();
    bindGreyscaleTransformSync();
    
    // HIER: Einmaliger Initialaufruf beim ersten Laden der Seite
    updateImageDimensions();
  }

  // Zentraler Startpunkt nach dem Parsen des HTML
  document.addEventListener('DOMContentLoaded', initializeBxImageMagickAdmin);
</script>
<?php
  }
?>