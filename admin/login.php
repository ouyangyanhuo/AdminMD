<?php
/**
 * AdminMD 登录页美化模板（Typecho 1.3.0+ / 1.2.x）
 *
 * 由插件在激活时注入到 admin/login.php 之后被 require。
 * 注意：本文件被 require 时，相对路径会以「本文件所在目录」为基准解析，
 * 因此这里必须使用绝对路径引用后台的 common.php / header.php 等，
 * 否则会把 common.php 二次载入，触发「常量重复定义」警告。
 *
 * @package AdminMD
 */

if (!defined('__TYPECHO_ADMIN__')) {
    exit;
}

/** 内核后台目录。Typecho 支持在 config 中自定义 pluginDir，但后台目录始终是 <root>/admin */
$adminmdAdminDir = AdminMD_Core::rootPath() . '/admin';

if (!is_file($adminmdAdminDir . '/common.php')) {
    return;
}

// common.php 正常已由 admin/login.php 载入；此处用 include_once 保证不会二次执行
include_once $adminmdAdminDir . '/common.php';

if (!isset($user) || !isset($options)) {
    return;
}

if ($user->hasLogin()) {
    $response->redirect($options->adminUrl);
}

$adminmdRememberName = AdminMD_Core::readCookie('__typecho_remember_name');

Typecho_Cookie::delete('__typecho_remember_name');

$bodyClass = 'body-100';

include $adminmdAdminDir . '/header.php';

$adminmdUrl = AdminMD_Core::assetUrl();
$adminmdVersion = AdminMD_Core::assetVersion();
?>
<div class="typecho-login-wrap">
    <div class="typecho-login">
        <h1 class="adminmd-login-title"><?php _e('登录'); ?></h1>
        <form action="<?php $options->loginAction(); ?>" method="post" name="login" role="form">
            <p>
                <label for="name" class="sr-only"><?php _e('用户名或邮箱'); ?></label>
                <input type="text" id="name" name="name"
                       value="<?php echo htmlspecialchars($adminmdRememberName, ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="<?php _e('用户名或邮箱'); ?>" class="text-l w-100" autofocus />
            </p>
            <p>
                <label for="password" class="sr-only"><?php _e('密码'); ?></label>
                <input type="password" id="password" name="password"
                       placeholder="<?php _e('密码'); ?>" class="text-l w-100" required />
            </p>
            <p class="submit">
                <button type="submit" class="btn btn-l w-100 primary"><?php _e('登录'); ?></button>
                <input type="hidden" name="referer"
                       value="<?php echo $request->filter('html')->get('referer'); ?>" />
            </p>
            <p>
                <label for="remember">
                    <input type="checkbox" name="remember" value="1" id="remember" class="checkbox"
                        <?php if (AdminMD_Core::readCookie('__typecho_remember_remember')): ?> checked<?php endif; ?> />
                    <?php _e('下次自动登录'); ?>
                </label>
            </p>
        </form>

        <p class="more-link">
            <a href="<?php $options->siteUrl(); ?>"><?php _e('返回首页'); ?></a>
            <?php if ($options->allowRegister): ?>
                &bull;
                <a href="<?php $options->registerUrl(); ?>"><?php _e('用户注册'); ?></a>
            <?php endif; ?>
        </p>
    </div>
</div>
<?php
include $adminmdAdminDir . '/common-js.php';
?>
<script>
(function () {
    var name = document.getElementById('name');
    if (name) {
        name.focus();
    }
})();
</script>
<?php
include $adminmdAdminDir . '/footer.php';
