-- タレントテーブルにslugカラムを追加
ALTER TABLE talents ADD COLUMN slug TEXT UNIQUE;

-- 既存のタレントに対してIDベースのスラッグを設定
-- （後から手動で変更可能）
UPDATE talents SET slug = 'talent-' || id WHERE slug IS NULL;

-- slugカラムにユニークインデックスを作成（既にUNIQUE制約があるが明示的に）
CREATE UNIQUE INDEX IF NOT EXISTS idx_talent_slug ON talents(slug);
