# PHP

下記のようにブランチを切っています。
- feature/php-pre-challenge1<br>FizzBazz
- feature/php-pre-challenge2<br>バブルソート
- feature/php-pre-challenge3<br>GETパラメータ[target]から1以上の整数を受け取る<br>既にデータベースに保存されている数値の羅列を変数の配列に代入<br>GETパラメータで受け取った整数になる組み合わせを探し、JSON形式で出力<br>もし、GETパラメータ[target]値が1以上の整数ではない場合は、HTTP レスポンスステータスコード[400]で返却
- feature/php-challenge<br>掲示板への機能追加(リツイート機能といいね機能）
##### RT（リツイート）機能を実装
- ボタンを押下すると、元の投稿をRTすることができる
- ボタン押下を繰り返すと、RTの取り消しを行うことができる
- RTされた件数が分かる
※TwitterのコメントなしのRTと同様の挙動
##### いいね！ボタンを実装
- いいねボタンを押下すると、ボタンがいいね状態になる
- ボタン押下を繰り返すと、いいね数が+1、-1される（要は取り消しが可能）
- RTに対して いいね を押下した場合、RT元の投稿のいいね数が+1される。また、再びいいねボタンを押すとRT元の投稿のいいね数が-1される。

## ディレクトリ解説

```
quelcode-php
├── html ....................... ドキュメントルート
├── mysql5.7
│   ├── mysql .................. 起動すると作られる。データ永続化用
│   ├── mysqlvolume ............ mysqlコンテナにマウントされる。ホストとのファイル受け渡し用
│   └── my.cnf ................. mysqlコンテナの設定ファイル
├── php7.2
│   ├── Dockerfile ............. phpコンテナのDockerファイル
│   └── php.ini ................ phpの設定ファイル
├── .gitignore
├── docker-compose.yml
└── README.md
```

## データベース接続情報
MySQL バージョン 5.7.x


### コンテナ内部から接続する場合
```
host:mysql
port:3306
user:test
password:test
dbname:test
```

### Macから接続する場合
```
host:localhost
port:13306
user:test
password:test
dbname:test
```
