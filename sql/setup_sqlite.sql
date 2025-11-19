-- KaleidoChrome ローカル開発用データベース（SQLite）
-- 作成日: 2025-11-18

-- ==========================================
-- 1. 管理者アカウントテーブル
-- ==========================================
CREATE TABLE IF NOT EXISTS admin_users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT NOT NULL UNIQUE,
  password TEXT NOT NULL,
  email TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  last_login DATETIME
);

CREATE INDEX IF NOT EXISTS idx_username ON admin_users(username);

-- ==========================================
-- 2. ブログ記事テーブル
-- ==========================================
CREATE TABLE IF NOT EXISTS posts (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT NOT NULL,
  slug TEXT NOT NULL UNIQUE,
  content TEXT NOT NULL,
  excerpt TEXT,
  featured_image TEXT,
  status TEXT DEFAULT 'draft' CHECK(status IN ('draft', 'published', 'archived')),
  author_id INTEGER NOT NULL,
  view_count INTEGER DEFAULT 0,
  published_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (author_id) REFERENCES admin_users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_slug ON posts(slug);
CREATE INDEX IF NOT EXISTS idx_status ON posts(status);
CREATE INDEX IF NOT EXISTS idx_published_at ON posts(published_at);
CREATE INDEX IF NOT EXISTS idx_author ON posts(author_id);

-- ==========================================
-- 3. 画像管理テーブル
-- ==========================================
CREATE TABLE IF NOT EXISTS images (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  filename TEXT NOT NULL,
  original_name TEXT,
  file_path TEXT NOT NULL,
  file_size INTEGER,
  mime_type TEXT,
  alt_text TEXT,
  description TEXT,
  uploaded_by INTEGER NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (uploaded_by) REFERENCES admin_users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_filename ON images(filename);
CREATE INDEX IF NOT EXISTS idx_uploaded_by ON images(uploaded_by);

-- ==========================================
-- 初期データ投入
-- ==========================================

-- 管理者アカウント作成（パスワード: admin123）
INSERT INTO admin_users (username, password, email) VALUES
('admin', '$2y$10$9jkH98lYedTuO27C/6CJ..wTHyGw3OBuIB2R1STFu9IGi4UgOL4kK', 'admin@kaleidochrome.com');

-- サンプル記事
INSERT INTO posts (title, slug, content, excerpt, status, author_id, published_at) VALUES
(
  'KaleidoChromeへようこそ！',
  'welcome-to-kaleidochrome',
  '<p>KaleidoChromeの公式ブログへようこそ！</p><p>このブログでは、所属ライバーの活動情報や事務所からのお知らせなどを発信していきます。</p><p>今後とも、KaleidoChromeをよろしくお願いいたします。</p>',
  'KaleidoChromeの公式ブログがスタートしました。所属ライバーの活動情報や事務所からのお知らせを発信していきます。',
  'published',
  1,
  datetime('now')
);
