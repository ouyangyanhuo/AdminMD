<?php
'common.php';

if ($user->hasLogin()) {
    $response->redirect($options->adminUrl);
}
$rememberName = htmlspecialchars(Typecho_Cookie::get('__typecho_remember_name'));
Typecho_Cookie::delete('__typecho_remember_name');

$bodyClass = 'body-100';

include 'header.php';
?>
<link rel="stylesheet" href="//cdn.bootcdn.net/ajax/libs/mdui/1.0.2/css/mdui.min.css">
<script src="//cdn.bootcdn.net/ajax/libs/mdui/1.0.2/js/mdui.min.js"></script>
<div class="typecho-login-wrap">
    <div class="typecho-login">
        <h1>Login</h1>
        <form action="<?php $options->loginAction(); ?>" method="post" name="login" role="form"><br>
            <p>
                <label for="name" class="sr-only"><?php _e('用户名'); ?></label>
                <input type="text" id="name" name="name" value="<?php echo $rememberName; ?>" placeholder="<?php _e('用户名'); ?>" class="text-l w-100" autofocus />
            </p>
            <p>
                <label for="password" class="sr-only"><?php _e('密码'); ?></label>
                <input type="password" id="password" name="password" class="text-l w-100" placeholder="<?php _e('密码'); ?>" />
            </p>
            <p class="submit">
                <button type="submit" class="mdui-btn mdui-btn-raised mdui-ripple mdui-color-blue mdui-text-color-white-text"><?php _e('登录'); ?></button>
                <input type="hidden" name="referer" value="<?php echo htmlspecialchars($request->get('referer')); ?>" />
            </p>
            <p>
            	<label for="remember"><input type="checkbox"/> <?php _e('下次自动登录'); ?></label>
            </p>
        </form>
        
        <p class="more-link">
        	<div class="mdui-chip" mdui-dialog="{target: '#x1'}">
			<span class="mdui-chip-icon"><i class="mdui-icon material-icons">keyboard_backspace</i></span>
			<span class="mdui-chip-title mdui-text-color-black-text"><?php _e('返回首页'); ?></span>
			</div>
			<div class="mdui-dialog" id="x1">
				<div class="mdui-dialog-title">确定要离开这里吗?</div>
				<div class="mdui-dialog-content">为防止误触，请确定是否离开这里，如果不离开请点击对话框以外的地方.</div>
				<div class="mdui-dialog-actions">
    				<a href="<?php $options->siteUrl(); ?>"><button class="mdui-btn mdui-ripple">确定离开</button></a>
				</div>
			</div>
            <?php if($options->allowRegister): ?>
            &bull;
            <div class="mdui-chip">
        	<a href="<?php $options->registerUrl(); ?>">
			<span class="mdui-chip-icon"><i class="mdui-icon material-icons">mood</i></span>
			<span class="mdui-chip-title"><?php _e('用户注册'); ?></span>
			</a>
			</div>
            <?php endif; ?>
        </p>
    </div>
</div>
<?php 
include 'common-js.php';
?>
<script>
$(document).ready(function () {
    $('#name').focus();
});
</script>
<?php
include 'footer.php';
?>