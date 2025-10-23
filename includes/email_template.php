<?php
require_once __DIR__ . '/../config/email_config.php';

function buildEmailTemplate($title, $content, $buttonText = null, $buttonLink = null) {
  $logoUrl = WEBSITE_URL . "/assets/img/LOGO.png";
  $buttonHtml = "";
  if ($buttonText && $buttonLink) {
    $buttonHtml = "
      <div style='text-align:center;margin-top:25px;'>
        <a href='$buttonLink' 
           style='background:#6366f1;color:#fff;padding:12px 25px;
                  border-radius:6px;text-decoration:none;font-weight:bold;'>
           $buttonText
        </a>
      </div>";
  }

  return "
  <div style='font-family:Arial,Helvetica,sans-serif;background:#f6f8fc;padding:20px;'>
    <div style='max-width:600px;margin:auto;background:#fff;border-radius:12px;
                box-shadow:0 2px 10px rgba(0,0,0,0.1);overflow:hidden;'>
      <div style='background:linear-gradient(135deg,#6366f1,#7c3aed);
                  padding:20px;text-align:center;'>
        <h1 style='color:#fff;margin:0;font-size:24px;font-weight:bold;'>NT2 Taalles International</h1>
      </div>

      <div style='padding:30px;color:#333;'>
        <h2 style='color:#4f46e5;margin-top:0;'>$title</h2>
        <div style='font-size:15px;line-height:1.6;'>$content</div>
        $buttonHtml
      </div>

      <div style='background:#f1f3f8;padding:15px;text-align:center;font-size:13px;color:#666;'>
        <p>© " . date('Y') . " NT2 Taalles International</p>
        <p>
          <a href='" . WEBSITE_URL . "' style='color:#6366f1;text-decoration:none;'>Website</a> |
          <a href='mailto:" . ADMIN_EMAIL . "' style='color:#6366f1;text-decoration:none;'>Contact us</a>
        </p>
      </div>
    </div>
  </div>";
}
?>
