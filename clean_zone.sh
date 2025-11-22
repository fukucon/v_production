#!/bin/bash
# Zone.Identifierファイルを削除するスクリプト

echo "Zone.Identifierファイルを検索中..."
count=$(find . -name "*:Zone.Identifier" 2>/dev/null | wc -l)

if [ $count -eq 0 ]; then
    echo "Zone.Identifierファイルは見つかりませんでした。"
else
    echo "$count 個のZone.Identifierファイルを削除します..."
    find . -name "*:Zone.Identifier" -delete 2>/dev/null
    echo "✓ 削除完了"
fi
