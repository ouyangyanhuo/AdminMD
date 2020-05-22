<?php if(!defined('__TYPECHO_ADMIN__')) exit; ?>
<div class="container-scroller">
<link rel="stylesheet" href="//cdnjs.loli.net/ajax/libs/mdui/0.4.3/css/mdui.min.css">
<script src="//cdnjs.loli.net/ajax/libs/mdui/0.4.3/js/mdui.min.js"></script>
<body class="mdui-appbar-with-toolbar mdui-theme-primary-indigo mdui-theme-accent-pink mdui-loaded mdui-drawer-body-left">
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
				
            	<span class="nav-link dropdown-toggle" mdui-menu="{target: &#39;#more_menu&#39;}">
            		
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
            			<li class="mdui-divider"></li>
						<li class="mdui-menu-item">
                		<span><center><?php if ($user->logged > 0) { $logged = new Typecho_Date($user->logged); _e('最后登录: %s', $logged->word()); } ?></center></span>
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
	<!--按钮1-->
	<div class="mdui-collapse-item ">
      <div class="mdui-collapse-item-header mdui-list-item mdui-ripple">
        <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-blue">settings</i>
        <div class="mdui-list-item-content">主控制台</div>
        <i class="mdui-collapse-item-arrow mdui-icon material-icons">keyboard_arrow_down</i>
      </div>
      <div class="mdui-collapse-item-body mdui-list">
        <a href="./index.php" class="mdui-list-item mdui-ripple ">首页概要</a>
        <a href="./profile.php" class="mdui-list-item mdui-ripple ">个人设置</a>
        <a href="./plugins.php" class="mdui-list-item mdui-ripple ">插件管理</a>
        <a href="./themes.php" class="mdui-list-item mdui-ripple ">主页外观</a>
        <a href="./backup.php" class="mdui-list-item mdui-ripple ">系统备份</a>
      </div>
    </div>
	<!--按钮2-->
   	<div class="mdui-collapse-item ">
      <div class="mdui-collapse-item-header mdui-list-item mdui-ripple">
        <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-red">border_color</i>
        <div class="mdui-list-item-content">内容撰写</div>
        <i class="mdui-collapse-item-arrow mdui-icon material-icons">keyboard_arrow_down</i>
      </div>
      <div class="mdui-collapse-item-body mdui-list">
        <a href="./write-post.php" class="mdui-list-item mdui-ripple ">撰写文章</a>
        <a href="./write-page.php" class="mdui-list-item mdui-ripple ">创建页面</a>
      </div>
    </div>
	<!--按钮3-->
    <div class="mdui-collapse-item ">
      <div class="mdui-collapse-item-header mdui-list-item mdui-ripple">
        <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-orange">inbox</i>
        <div class="mdui-list-item-content">内容管理</div>
        <i class="mdui-collapse-item-arrow mdui-icon material-icons">keyboard_arrow_down</i>
      </div>
      <div class="mdui-collapse-item-body mdui-list">
        <a href="./manage-posts.php" class="mdui-list-item mdui-ripple ">管理文章</a>
        <a href="./manage-comments.php" class="mdui-list-item mdui-ripple ">管理评论</a>
        <a href="./manage-categories.php" class="mdui-list-item mdui-ripple ">管理分类</a>
        <a href="./manage-tags.php" class="mdui-list-item mdui-ripple ">管理标签</a>
        <a href="./manage-medias.php" class="mdui-list-item mdui-ripple ">管理文件</a>
        <a href="./manage-users.php" class="mdui-list-item mdui-ripple ">管理用户</a>
        <a href="./manage-pages.php" class="mdui-list-item mdui-ripple ">管理页面</a>
      </div>
    </div>
    <!--按钮4-->
	<div class="mdui-collapse-item ">
      <div class="mdui-collapse-item-header mdui-list-item mdui-ripple">
        <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-pink">settings_applications</i>
        <div class="mdui-list-item-content">更多设置</div>
        <i class="mdui-collapse-item-arrow mdui-icon material-icons">keyboard_arrow_down</i>
      </div>
      <div class="mdui-collapse-item-body mdui-list">
        <a href="./options-general.php" class="mdui-list-item mdui-ripple ">基本设置</a>
        <a href="./options-discussion.php" class="mdui-list-item mdui-ripple ">评论设置</a>
        <a href="./options-reading.php" class="mdui-list-item mdui-ripple ">阅读设置</a>
        <a href="./options-permalink.php" class="mdui-list-item mdui-ripple ">永久链接</a>
      </div>
    </div>
    <!--按钮5-->
	<div class="mdui-collapse-item ">
      <div class="mdui-collapse-item-header mdui-list-item mdui-ripple">
        <i class="mdui-list-item-icon mdui-icon material-icons mdui-text-color-red">notifications_active</i>
        <div class="mdui-list-item-content">系统公告</div>
        <i class="mdui-collapse-item-arrow mdui-icon material-icons">keyboard_arrow_down</i>
      </div>
      <div class="mdui-collapse-item-body mdui-list">
        <a href="http://typecho.org/blog/" class="mdui-list-item mdui-ripple" target="_blank">Typecho公告</a>
        <a onclick="notice();" class="mdui-list-item mdui-ripple">AdminMD公告</a>
      </div>
      <script type="text/javascript">
        function notice() {
            mdui.dialog({
                title: 'AdminMD公告',
                content: '本模板当前版本:Vesion1.3<br>开发者:Magneto<br>开源地址：<a href="https://gitee.com/Magnetokuwan/AdminMD" target="_blank">Gitee</a>，<a href="https://github.com/ouyangyanhuo/AdminMD" target="_blank">GitHub</a><br>更新日志：修复已知Bug、登录界面优化、由T-AdminTheme更名为AdminMD',
                buttons: [
                    {
                        text: '我知道了'
                    }
                ]
            });
        }
    </script>
    </div>
  </div>
</div>