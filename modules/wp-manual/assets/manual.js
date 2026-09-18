// タブ切り替え・検索・閲覧ログ
document.addEventListener('DOMContentLoaded', function () {
  var root = document.querySelector('.wpm');
  if (!root) return;

  var tabs    = root.querySelectorAll('.wpm-tab');
  var panels  = root.querySelectorAll('.wpm-panel');
  var groups  = root.querySelectorAll('.wpm-tabs__group');
  var firstId = root.getAttribute('data-first');
  var logged  = {};

  // ---------- 閲覧ログ（1ページ表示中は同じタブを1回だけ記録） ----------
  function logView(id) {
    if (!window.wpManual || logged[id]) return;
    logged[id] = true;
    var body = new FormData();
    body.append('action', 'wp_manual_log');
    body.append('nonce', wpManual.nonce);
    body.append('section', id);
    if (navigator.sendBeacon) {
      navigator.sendBeacon(wpManual.ajaxUrl, body);
    } else {
      fetch(wpManual.ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin' });
    }
  }

  // ---------- タブ ----------
  function activate(id, scroll) {
    var found = false;
    panels.forEach(function (p) {
      var match = p.getAttribute('data-panel') === id;
      p.hidden = !match;
      if (match) found = true;
    });
    if (!found) {
      return activate(firstId, false);
    }
    tabs.forEach(function (t) {
      var match = t.getAttribute('data-tab') === id;
      t.classList.toggle('is-active', match);
      t.setAttribute('aria-selected', match ? 'true' : 'false');
    });
    if (scroll) {
      root.querySelector('.wpm-main').scrollIntoView({ block: 'start', behavior: 'smooth' });
    }
    logView(id);
  }

  function fromHash() {
    var id = location.hash.replace('#', '');
    activate(id || firstId, false);
  }

  tabs.forEach(function (t) {
    t.addEventListener('click', function (e) {
      e.preventDefault();
      var id = t.getAttribute('data-tab');
      history.replaceState(null, '', '#' + id);
      activate(id, window.innerWidth < 960);
    });
  });

  root.addEventListener('click', function (e) {
    var a = e.target.closest('a.wpm-tabLink');
    if (!a) return;
    e.preventDefault();
    var id = a.getAttribute('href').replace('#', '');
    history.replaceState(null, '', '#' + id);
    activate(id, true);
  });

  window.addEventListener('hashchange', fromHash);
  fromHash();

  // ---------- 検索（タブ名 + 本文をインクリメンタル検索してタブを絞り込む） ----------
  var input = root.querySelector('.wpm-search__input');
  var count = root.querySelector('.wpm-search__count');
  var empty = root.querySelector('.wpm-search__empty');
  if (!input) return;

  // 各パネルの検索用テキストを事前に作る（編集フォーム内の文字は除く）
  var index = {};
  panels.forEach(function (p) {
    var clone = p.cloneNode(true);
    clone.querySelectorAll('.wpm-editBox, .wpm-panel__toggle, form').forEach(function (el) { el.remove(); });
    index[p.getAttribute('data-panel')] = clone.textContent.replace(/\s+/g, ' ').toLowerCase();
  });

  function normalize(str) {
    // 全角英数を半角に寄せ、大文字小文字を無視
    return str.replace(/[Ａ-Ｚａ-ｚ０-９]/g, function (c) {
      return String.fromCharCode(c.charCodeAt(0) - 0xFEE0);
    }).toLowerCase();
  }

  function search(q) {
    q = normalize(q.trim());
    var hits = 0;
    var firstHit = null;

    tabs.forEach(function (t) {
      var id = t.getAttribute('data-tab');
      var hit = q === '' || (index[id] || '').indexOf(q) !== -1;
      t.hidden = !hit;
      if (hit) {
        hits++;
        if (!firstHit) firstHit = id;
      }
    });

    groups.forEach(function (g) {
      var visible = g.querySelectorAll('.wpm-tab:not([hidden])').length > 0;
      g.hidden = !visible;
    });

    if (q === '') {
      count.textContent = '';
      empty.hidden = true;
      return;
    }
    count.textContent = hits + '件';
    empty.hidden = hits > 0;

    // 現在開いているタブが検索結果に無ければ、最初のヒットへ切り替える
    var active = root.querySelector('.wpm-tab.is-active');
    if (firstHit && (!active || active.hidden)) {
      activate(firstHit, false);
    }
  }

  var timer;
  input.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(function () { search(input.value); }, 120);
  });
  input.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      input.value = '';
      search('');
    }
  });
});
