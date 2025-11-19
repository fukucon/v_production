-- タレントテーブルにname_kanaカラムを追加
ALTER TABLE talents ADD COLUMN name_kana VARCHAR(100) NULL COMMENT 'タレント名（かな）' AFTER name;
