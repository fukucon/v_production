-- タレントテーブルにslugカラムを追加（MySQL用）

-- slugカラムを追加
ALTER TABLE talents ADD COLUMN slug VARCHAR(200) UNIQUE AFTER name_kana;

-- 既存のタレントに対してIDベースのスラッグを設定
UPDATE talents SET slug = CONCAT('talent-', id) WHERE slug IS NULL;

-- slugカラムにユニークインデックスを作成
CREATE UNIQUE INDEX idx_talent_slug ON talents(slug);
