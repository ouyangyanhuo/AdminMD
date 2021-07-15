<?php include 'common.php'; ?>
<?php if(!defined('__TYPECHO_ADMIN__')) exit; ?>
<?php
$url = Helper::options()->pluginUrl . '/AdminMD/';
?>
<div class="container-scroller">
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/mdui@1.0.1/dist/css/mdui.min.css">
<script src="///cdn.jsdelivr.net/npm/mdui@1.0.1/dist/js/mdui.min.js"></script>
<body class="mdui-appbar-with-toolbar mdui-theme-primary-indigo content-wrapper-before gradient-45deg-indigo-blue mdui-theme-accent-pink mdui-loaded mdui-drawer-body-left">
	<header class="mdui-appbar mdui-appbar-fixed">
			<div class="mdui-toolbar mdui-color-theme">
		<!--菜单按钮-->
    <span class="mdui-btn mdui-btn-icon mdui-ripple mdui-ripple-white" mdui-drawer="{target: &#39;#main-drawer&#39;, swipe: true}">
    <i class="mdui-icon material-icons">menu</i></span>
    <!--名称-->
    <a class="mdui-typo-headline mdui-hidden-xs"><?php _e('%s', $menu->title, $options->title); ?></a>
<!--右侧按钮集开端-->
    <div class="mdui-toolbar-spacer"></div>
	<!-- 顶部导航 -->
			<!--UI-->
				<!--个人信息-->
				<?php echo '<img src="' . Typecho_Common::gravatarUrl($user->mail, 220, 'X', 'mm', $request->isSecure()) . '" alt="' . $user->screenName . '" width="40" class="mdui-img-circle"/>'; ?>
            	<span class="nav-link dropdown-toggle mdui-ripple" mdui-menu="{target: &#39;#more_menu&#39;}">
            		
            		<div class="nav-profile-text"><?php $user->screenName(); ?></div>
            	</span>
				<!--下拉按钮-->
            		<ul class="mdui-menu" id="more_menu">
						<li class="mdui-menu-item">
            				<a href="<?php $options->adminUrl('profile.php'); ?>" class="dropdown-item">
            					<i class="mdi mdi-account mr-2 text-success"></i>
                				<span>我的信息</span>
            				</a>
            			</li>
            			<li class="mdui-menu-item">
            			<a href="<?php $options->adminUrl('write-post.php'); ?>" class="dropdown-item">
                			<i class="mdi mdi-pen mr-2 text-warning"></i>
                			<span>新建文章</span>
            			</a>
						</li>
						<li class="mdui-menu-item">
            				<a href="<?php $options->adminUrl('manage-comments.php'); ?>" class="dropdown-item">
                				<i class="mdi mdi-comment-processing-outline  mr-2 text-primary"></i>
                				<span>评论管理</span>
            				</a> 
            			</li>
            		</ul>
        		</li>
				<!--更多-->
				<span class="mdui-btn mdui-btn-icon mdui-ripple" mdui-menu="{target: &#39;#earth_menu&#39;}">
				<i class="mdui-icon material-icons">more_vert</i></span>
				<ul class="mdui-menu" id="earth_menu">
					<li class="mdui-menu-item">
						<a class="exit" href="<?php $options->logoutUrl(); ?>" class="mdui-ripple"><?php _e('登出'); ?></a>
        			</li>
        			<li class="mdui-menu-item">
						<a href="<?php $options->siteUrl(); ?>" class="mdui-ripple"><?php _e('网站'); ?></a>
					</li>
					<li class="mdui-divider"></li>
						<li class="mdui-menu-item">
						<a href="javascript:history.go(0);" class="mdui-ripple">刷新页面</a>
					</li>
				</ul>
			<!--UI结束-->
<!--右侧按钮集结束-->
  </div>
  
</header>
<div class="mdui-drawer" id="main-drawer">
  <div class="mdui-list" mdui-collapse="{accordion: true}" style="margin-bottom: 76px;">
     <?php $menu->output(); ?>
  </div>
</div>
		
<script>
$(document).ready(function () {
    var ul = $('#typecho-message ul'), cache = window.sessionStorage,
        html = cache ? cache.getItem('feed') : '',
        update = cache ? cache.getItem('update') : '';

    if (!!html) {
        ul.html(html);
    } else {
        html = '';
        $.get('<?php $options->index('/action/ajax?do=feed'); ?>', function (o) {
            for (var i = 0; i < o.length; i ++) {
                var item = o[i];
                html += '<li><span>' + item.date + '</span> <a href="' + item.link + '" target="_blank">' + item.title
                    + '</a></li>';
            }

            ul.html(html);
            cache.setItem('feed', html);
        }, 'json');
    }

    function applyUpdate(update) {
        if (update.available) {
            $('<div class="update-check message error"><p>'
                + '<?php _e('您当前使用的版本是 %s'); ?>'.replace('%s', update.current) + '<br />'
                + '<strong><a href="' + update.link + '" target="_blank">'
                + '<?php _e('官方最新版本是 %s'); ?>'.replace('%s', update.latest) + '</a></strong></p></div>')
            .insertAfter('.typecho-page-title').effect('highlight');
        }
    }

    if (!!update) {
        applyUpdate($.parseJSON(update));
    } else {
        $.get('<?php $options->index('/action/ajax?do=checkVersion'); ?>', function (o, status, resp) {
            applyUpdate(o);
            cache.setItem('update', resp.responseText);
        }, 'json');
    }
});

</script>