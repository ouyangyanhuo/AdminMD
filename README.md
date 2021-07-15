# AdminMD

## 介绍

为Typecho博客程序专门美化的后台,框架主要采用MDUI

## 更新日志:

1.由覆盖式模板改为插件式后台模板，一定程度上增强了兼容性

2.新增了自定义后台登录页面一键自定义

3.修复了部分已知Bug

4.新增了部分未知Bug(理论上是完美移植为插件版)

## 编程语言
PHP

## 安装教程

导入到Typecho程序 usr/plugins/ 目录中，并解压

解压后文件夹名称必须为AdminMD

## 使用须知

1.当前版本中仍存在部分不影响使用的未知Bug，一旦发现请务必反馈

2.后台登录页面的背景图可以在插件设置中进行更改

3.由于 Typecho 程序默认使用的 Gravatar 官方的头像线路已被 GWF 屏蔽，因此会导致使用本主题时使后台完全加载缓慢。

#### 解决 使用须知->3 问题的方法

解决方法

修改 Typecho 程序源代码

需要修改文件地址：网站根目录 ``/var/Typecho/Common.php``第 937 行

修改前：

```
$url = $isSecure ? 'https://secure.gravatar.com' : 'http://www.gravatar.com';
```
修改后：
```
$url = $isSecure ? 'https://sdn.geekzu.org' : 'http://www.gravatar.com';
```

其中 ``https://sdn.geekzu.org`` 可以替换为别的 Gravatar 国内代理地址

## 下载渠道
1.Gitte [https://gitee.com/Magnetokuwan/AdminMD](https://gitee.com/Magnetokuwan/AdminMD)  适合国内（由GitHub同步至此）

2.GitHub [https://github.com/ouyangyanhuo/AdminMD](https://github.com/ouyangyanhuo/AdminMD)  适合国外（主仓库）
## 使用截图

![登录](https://cdn.jsdelivr.net/gh/fyhgay/CDNS@latest/2021/01/08/3af177c1328c3d1fc3da5ff26602feee.png "登录")
![后台首页](https://cdn.jsdelivr.net/gh/fyhgay/CDNS@latest/2021/07/15/748ba291663f8cb917662b703825cb4d.png "后台首页")
![文章撰写](https://cdn.jsdelivr.net/gh/fyhgay/CDNS@latest/2021/07/15/34c412ed6388b9ca1d72d65c89ce1f41.png "文章撰写")
![数据备份](https://cdn.jsdelivr.net/gh/fyhgay/CDNS@latest/2021/07/15/ff54bddcfd504694acaa493d67ee8eda.png "数据备份")