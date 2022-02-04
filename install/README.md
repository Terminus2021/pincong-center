#如何安装 Pincong Center
原帖在此：https://pincong.rocks/article/684  
——————————————————————————————————————————————————————————————————————————————————————————————————  
第一步：确保你的服务器运行了PHP和MySQL服务。  
第二步：使用root账户登入MySQL，并创建一个新的数据库，名称自定。  
第三步：下载Pincong Center源代码，并上载至网站空间。  
第四步：修改 database.php 中的账号、密码、数据库名信息。  
第五步：先后导入 install/db/ 下的 tables.sql、settings.sql及admin.sql，记住需要开 --default-character-set=utf8。  
第六步：安装后登入admin。默认密码为admin，登入后进入dashboard进行更多操作。
