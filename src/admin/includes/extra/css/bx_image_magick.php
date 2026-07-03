<?php 
  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  if (basename($_SERVER['PHP_SELF']) == 'bx_image_magick.php') {
?>
<style>
    div#bx_header {
      display: block; 
      background: #AF417E; 
      border-radius: 4px; 
      margin: 0 0 5px 0; 
      padding: 10px 0 6px 0;
    }

    div#bx_header .main {
      font-weight: bold;
      color: #fff;
      margin: 5px 10px;
      /*text-align: center; */
    }


  /* BX Image Magick Admin Styles */
  .magick-tabs .tab-nav {
    list-style: none; 
    padding: 0;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin:0;
  }
  .magick-tabs .tab-nav li a {
    padding: 6px 10px;
    background: #f1f1f1;
    border: 1px solid #ccc;
    border-bottom: none;
    display: inline-block;
    border-radius: 4px 4px 0 0;
    text-decoration: none;
    color: #222;
  }
  .magick-tabs .tab-nav li a.active {
    background: #AF417E;
    color: #fff;
    font-weight: bold;
  }
  .magick-tabs .tab-content {
    border-top: 1px solid #ccc;
  }
  .magick-tabs .tab-content > div {
    display: none;
    padding: 5px;
    border: 1px solid #ccc;
    background: #fff;
    border-top: none;
  }
  .magick-tabs .tab-content > div.active {
    display: block;
  }

  .magick-settings-wrap {
    padding: 4px;
  }

  .magick-workspace-tabs {
    display: grid;
    grid-template-columns: minmax(530px, 3fr) minmax(320px, 2fr);
    gap: 16px;
    align-items: start;
  }

  .magick-workspace-left,
  .magick-workspace-right {
    min-width: 0;
  }

  .magick-workspace-left .magick-inline-preview-panel {
    margin-top: 0;
    padding-top: 0;
    border-top: 0;
    position: sticky;
    top: 8px;
  }

  .magick-settings-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 8px;
    align-items: start;
  }

  .magick-settings-grid > label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: bold;
    color: #333;
    margin-top: 2px;
  }

  .magick-label-help {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 1px solid #AF417E;
    background: #fff;
    color: #AF417E;
    font-size: 11px;
    font-weight: bold;
    line-height: 1;
    cursor: help;
  }

  .magick-label-help-popup {
    position: absolute;
    top: 22px;
    left: 0;
    width: min(340px, 65vw);
    padding: 8px 10px;
    border: 1px solid #e4bfd4;
    border-radius: 4px;
    background: #fff7fb;
    color: #5d7282;
    font-size: 11px;
    font-weight: normal;
    line-height: 1.45;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
    z-index: 25;
    opacity: 0;
    pointer-events: none;
    transform: translateY(-2px);
    transition: opacity 0.16s ease, transform 0.16s ease;
  }

  .magick-label-help:hover .magick-label-help-popup,
  .magick-label-help:focus .magick-label-help-popup,
  .magick-label-help:focus-within .magick-label-help-popup {
    opacity: 1;
    pointer-events: auto;
    transform: translateY(0);
  }

  .magick-settings-grid input {
    padding: 6px 8px;
    border: 1px solid #bbb;
    border-radius: 3px;
  }

  .magick-settings-grid input.w100 {
    width: 100%;
    box-sizing: border-box;
  }

  .magick-settings-grid .range-control {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .magick-settings-grid .range-table {
    width: 100%;
    table-layout: fixed;
    border-collapse: collapse;
  }

  .magick-settings-grid .range-table-slider {
    width: auto;
    vertical-align: top;
    padding-right: 10px;
  }

  .magick-settings-grid .range-table-color {
    width: 54px;
    text-align: right;
    vertical-align: top;
  }

  .magick-settings-grid .range-table-current {
    width: 54px;
    text-align: right;
    vertical-align: middle;
    font-weight: bold;
    font-size: 16px;
  }

  .magick-settings-grid .range-control input[type="range"] {
    width: 100%;
    min-width: 0;
    padding: 0;
    border: 0;
    border-radius: 0;
  }

  .magick-settings-grid .range-control input[type="color"] {
    display: block;
    width: 44px;
    height: 28px;
    padding: 0;
    border: 0;
    border-radius: 3px;
    background: transparent;
    margin: 0 0 4px auto;
  }

  .magick-settings-grid .range-control input[type="color"]:last-child {
    margin-bottom: 0;
  }

  .magick-settings-grid .range-minmax {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: #666;
    line-height: 1;
  }

  .magick-settings-actions {
    margin-top: 12px;
    display: flex;
    gap: 8px;
  }

  .magick-inline-preview-panel {
    margin-top: 14px;
    padding-top: 10px;
    border-top: 1px solid #ddd;
  }

  @media (max-width: 1200px) {
    .magick-workspace-tabs {
      grid-template-columns: 1fr;
    }

    .magick-workspace-left .magick-inline-preview-panel {
      position: static;
    }
  }

  @media (max-width: 900px) {
    .magick-settings-grid {
      grid-template-columns: 1fr;
      gap: 8px;
    }

    .magick-settings-grid > label {
      margin-top: 2px;
    }

    .magick-merge-positioner-row,
    .magick-merge-positioner-row-overlay {
      grid-template-columns: 1fr;
      gap: 4px;
    }

    .magick-merge-positioner-value {
      text-align: left;
    }
  }

  .magick-inline-preview-title {
    margin-bottom: 8px;
    color: #333;
  }

  .magick-preview-empty {
    font-size: 12px;
    color: #666;
    padding: 10px;
    text-align: center;
  }

  .magick-preview-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    width: 100%;
    margin-top: 8px;
    min-height: 240px;
    border: 1px dashed #aaa;
    border-radius: 4px;
    background: repeating-linear-gradient(45deg, #fafafa, #fafafa 10px, #f4f4f4 10px, #f4f4f4 20px);
    color: #666;
    padding: 26px 16px 16px 36px;
    box-sizing: border-box;
  }

  .magick-preview-image-area {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: fit-content;
    max-width: 100%;
    margin: 0 auto;
    overflow: hidden;
  }

  /* Die Breiten-Anzeige (oben) */
  .magick-preview-placeholder::before {
    content: var(--img-width-label, "Max: 0px | Ist: 0px");
    position: absolute;
    top: 8px;
    left: 50%;
    transform: translateX(-50%);
    font-family: sans-serif;
    font-size: 12px;
    font-weight: bold;
  }

  /* Die Höhen-Anzeige (links, vertikal) */
  .magick-preview-placeholder::after {
    content: var(--img-height-label, "Max: 0px | Ist: 0px");
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%) rotate(180deg);
    font-family: sans-serif;
    font-size: 12px;
    font-weight: bold;
    writing-mode: vertical-lr; 
  }

  .magick-preview-image-area img {
    max-width: 100%;
    width: auto;
    height: auto;
    display: block;
  }

  .magick-preview-samples {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #e2e2e2;
  }

  .magick-preview-samples-title {
    margin-bottom: 8px;
    font-size: 12px;
    font-weight: bold;
    color: #555;
  }

  .magick-preview-samples-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }

  .magick-preview-sample {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    min-width: 92px;
    padding: 8px;
    border: 1px solid #cfcfcf;
    border-radius: 4px;
    background: #fafafa;
    color: #333;
    cursor: pointer;
    text-align: center;
  }

  .magick-preview-sample:hover {
    border-color: #AF417E;
    background: #fff4fa;
    color: #AF417E;
  }

  .magick-preview-sample.is-active {
    border-color: #AF417E;
    background: #ffe9f5;
    box-shadow: inset 0 0 0 1px #AF417E;
  }

  .magick-preview-sample-thumb {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 64px;
    height: 48px;
    overflow: hidden;
    background: repeating-linear-gradient(45deg, #efefef, #efefef 10px, #f8f8f8 10px, #f8f8f8 20px);
    border: 1px solid #ddd;
    border-radius: 3px;
  }

  .magick-preview-sample-thumb img {
    max-width: 100%;
    max-height: 100%;
    display: block;
  }

  .magick-preview-sample-label {
    display: block;
    font-size: 11px;
    font-weight: bold;
    line-height: 1.2;
  }

  /* 1. Der Hinweis ist standardmäßig unsichtbar */
  .magick-size-warning {
      display: none;
      margin-top: 8px;
      padding: 6px 10px;
      background-color: #fff3cd; /* Dezentes Gelb/Orange für Warnungen */
      border: 1px solid #ffeeba;
      color: #856404;
      border-radius: 4px;
      font-size: 12px;
      text-align: center;
  }
/* .magick-size-warning
   .magick-preview-placeholder.is-scaled */

  /* 2. NUR wenn der Container die Klasse hat, wird der Hinweis eingeblendet */
  .magick-preview-placeholder.is-scaled + .magick-size-warning {
      display: block;
  }
  
  .current-range {
    font-size: 1.0rem;
    font-weight: bold;
    color: #666;
    margin: 5px 0;
  } 

  .magick-inline-note {
    padding: 6px 8px;
    border-left: 3px solid #AF417E;
    background: #ffe9f5;
    color: #5d7282;
    font-size: 11px;
    line-height: 1.45;
    border-radius: 3px;
  }

  .magick-merge-positioner {
    border: 1px solid #d7d7d7;
    border-radius: 4px;
    padding: 8px;
    background: #fdfdfd;
  }

  .magick-merge-positioner-canvas {
    position: relative;
    width: 420px;
    height: 220px;
    max-width: 100%;
    border: 1px dashed #b9b9b9;
    border-radius: 4px;
    overflow: hidden;
    background: repeating-linear-gradient(45deg, #f7f7f7, #f7f7f7 10px, #efefef 10px, #efefef 20px);
  }

  .magick-merge-positioner-preview {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    display: block;
    object-fit: fill;
    pointer-events: none;
    z-index: 1;
  }

  .magick-merge-positioner-handle {
    position: absolute;
    top: 0;
    left: 0;
    width: 96px;
    height: 64px;
    border: 0;
    background: transparent;
    color: transparent;
    font-size: 0;
    font-weight: normal;
    display: block;
    box-sizing: content-box;
    cursor: grab;
    user-select: none;
    touch-action: none;
    z-index: 2;
  }

  .magick-merge-positioner-handle:active {
    cursor: grabbing;
  }

  .magick-merge-positioner-controls {
    margin-top: 8px;
    display: grid;
    grid-template-columns: 1fr;
    gap: 6px;
  }

  .magick-merge-positioner-row {
    display: grid;
    grid-template-columns: 26px 1fr 40px;
    align-items: center;
    gap: 8px;
  }

  .magick-merge-positioner-row label {
    margin: 0;
    font-weight: bold;
    font-size: 11px;
    color: #555;
  }

  .magick-merge-positioner-row input[type="range"] {
    width: 100%;
    min-width: 0;
    padding: 0;
    border: 0;
    border-radius: 0;
  }

  .magick-merge-positioner-row-overlay {
    grid-template-columns: 60px 1fr 0;
  }

  .magick-preview-image-area .magick-merge-positioner-handle {
    z-index: 3;
  }

  .magick-merge-positioner-row select {
    width: 100%;
    min-width: 0;
    padding: 4px 6px;
    border: 1px solid #bbb;
    border-radius: 3px;
    background: #fff;
    color: #333;
    box-sizing: border-box;
  }

  .magick-merge-positioner-value {
    text-align: right;
    font-family: monospace;
    font-size: 14px;
    font-weight: bold;
    color: #333;
  }

  /* Future: Modal (Image Magick device management / diagnostics) */
  #magickModal {
    display: none; 
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
  }
  #magickModal .modal-content {
    padding: 0; 
    border: 1px solid #aaa; 
    background-color: #fff; 
    font-family: Arial, sans-serif; 
    font-size: 14px; 
    margin: auto; 
    box-shadow: 0 2px 5px rgba(0,0,0,0.25);
    width: 400px; 
    margin-top: 10%;
    border-radius: 8px;
    overflow: hidden;
  }
  #magickModal .modal-content h3 {
    margin: 0;
    padding: 8px 15px;
    background-color: #AF417E;
    color: white;
    font-size: 16px;
    font-weight: bold;
  }

  #magickModal .modal-content > div {
    padding: 15px;
  }

  #magickModal .modal-content > div > div {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
  }
  #magickModal .modal-content > div > div > label,
  #magickModal .modal-content > div > div > span {
    width: 120px; 
    flex-shrink: 0; 
    margin-right: 10px; 
    font-weight: bold;
  }
  #magickModal .modal-content > div > div > input {
    flex-grow: 1; 
    padding: 6px; 
    border: 1px solid #aaa;
  }
  #magickModal #result_output {
    color: #AF417E; 
    flex-grow: 1; 
    padding: 6px; 
    border: 1px solid #aaa; 
    text-align: center;
    background-color: #f9f9f9;
  }
  #magickModal .close {
    color: white;
    float: right;
    font-size: 20px;
    font-weight: bold;
    cursor: pointer;
  }
  #magickModal .close:hover,
  #magickModal .close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
  }

  /* Future: fixed message stack (animated via JS) */
  .fixed_messageStack {
    position: fixed;
    top: 88px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1000;
    width: 80%;
    padding: 10px 0;
    text-align: center;
    display: none;
  }

  button.but_red {
    color:#ddd;
    background-color:#bc2525;
    border-color: #a81e1e;
  }
  button.but_red:hover {
    color:#fff;
    background-color:#a91f1f;
    border-color: #991919;
    text-decoration:none !important;
    cursor:pointer;
  }

  .magick-preview-workspace {
    display: grid;
    grid-template-columns: minmax(260px, 320px) 1fr;
    gap: 16px;
    align-items: start;
  }

  .magick-preview-upload-panel {
    border: 1px solid #d8d8d8;
    border-radius: 4px;
    padding: 10px;
    background: #fafafa;
  }

  .magick-preview-main-panel {
    border-left: 1px solid #d8d8d8;
    padding-left: 16px;
  }

  .magick-preview-stage {
    border: 1px solid #d8d8d8;
    border-radius: 4px;
    padding: 10px;
    background: #fff;
    margin-bottom: 14px;
  }

  .magick-preview-stage-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
  }

  .magick-preview-source-switch {
    display: inline-flex;
    gap: 6px;
  }

  .magick-preview-source-switch .button {
    min-height: auto;
    padding: 4px 10px;
    line-height: 1.25;
  }

  .magick-preview-source-switch .button.is-active {
    border-color: #AF417E;
    background: #ffe9f5;
    color: #7f1958;
    font-weight: bold;
  }

  .magick-preview-stage-canvas {
    min-height: 260px;
    border: 1px dashed #b9b9b9;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: repeating-linear-gradient(45deg, #f7f7f7, #f7f7f7 10px, #efefef 10px, #efefef 20px);
  }

  #preview-file-stage-image {
    display: block;
    max-width: 100%;
    max-height: 360px;
    width: auto;
    height: auto;
    border-radius: 4px;
  }

  .magick-preview-stage-empty {
    color: #666;
    font-size: 13px;
    text-align: center;
    padding: 12px;
    line-height: 1.45;
  }

  .magick-preview-stage-caption {
    margin-top: 8px;
    color: #555;
    font-size: 12px;
    word-break: break-all;
  }

  .magick-gallery-panel h3 {
    margin-bottom: 8px;
  }

  .magick-gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 10px;
  }

  .magick-gallery-item {
    border: 1px solid #d8d8d8;
    border-radius: 4px;
    padding: 8px;
    background: #fff;
  }

  .magick-gallery-item.is-selected {
    border-color: #AF417E;
    box-shadow: inset 0 0 0 1px #AF417E;
    background: #fff5fb;
  }

  .magick-gallery-thumb {
    display: block;
    width: 100%;
    padding: 4px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #fff;
    cursor: pointer;
    margin: 0 0 6px 0;
  }

  .magick-gallery-thumb img {
    display: block;
    width: 100%;
    height: 88px;
    object-fit: contain;
  }

  .magick-gallery-name {
    font-size: 11px;
    line-height: 1.25;
    color: #444;
    margin-bottom: 6px;
    min-height: 28px;
    word-break: break-all;
  }

  .magick-gallery-action {
    display: block;
    width: 100%;
    margin-bottom: 4px;
    min-height: auto;
    line-height: 1.2;
    padding: 3px 5px;
  }

  .magick-gallery-item .magick-gallery-action:last-child {
    margin-bottom: 0;
  }

  @media (max-width: 1220px) {
    .magick-preview-workspace {
      grid-template-columns: 1fr;
    }

    .magick-preview-main-panel {
      border-left: 0;
      border-top: 1px solid #d8d8d8;
      padding-left: 0;
      padding-top: 12px;
    }
  }
</style>
<?php } ?>