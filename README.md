## AdminMD介绍

为Typecho博客程序专门美化的后台，兼容 Typecho 1.3.0 / 1.2.x / 1.1，兼容 PHP 7.0 – 8.5。

[![AdminMD](https://img.shields.io/badge/Magneto-AdminMD-brightgreen?style=for-the-badge&logo=github)](https://fmcf.cc/technology/37)
![Version](https://img.shields.io/badge/Version-2.1.0-critical)
![Typecho](https://img.shields.io/badge/Typecho-1.3.0-blue)
![PHP](https://img.shields.io/badge/PHP-7.0%20~%208.5-777bb4)
![Repo-size](https://img.shields.io/github/repo-size/ouyangyanhuo/AdminMD)
![License](https://img.shields.io/github/license/ouyangyanhuo/AdminMD)
![Stars](https://img.shields.io/github/stars/ouyangyanhuo/AdminMD)
![Forks](https://img.shields.io/github/forks/ouyangyanhuo/AdminMD)

## 2.1.0 更新日志（Typecho 1.3.0 / PHP 8.5 适配）

这一版是一次结构性重写，核心目标是「在 1.3.0 上不白屏、不失效、不留垃圾文件」。

- **不再替换 `var/Widget/Menu.php`。** 1.3.0 的 `Widget\Menu` 已改为强类型类
  （`public string $title`、`output($class, $childClass)`），直接覆盖文件必然致命错误。
  改为在内核菜单类被加载之前，用 `class_alias` 就近接管到插件自身的精确复刻实现；
  接管失败会自动回退原生菜单，**任何情况下都不会白屏**。
- **钩子句柄改用斜杠式字符串。** 1.3.0 会对插件句柄做 `Common::nativeClassName()`
  归一化，`admin\header.php` 与 `admin/header.php` 会落到不同的键上。旧写法在新版下
  会静默失效（页面无报错，但样式完全不生效），现已统一修正。
- **不再生成 `.bak` 文件。** 旧版会在 `admin/` 下堆积 `.bak`，且反复启用/禁用会层层嵌套。
  现改为「标记块」注入登录页，禁用时按标记精确剔除，还原结果与原文件逐字节一致。
- **移除已失效的页面覆盖模板。** 1.3.0 重构了后台 DOM 与模板职责，
  `index.php` / `copyright.php` / `write-post.php` / `category.php` / `backup.php`
  等页面在新版下不具备可移植的覆盖价值，故删除；仅保留登录页美化。
- **后台改用 1.3.0 的「顶部导航 + 侧边抽屉」结构**，不依赖 mdui / 图标字体等外部 CDN，
  样式与脚本全部本地化（此前引用的 jsdelivr `Version1.7` 资源已与仓库不一致）。
- **PHP 8.5 兼容**：修复 `Typecho_Cookie::get()` 返回 `null` 传入 `htmlspecialchars()`
  的弃用警告、`explode('/', $options->version)` 空值告警等问题。
- **幂等与健壮性**：重复激活不会重复注册钩子；插件未保存过配置时不再抛异常
  （旧实现会让菜单接管静默失效）。

## 兼容性说明

| Typecho | PHP | 行为 |
| --- | --- | --- |
| 1.3.0+ | 8.0 – 8.5 | 侧边抽屉 + 菜单接管 + 登录页美化 |
| 1.2.x | 7.0 – 8.5 | 登录页美化 + 样式注入（不接管菜单） |
| 1.1 | 7.0 – 8.x | 同上 |

插件设置中提供「后台美化开关」，当内置样式与你的其他插件冲突时可一键关闭后台美化，
仅保留登录页美化。

## 自检

插件附带一份不依赖数据库的自检脚本，用于校验「钩子句柄归一化」「菜单类接管」
「登录页标记块幂等与还原」等最容易静默失效的环节：

```bash
php usr/plugins/AdminMD/tests/selftest.php
```

脚本需在 Typecho 站点根目录下的插件路径中运行（它通过 `__TYPECHO_ROOT_DIR__`
向上推导站点根目录）。


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
