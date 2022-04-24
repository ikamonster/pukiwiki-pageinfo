# PukiWiki用プラグイン<br>ページ情報表示 pageinfo.inc.php

ページ情報を表示するPukiWiki用プラグイン。

|対象PukiWikiバージョン|対象PHPバージョン|
|:---:|:---:|
|PukiWiki 1.5.3 ~ 1.5.4 (UTF-8)|PHP 7.4 ~ 8.1|

## インストール

下記GitHubページからダウンロードした pageinfo.inc.php を PukiWiki の plugin ディレクトリに配置してください。

[https://github.com/ikamonster/pukiwiki-pageinfo](https://github.com/ikamonster/pukiwiki-pageinfo)

## 使い方

```
#pageinfo([label])
&pageinfo([label]);
```

label … ページ情報表示へのリンクラベル。省略するとアイコン画像リンクを出力

## 使用例

```
&pageinfo(ページ情報);
```

## 設定

ソース内の下記の定数で動作を制御することができます。

|定数名|値|既定値|意味|
|:---|:---:|:---:|:---|
|PLUGIN_PAGEINFO_SHOW_PAGE_FUNCTIONS| 0 or 1| 0|ページ操作ツールを表示|
|PLUGIN_PAGEINFO_SHOW_GENERAL_FUNCTIONS| 0 or 1| 0|一般ツールを表示|
|PLUGIN_PAGEINFO_SHOW_ATTACHEDFILES| 0 or 1| 1|添付ファイルリストを表示|
|PLUGIN_PAGEINFO_SHOW_RELATEDPAGES| 0 or 1| 1|関連ページリストを表示|
|PLUGIN_PAGEINFO_SHOW_BASICINFO| 0 or 1| 1|ページ基本情報を表示|
|PLUGIN_PAGEINFO_SHOW_VIEWS| 0 or 1| 0|ページ閲覧回数を表示（counter標準プラグインが設置されていなければ無意味）|
|PLUGIN_PAGEINFO_SHOW_PROTECTION| 0 or 1| 1|ページ保護情報を表示|
|PLUGIN_PAGEINFO_SHOW_CMSINFO| 0 or 1| 0|CMS（PukiWiki）情報を表示|
|PLUGIN_PAGEINFO_SHOW_SERVERINFO| 0 or 1| 0|サーバー情報を表示|
