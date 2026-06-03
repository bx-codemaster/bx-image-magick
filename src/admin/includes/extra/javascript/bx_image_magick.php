<?php
  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  if ( defined('MODULE_BX_IMAGE_MAGICK_STATUS') && 'True' == MODULE_BX_IMAGE_MAGICK_STATUS  && basename($_SERVER['PHP_SELF']) == 'bx_image_magick.php') {
?>
<script>
"use strict";
document.addEventListener('DOMContentLoaded', function () {
  const tabs = document.querySelectorAll('.magick-tabs .tab-nav a');
  const contents = document.querySelectorAll('.magick-tabs .tab-content > div');

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
    if (target) target.classList.add('active');
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