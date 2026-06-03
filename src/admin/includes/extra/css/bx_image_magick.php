<?php 
  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  if (basename($_SERVER['PHP_SELF']) == 'bx_image_magick.php') {
?>
<style>
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
    grid-template-columns: 270px 1fr;
    gap: 8px 12px;
    align-items: center;
  }

  .magick-settings-grid label {
    font-weight: bold;
    color: #333;
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

  .magick-settings-actions {
    margin-top: 12px;
    display: flex;
    gap: 8px;
  }

  .magick-preview-placeholder {
    margin-top: 8px;
    min-height: 180px;
    border: 1px dashed #aaa;
    border-radius: 4px;
    background: repeating-linear-gradient(45deg, #fafafa, #fafafa 10px, #f4f4f4 10px, #f4f4f4 20px);
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #666;
    padding: 10px;
  }

  .boxRight .contentTable {
    border: 1px solid #ccc;
  }

  .boxRight .contentTable:nth-child(even) {
    margin-bottom: 5px;
    border-top: none;
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