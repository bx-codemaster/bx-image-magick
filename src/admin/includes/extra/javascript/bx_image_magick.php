<?php
  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  if ( defined('MODULE_BX_IMAGE_MAGICK_STATUS') && 'True' == MODULE_BX_IMAGE_MAGICK_STATUS  && basename($_SERVER['PHP_SELF']) == 'bx_image_magick.php') {
?>
<script>
"use strict";
document.addEventListener('DOMContentLoaded', function () {
  const tabs = document.querySelectorAll('.magick-tabs .tab-nav a');
  const contents = document.querySelectorAll('.magick-tabs .tab-content > div');

  function splitTopLevelExpressions(value) {
    const parts = [];
    let buffer = '';
    let depth = 0;
    let quote = null;

    for (let i = 0; i < value.length; i++) {
      const ch = value[i];

      if (quote !== null) {
        if (ch === '\\' && i + 1 < value.length) {
          buffer += ch + value[i + 1];
          i++;
          continue;
        }
        if (ch === quote) {
          quote = null;
        }
        buffer += ch;
        continue;
      }

      if (ch === '\'' || ch === '"') {
        quote = ch;
        buffer += ch;
        continue;
      }

      if (ch === '(') {
        depth++;
        buffer += ch;
        continue;
      }

      if (ch === ')') {
        depth = Math.max(0, depth - 1);
        buffer += ch;
        continue;
      }

      if (ch === ',' && depth === 0) {
        const token = buffer.trim();
        if (token !== '') {
          parts.push(token);
        }
        buffer = '';
        continue;
      }

      buffer += ch;
    }

    const tail = buffer.trim();
    if (tail !== '') {
      parts.push(tail);
    }

    return parts;
  }

  function parseEffectInt(transformValue, effectName) {
    const regex = new RegExp(effectName + '\\s*\\(\\s*(-?\\d+)\\s*(?:,|\\))', 'i');
    const match = String(transformValue || '').match(regex);
    if (!match) {
      return null;
    }
    return parseInt(match[1], 10);
  }

  function normalizeHexColor(value, fallback) {
    const raw = String(value || '').trim().replace(/^#/, '').toUpperCase();
    if (/^[0-9A-F]{3}$/.test(raw)) {
      return raw[0] + raw[0] + raw[1] + raw[1] + raw[2] + raw[2];
    }
    if (/^[0-9A-F]{6}$/.test(raw)) {
      return raw;
    }
    return String(fallback || '000000').replace(/^#/, '').toUpperCase();
  }

  function parseDropShadowConfig(transformValue) {
    const match = String(transformValue || '').match(/drop_shadow\s*\(\s*(-?\d+)\s*(?:,\s*([#A-Fa-f0-9]{3,6})\s*)?(?:,\s*([#A-Fa-f0-9]{3,6})\s*)?(?:,\s*(-?\d{1,3})\s*)?\)/i);
    if (!match) {
      return null;
    }

    return {
      width: parseInt(match[1], 10),
      color: normalizeHexColor(match[2] || '', '000000'),
      background: normalizeHexColor(match[3] || '', 'FFFFFF'),
      fade: Math.max(20, Math.min(100, parseInt(match[4] || '65', 10)))
    };
  }

  function parseRoundEdgesConfig(transformValue) {
    const match = String(transformValue || '').match(/round_edges\s*\(\s*(-?\d+)\s*(?:,\s*([#A-Fa-f0-9]{3,6})\s*)?(?:,\s*(-?\d+)\s*)?\)/i);
    if (!match) {
      return null;
    }

    return {
      radius: parseInt(match[1], 10),
      background: normalizeHexColor(match[2] || '', 'FFFFFF')
    };
  }

  function clampToSliderRange(value, slider) {
    const min = parseInt(slider.min || '0', 10);
    const max = parseInt(slider.max || '100', 10);
    let next = Number.isFinite(value) ? value : min;
    next = Math.max(min, Math.min(max, next));
    return next;
  }

  function buildTransformWithSliders(originalValue, roundValue, roundBackgroundColor, shadowValue, shadowColor, shadowBackgroundColor, shadowFade) {
    const tokens = splitTopLevelExpressions(String(originalValue || ''));
    const nextTokens = [];
    let effectInsertIndex = -1;

    tokens.forEach(function(token) {
      if (/^round_edges\s*\(/i.test(token)) {
        if (effectInsertIndex === -1) {
          effectInsertIndex = nextTokens.length;
        }
        return;
      }

      if (/^drop_shadow\s*\(/i.test(token)) {
        if (effectInsertIndex === -1) {
          effectInsertIndex = nextTokens.length;
        }
        return;
      }

      nextTokens.push(token);
    });

    if (effectInsertIndex === -1) {
      effectInsertIndex = nextTokens.length;
    }

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
    if (/^none$/i.test(raw)) {
      return 'none';
    }

    const match = raw.match(/^\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*$/);
    if (!match) {
      return '';
    }

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
        if (insertAt === -1) {
          insertAt = nextTokens.length;
        }
        return;
      }

      nextTokens.push(token);
    });

    if (insertAt === -1) {
      insertAt = nextTokens.length;
    }

    if (normalized !== '' && normalized !== 'none') {
      nextTokens.splice(insertAt, 0, 'greyscale(' + normalized + ')');
    }

    return nextTokens.join(',');
  }

  function bindRangeValueDisplay() {
    const outputs = document.querySelectorAll('.range-current[data-for]');

    outputs.forEach(function(output) {
      const targetId = output.getAttribute('data-for');
      if (!targetId) {
        return;
      }

      const slider = document.getElementById(targetId);
      if (!slider) {
        return;
      }

      const updateOutput = function() {
        output.textContent = String(slider.value);
      };

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

      if (!roundSlider || !roundColorInput || !shadowSlider || !shadowFadeSlider || !shadowColorInput || !shadowBgColorInput) {
        return;
      }

      const syncSlidersFromTransform = function() {
        const transformValue = transformInput.value;
        const roundConfig = parseRoundEdgesConfig(transformValue);
        const shadowConfig = parseDropShadowConfig(transformValue);

        if (roundConfig && Number.isInteger(roundConfig.radius)) {
          roundSlider.value = String(clampToSliderRange(roundConfig.radius, roundSlider));
          roundColorInput.value = '#' + normalizeHexColor(roundConfig.background, 'FFFFFF').toLowerCase();
        } else {
          roundSlider.value = String(clampToSliderRange(0, roundSlider));
          roundColorInput.value = '#ffffff';
        }

        if (shadowConfig && Number.isInteger(shadowConfig.width)) {
          shadowSlider.value = String(clampToSliderRange(shadowConfig.width, shadowSlider));
          shadowFadeSlider.value = String(clampToSliderRange(shadowConfig.fade, shadowFadeSlider));
          shadowColorInput.value = '#' + normalizeHexColor(shadowConfig.color, '000000').toLowerCase();
          shadowBgColorInput.value = '#' + normalizeHexColor(shadowConfig.background, 'FFFFFF').toLowerCase();
        } else {
          shadowSlider.value = String(clampToSliderRange(0, shadowSlider));
          shadowFadeSlider.value = String(clampToSliderRange(65, shadowFadeSlider));
          shadowColorInput.value = '#000000';
          shadowBgColorInput.value = '#ffffff';
        }

        roundSlider.dispatchEvent(new Event('change'));
        shadowSlider.dispatchEvent(new Event('change'));
      };

      syncSlidersFromTransform();

      const syncTransform = function() {
        const roundValue = parseInt(roundSlider.value, 10) || 0;
        const shadowValue = parseInt(shadowSlider.value, 10) || 0;
        const shadowFade = parseInt(shadowFadeSlider.value, 10) || 65;
        transformInput.value = buildTransformWithSliders(transformInput.value, roundValue, roundColorInput.value, shadowValue, shadowColorInput.value, shadowBgColorInput.value, shadowFade);
      };

      transformInput.addEventListener('input', syncSlidersFromTransform);
      transformInput.addEventListener('change', syncSlidersFromTransform);
      transformInput.addEventListener('blur', syncSlidersFromTransform);
      roundSlider.addEventListener('input', syncTransform);
      roundSlider.addEventListener('change', syncTransform);
      roundColorInput.addEventListener('input', syncTransform);
      roundColorInput.addEventListener('change', syncTransform);
      shadowSlider.addEventListener('input', syncTransform);
      shadowSlider.addEventListener('change', syncTransform);
      shadowFadeSlider.addEventListener('input', syncTransform);
      shadowFadeSlider.addEventListener('change', syncTransform);
      shadowColorInput.addEventListener('input', syncTransform);
      shadowColorInput.addEventListener('change', syncTransform);
      shadowBgColorInput.addEventListener('input', syncTransform);
      shadowBgColorInput.addEventListener('change', syncTransform);
    });
  }

  function bindGreyscaleSelectColor() {
    const greyscaleSelects = document.querySelectorAll('select[name^="greyscale_"]');

    greyscaleSelects.forEach(function(select) {
      const updateSelectColor = function() {
        const value = String(select.value || '');
        const match = value.match(/^\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*$/);
        if (!match) {
          select.style.backgroundColor = '';
          select.style.color = '';
          return;
        }

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
      if (!transformInput) {
        return;
      }

      const syncTransformFromGreyscale = function() {
        transformInput.value = buildTransformWithGreyscale(transformInput.value, select.value);
      };

      select.addEventListener('change', syncTransformFromGreyscale);
    });
  }

  const STORAGE_KEY = 'bxImageMagickActiveTab';
  const EXPIRATION_MS = 1000 * 60 * 60; // 1 Stunde

  // Funktion zum Aktivieren eines Tabs
  function activateTab(tabId) {
    // Navigation
    tabs.forEach(t => t.classList.remove('active'));
    const activeTab = document.querySelector(`.magick-tabs .tab-nav a[href="${tabId}"]`);
    if (activeTab) activeTab.classList.add('active');

    // Inhalte
    contents.forEach(c => c.classList.remove('active'));
    const target = document.querySelector(tabId);
    if (target) {
      target.classList.add('active');

      const transformInput = target.querySelector('input[name^="transform_"]');
      if (transformInput) {
        transformInput.dispatchEvent(new Event('change'));
      }
    }
  }

  // Klick-Handler
  tabs.forEach(tab => {
    tab.addEventListener('click', function (e) {
      e.preventDefault();
      const tabId = this.getAttribute('href');
      activateTab(tabId);

      // Tab + Timestamp speichern
      const data = {
        tabId: tabId,
        timestamp: Date.now()
      };
      localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    });
  });

  // Letzten Tab beim Laden wiederherstellen (nur wenn noch gültig)
  const stored = localStorage.getItem(STORAGE_KEY);
  if (stored) {
    try {
      const data = JSON.parse(stored);
      if (Date.now() - data.timestamp < EXPIRATION_MS) {
        // noch gültig
        activateTab(data.tabId);
      } else {
        // abgelaufen -> löschen und ersten Tab aktivieren
        localStorage.removeItem(STORAGE_KEY);
        if (tabs.length > 0) {
          activateTab(tabs[0].getAttribute('href'));
        }
      }
    } catch (e) {
      // falls JSON ungültig -> reset
      localStorage.removeItem(STORAGE_KEY);
      if (tabs.length > 0) {
        activateTab(tabs[0].getAttribute('href'));
      }
    }
  } else if (tabs.length > 0) {
    // Standard: Ersten aktivieren
    activateTab(tabs[0].getAttribute('href'));
  }

  bindSliderTransformSync();
  bindRangeValueDisplay();
  bindGreyscaleSelectColor();
  bindGreyscaleTransformSync();

});

$(document).ready(function() {
  $(".fixed_messageStack").slideDown("slow", function() {
    setTimeout(function() { $(".fixed_messageStack").slideUp("slow"); }, 2000); 
  });
});
</script>
<?php
 }
?>