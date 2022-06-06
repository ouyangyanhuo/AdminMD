## AdminMD介绍

为Typecho博客程序专门美化的后台,框架主要采用MDUI

[![AdminMD](https://img.shields.io/badge/Magneto-AdminMD-brightgreen?style=for-the-badge&logo=github)](https://fmcf.cc/technology/37)
![Version](https://img.shields.io/badge/Version-1.8-critical?style=for-the-badge)
![Repo-size](https://img.shields.io/github/repo-size/ouyangyanhuo/AdminMD?style=for-the-badge)
![License](https://img.shields.io/github/license/ouyangyanhuo/AdminMD?style=for-the-badge)
![Stars](https://img.shields.io/github/stars/ouyangyanhuo/AdminMD?style=for-the-badge)
![Forks](https://img.shields.io/github/forks/ouyangyanhuo/AdminMD?style=for-the-badge)

## 更新日志:

- 删除了 Jsdeliv 原始线路的所有内容，并替换为 BootCDN 或者 Jsdeliv-Fastly
- 此版本属于紧急更新 应对 CSS、JS 失效导致的加载问题

## 编程语言

PHP

## 安装教程

导入到Typecho程序 usr/plugins/ 目录中，并解压

解压后文件夹名称必须为AdminMD

## 1.8 及以后版本注意事项

1.更改命名规则：Version1.8 → 1.8

2.将使用 Https 协议引用储存在仓库中的大部分静态资源，且使用Jsdeliv-Fastly线路，如果需要使用自建或本地静态资源，请自行研究。

## 使用须知

1.当前版本中仍存在部分不影响使用的未知Bug，一旦发现请务必反馈

2.后台登录页面的背景图可以在插件设置中进行更改

3.由于 Typecho 程序默认使用的 Gravatar 官方的头像线路已被 GWF 屏蔽，因此会导致使用本主题时使后台完全加载缓慢。

4.启用本插件后，若菜单栏未生效请清空缓存(服务器缓存)后，10分钟内会自动生效，禁用同理。

### 解决 使用须知->3 问题的方法

解决方法

修改 Typecho 程序源代码

需要修改文件地址：网站根目录 ``/var/Typecho/Common.php``第 937 行

修改前：

```
$url = $isSecure ? 'https://secure.gravatar.com' : 'http://www.gravatar.com';
```
修改后：
```
$url = $isSecure ? 'https://dn-qiniu-avatar.qbox.me' : 'http://www.gravatar.com';
```

其中 ``https://dn-qiniu-avatar.qbox.me`` 可以替换为别的 Gravatar 国内代理地址

## 下载渠道
1.Gitte [https://gitee.com/Magnetokuwan/AdminMD](https://gitee.com/Magnetokuwan/AdminMD)  适合国内（由GitHub同步至此）

2.GitHub [https://github.com/ouyangyanhuo/AdminMD](https://github.com/ouyangyanhuo/AdminMD)  适合国外（主仓库）
## 使用截图

![登录](https://cdn.jsdelivr.net/gh/fyhgay/CDNS@latest/2021/01/08/3af177c1328c3d1fc3da5ff26602feee.png "登录")
![后台首页](https://cdn.jsdelivr.net/gh/fyhgay/CDNS@latest/2021/07/15/748ba291663f8cb917662b703825cb4d.png "后台首页")
![文章撰写](https://cdn.jsdelivr.net/gh/fyhgay/CDNS@latest/2021/07/15/34c412ed6388b9ca1d72d65c89ce1f41.png "文章撰写")
![数据备份](https://cdn.jsdelivr.net/gh/fyhgay/CDNS@latest/2021/07/15/ff54bddcfd504694acaa493d67ee8eda.png "数据备份")