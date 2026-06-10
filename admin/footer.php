<?php if(!defined('__TYPECHO_ADMIN__')) exit; ?>
<?php
$url = Helper::options()->pluginUrl . '/AdminMD/';
list($prefixVersion, $suffixVersion) = explode('/', Helper::options()->version);
?>
<link rel="stylesheet" href="<?php echo $url; ?>assets/css/cursor.css?<?php echo $suffixVersion; ?>">
<script src="<?php echo $url; ?>assets/js/cursor.js?<?php echo $suffixVersion; ?>"></script>
</body>
</html>
<?php
/** 注册一个结束插件 */
Typecho_Plugin::factory('admin/footer.php')->end();
