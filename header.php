<?php
if (!defined('__TYPECHO_ADMIN__')) {
    exit;
}
/** AdminMD Css and Js */
$header = '<link rel="stylesheet" href="' . Typecho_Common::url('normalize.css?v=' . $suffixVersion, $options->adminStaticUrl('css')) . '">
<link rel="stylesheet" href="' . Typecho_Common::url('grid.css?v=' . $suffixVersion, $options->adminStaticUrl('css')) . '">
<link rel="stylesheet" href="' . Typecho_Common::url('style.css?v=' . $suffixVersion, $options->adminStaticUrl('css')) . '">
<link rel="stylesheet" href="' . Typecho_Common::url('style.css?v=' . $suffixVersion, $options->adminStaticUrl('assets/css')) . '">
<link rel="stylesheet" href="' . Typecho_Common::url('vendor.bundle.base.css?v=' . $suffixVersion, $options->adminStaticUrl('assets/vendors/css')) . '">
<link rel="stylesheet" href="' . Typecho_Common::url('materialdesignicons.min.css?v=' . $suffixVersion, $options->adminStaticUrl('assets/vendors/mdi/css')) . '">
<script src="' . Typecho_Common::url('vendor.bundle.base.js?v=' . $suffixVersion, $options->adminStaticUrl('assets/vendors/js')) . '"></script>
<script src="' . Typecho_Common::url('off-canvas.js?v=' . $suffixVersion, $options->adminStaticUrl('assets/js')) . '"></script>
<script src="' . Typecho_Common::url('hoverable-collapse.js?v=' . $suffixVersion, $options->adminStaticUrl('assets/js')) . '"></script>

<!--[if lt IE 9]>
<script src="' . Typecho_Common::url('html5shiv.js?v=' . $suffixVersion, $options->adminStaticUrl('js')) . '"></script>
<script src="' . Typecho_Common::url('respond.js?v=' . $suffixVersion, $options->adminStaticUrl('js')) . '"></script>
<![endif]-->';

/** 注册一个初始化插件 */
$header = Typecho_Plugin::factory('admin/header.php')->header($header);

?><!DOCTYPE HTML>
<html class="no-js">
    <head>
        <meta charset="<?php $options->charset(); ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="renderer" content="webkit">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title><?php _e('%s - Powered by Typecho',$menu->title); ?></title>
        <meta name="robots" content="noindex, nofollow">
        <?php echo $header; ?>
    </head>
    <body<?php if (isset($bodyClass)) {echo ' class="' . $bodyClass . '"';} ?>>
    <!--[if lt IE 9]>
        <div class="message error browsehappy" role="dialog"><?php _e('当前网页 <strong>不支持</strong> 你正在使用的浏览器. 为了正常的访问, 请 <a href="https://www.microsoft.com/zh-cn/edge/">升级你的浏览器</a>'); ?>.</div>
    <![endif]-->
