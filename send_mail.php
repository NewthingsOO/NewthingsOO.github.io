<?php
// ==========================================
// お問い合わせ送信スクリプト (UTF-8 / PHP 7+)
// - メール送信（mb_send_mail）
// - 送信内容のCSV保存（メール不可のサーバーでも安心）
// ==========================================

mb_language("Japanese");
mb_internal_encoding("UTF-8");

// ★★ ここをあなたの受信アドレスに変更してください ★★
$to = "your-email@example.com";

// セキュリティ: POST以外は拒否
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo "Method Not Allowed";
  exit;
}

// 入力取得 & サニタイズ
$name    = trim((string)($_POST['name'] ?? ''));
$email   = trim((string)($_POST['email'] ?? ''));
$subject = trim((string)($_POST['subject'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));
$website = trim((string)($_POST['website'] ?? '')); // honeypot

// バリデーション
$errors = [];
if ($website !== '') { // ボット疑い
  $errors[] = 'spam';
}
if ($name === '') {
  $errors[] = 'name';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $errors[] = 'email';
}
if ($message === '') {
  $errors[] = 'message';
}

if (!empty($errors)) {
  http_response_code(400);
  echo "<!DOCTYPE html><html lang='ja'><meta charset='UTF-8'><body>送信内容に不備があります。<br><a href='index.html#contact'>戻る</a></body></html>";
  exit;
}

// 件名
$siteTitle = 'My Hobby お問い合わせ';
$mailSubject = ($subject !== '' ? "[{$siteTitle}] " . $subject : "[{$siteTitle}] お問い合わせ");

// 本文
$body = "以下の内容でお問い合わせを受け付けました。\n\n"
      . "お名前: {$name}\n"
      . "メール: {$email}\n"
      . "件名: " . ($subject !== '' ? $subject : '(なし)') . "\n"
      . "----\n"
      . "{$message}\n"
      . "----\n"
      . "送信日時: " . date('Y-m-d H:i:s') . "\n"
      . "送信元IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";

// 送信者情報（Return-Path対策はサーバー設定に依存）
$headers  = "From: {$name} <{$email}>\r\n";
$headers .= "Reply-To: {$email}\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// メール送信（失敗してもCSV保存は行う）
$sent = false;
if ($to !== 'your-email@example.com') {
  $sent = mb_send_mail($to, $mailSubject, $body, $headers);
}

// CSV保存（追加書き込み）
$csvFile = __DIR__ . '/submissions.csv';
$fp = fopen($csvFile, 'a');
if ($fp) {
  $row = [
    date('c'),
    $name,
    $email,
    $subject,
    preg_replace('/[\r\n]+/', ' ', $message),
    $_SERVER['REMOTE_ADDR'] ?? ''
  ];
  fputcsv($fp, $row);
  fclose($fp);
}

// レスポンス（サンクスページ）
$ok = $sent || true; // メール送れなくても保存できたらOK表示にする
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>送信完了 | My Hobby</title>
  <style>
    body { font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Noto Sans JP', 'Hiragino Kaku Gothic ProN', 'Yu Gothic', 'Meiryo', sans-serif; padding: 32px; line-height: 1.7; }
    .card { max-width: 720px; margin: 0 auto; border: 1px solid #eee; border-radius: 16px; padding: 24px; box-shadow: 0 6px 20px rgba(0,0,0,0.06); }
    a.button { display: inline-block; padding: 10px 16px; border-radius: 999px; background: #2563eb; color: white; text-decoration: none; font-weight: 700; }
  </style>
</head>
<body>
  <div class="card">
    <h1>送信ありがとうございます</h1>
    <p>お問い合わせを受け付けました。折り返しご連絡いたします。</p>
    <ul>
      <li>お名前：<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></li>
      <li>メール：<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></li>
      <li>件名　：<?php echo htmlspecialchars($subject !== '' ? $subject : '(なし)', ENT_QUOTES, 'UTF-8'); ?></li>
    </ul>
    <p><a class="button" href="index.html#contact">戻る</a></p>
    <p style="color:#6b7280;">※ メール通知先を変更するには <code>send_mail.php</code> の <code>$to</code> を修正してください。</p>
  </div>
</body>
</html>
