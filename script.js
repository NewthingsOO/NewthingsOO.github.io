document.addEventListener('DOMContentLoaded', () => {
  // 年号更新
  const y = document.getElementById('year');
  if (y) y.textContent = new Date().getFullYear();

  // 画像ライトボックス
  const lightbox = document.getElementById('lightbox');
  const lightImg = document.getElementById('lightbox-img');
  const closeBtn = document.querySelector('.lightbox-close');
  document.querySelectorAll('.grid-item').forEach(img => {
    img.addEventListener('click', () => {
      lightImg.src = img.src.replace(/\/\d+\/\d+$/, '/1200/800'); // だいたい大きめに
      lightImg.alt = img.alt || '拡大画像';
      lightbox.hidden = false;
      document.body.style.overflow = 'hidden';
    });
  });
  closeBtn.addEventListener('click', () => {
    lightbox.hidden = true;
    document.body.style.overflow = '';
  });
  lightbox.addEventListener('click', (e) => {
    if (e.target === lightbox) {
      lightbox.hidden = true;
      document.body.style.overflow = '';
    }
  });

  // フォームバリデーション
  const form = document.getElementById('contactForm');
  const msg = document.getElementById('formMsg');
  form.addEventListener('submit', (e) => {
    msg.textContent = '';
    const name = form.name.value.trim();
    const email = form.email.value.trim();
    const message = form.message.value.trim();
    const trap = form.website.value.trim();
    // 必須チェック
    if (!name || !email || !message) {
      e.preventDefault();
      msg.textContent = '必須項目を入力してください。';
      return;
    }
    // 簡易メール形式
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      e.preventDefault();
      msg.textContent = 'メールアドレスの形式が正しくありません。';
      return;
    }
    // スパム対策
    if (trap) {
      e.preventDefault();
      msg.textContent = '送信に失敗しました。もう一度お試しください。';
      return;
    }
    // 問題なければそのまま送信（PHPが処理）
  });
});
