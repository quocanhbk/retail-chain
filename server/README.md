# bkrm
## Hướng dẫn cài đặt
- Cài đặt [Xampp](https://www.apachefriends.org/index.html)
- Clone cả thư mục vào htdocs trong thư mục cài đặt xampp
- Add database vào (1 trong 2)
  - db.sql là cái db cũ
  - bkrm.sql là cái mới
- Vào .env edit lại 3 dòng
```
DB_DATABASE = tên database
DB_USERNAME = tên user
DB_PASSWORD = password
```
- Bật Xampp, url truy cập vào web:
```
http://localhost/bkrm/webapp/public/
```
- Nếu có lỗi, tải [Composer](https://getcomposer.org/) về
- Mở cmd, cd đến thư mục webapp, gõ câu lệnh:
```
composer update --no-scripts
```
## API doc: 
https://docs.google.com/document/d/1dAEK4TxFoLA-5LKK5UDeWBKphXu9sZc1RZD2lM95L3A/edit?usp=sharing
