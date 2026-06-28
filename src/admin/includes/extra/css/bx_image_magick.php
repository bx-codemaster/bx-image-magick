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

  .magick-settings-grid {
    display: grid;
    grid-template-columns: 200px 1fr 1fr;
    gap: 8px 12px;
    align-items: start;
  }

  .magick-settings-grid label {
    font-weight: bold;
    color: #333;
    margin-top: 5px;
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

  .magick-preview-placeholder {
    display: inline-grid;
    width: 100%;
    /* Spalte 1: Breite für die Höhe (auto), Spalte 2: Bildbereich (1fr) */
    grid-template-columns: auto 1fr; 
    /* Reihe 1: Breite für die Breite (auto), Reihe 2: Bildbereich (1fr) */
    grid-template-rows: auto 1fr;
    gap: 8px; /* Abstand zwischen Texten und Bild */
    align-items: center;
    justify-items: center;
    margin-top: 8px;
    min-height: 180px;
    border: 1px dashed #aaa;
    border-radius: 4px;
    background: repeating-linear-gradient(45deg, #fafafa, #fafafa 10px, #f4f4f4 10px, #f4f4f4 20px);
    color: #666;
    padding: 10px;
  }

  /* Die Breiten-Anzeige (oben) */
  .magick-preview-placeholder::before {
     content: var(--img-width, "0px"); /* Dynamisch via JS oder statischer Text */
    grid-column: 2;          /* Sitzt in der zweiten Spalte (über dem Bild) */
    grid-row: 1;             /* Erste Reihe */
    font-family: sans-serif;
    font-size: 12px;
    font-weight: bold;
  }

  /* Die Höhen-Anzeige (links, vertikal) */
  .magick-preview-placeholder::after {
    content: var(--img-height, "0px");  /* Dynamisch via JS oder statischer Text */
    grid-column: 1;          /* Erste Spalte (links vom Bild) */
    grid-row: 2;             /* Zweite Reihe */
    font-family: sans-serif;
    font-size: 12px;
    font-weight: bold;
    
    /* Der CSS-Trick für vertikalen Text */
    writing-mode: vertical-lr; 
    transform: rotate(180deg); /* Dreht den Text, damit er von unten nach oben liest (optional) */
  }

  /* Dein dynamisches Bild (oder der aktuelle Inhalt) */
  .magick-preview-placeholder img {
    grid-column: 2;          /* Bild nutzt die Hauptzelle */
    grid-row: 2;
    max-width: 100%;
    height: auto;
    display: block;
  }

  /* 1. Der Hinweis ist standardmäßig unsichtbar */
  td.infoBoxContent .magick-size-warning {
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
/* td.infoBoxContent div.magick-size-warning
   td.infoBoxContent div.magick-preview-placeholder.is-scaled */

  /* 2. NUR wenn der Container die Klasse hat, wird der Hinweis eingeblendet */
  td.infoBoxContent .magick-preview-placeholder.is-scaled + .magick-size-warning {
      display: block;
  }
  
  .boxRight .contentTable {
    border: 1px solid #ccc;
  }

  .boxRight .contentTable:nth-child(even) {
    margin-bottom: 5px;
    border-top: none;
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

</style>
<?php } ?>