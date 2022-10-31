## 使用说明

1. 登陆glados。

2. 查看**cookie**。

   ![查看cookie](https://gitee.com/zach_li/picture/raw/master/typora/image-20221029134327642.png)

3. 修改脚本里的**cookie**。

4. 定时任务每天执行脚本（php5.3~php7.4，其它版本没有测试）。

   `0 1 * * *       php /home/deploy/shell/qiandao_glados.php`

5. 日志位置。

   ![日志位置](https://gitee.com/zach_li/picture/raw/master/typora/image-20221029134541689.png)



## 其它版本

同事写的shell版本在 [这里](https://github.com/catindog/glados)。



