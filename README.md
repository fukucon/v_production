# KaleidoChrome VTuber Agency Website

個性が輝く無限の可能性 - IRIAM・VTuber事務所のホームページ

## 🌐 サイトの確認方法

### 方法1: ファイルを直接開く
1. `index.html` ファイルを右クリック
2. 「プログラムから開く」→ お好みのブラウザを選択
3. または、ブラウザのアドレスバーにファイルパスを入力：
   ```
   file:///home/user/v_production/index.html
   ```

### 方法2: ローカルサーバーで起動
```bash
# Python 3がインストールされている場合
cd /home/user/v_production
python3 -m http.server 8000

# Node.jsがインストールされている場合
npx http-server -p 8000
```

ブラウザで `http://localhost:8000` にアクセス

### 方法3: オンラインで公開

#### GitHub Pages
1. GitHubにリポジトリを作成
2. ファイルをプッシュ
3. Settings → Pages で公開設定

#### Netlify（推奨）
1. https://netlify.com にアクセス
2. フォルダをドラッグ&ドロップ
3. 即座に公開URL取得

## 🎨 サイトの特徴

- ✨ 万華鏡テーマの動的アニメーション
- 🎯 パーティクルバックグラウンド
- 📱 フルレスポンシブデザイン
- 🚀 スムーズなスクロールとパララックス効果
- 💫 3Dホバーエフェクト
- 🎮 イースターエッグ: コナミコード (↑↑↓↓←→←→BA)

## 📂 ファイル構成

```
.
├── index.html   # メインHTML
├── styles.css   # スタイルシート
├── script.js    # JavaScript機能
└── README.md    # このファイル
```

## 🎬 使用技術

- HTML5
- CSS3 (アニメーション、グラデーション、レスポンシブ)
- Vanilla JavaScript (ES6+)
- Canvas API (パーティクルアニメーション)

---

**KaleidoChrome** - 個性が輝く無限の可能性 🌈
