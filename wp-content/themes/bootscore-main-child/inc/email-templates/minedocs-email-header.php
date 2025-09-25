<?php
$subject = $args['subject'];
$body = $args['body'];

// Assicuriamoci che il contenuto sia HTML
if (!strpos($body, '<html')) {
    $body = '<div>' . $body . '</div>';
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?php echo $subject; ?></title>
  <style>
    body {
      margin: 0;
      padding: 0;
      background-color: #f6f8fa;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .email-container {
      max-width: 600px;
      margin: 0 auto;
      background-color: #ffffff;
      border: 1px solid #e1e4e8;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
    }
    .email-header {
      background-color: #1a202c;
      color: #ffffff;
      padding: 30px 20px;
      text-align: center;
    }
    .email-header img {
      max-height: 50px;
      margin-bottom: 10px;
    }
    .email-header h1 {
      margin: 0;
      font-size: 22px;
    }
    .email-body {
      padding: 30px 20px;
      color: #2d3748;
      line-height: 1.6;
    }
    .email-footer {
      background-color: #2d3748;
      color: #ffffff;
      text-align: center;
      padding: 15px 10px;
      font-size: 12px;
    }
    a.button {
      display: inline-block;
      padding: 12px 24px;
      margin-top: 20px;
      background-color: #3182ce;
      color: #ffffff;
      text-decoration: none;
      border-radius: 6px;
      font-weight: bold;
    }
    a.button:hover {
      background-color: #2b6cb0;
    }
  </style>
</head>
<body>
  <div class="email-container">
    <div class="email-header">
      <img src="<?php echo MINEDOCS_LOGO; ?>" alt="Minedocs">
      <h1><?php echo $subject; ?></h1>
    </div>
    <div class="email-body">
      <?php echo $body; ?>
    </div>
    <div class="email-footer">
      &copy; <?php echo date('Y'); ?> Minedocs. Tutti i diritti riservati.<br>
      <?php echo home_url(); ?>
    </div>
  </div>
</body>
</html>
