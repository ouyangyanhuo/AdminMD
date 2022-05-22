<?php
include 'common.php';
include 'header.php';
include 'menu.php';
$stat = Typecho_Widget::widget('Widget_Stat');
?>
<div class="mdui-container-fluid" style="font-size:12pt;padding-left:15pt;padding-right:15pt;vertical-align:middle;padding-top:10pt;">
<div class="page-header">
  <h3 class="page-title">
	<svg t="1610104014073" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="855" width="30" height="30"><path d="M64 768h896v64H64zM64 192v512h896V192z m320 384H320V384h64z m192 0H512V384h64z m192 0h-64V320h64z" fill="#2071FF" p-id="856"></path></svg> 博客数据</h3>
</div>
<div class="row">
  <div class="mdui-col-xs-4">
	<div class="card bg-gradient-danger card-img-holder text-white">
	  <div class="card-body">
		<img src="https://s1.ax1x.com/2020/04/21/JGMB4J.png" class="card-img-absolute" alt="circle-image">
		<h4 class="font-weight-normal mb-3">文章总计<i class="mdi mdi-library-books mdi-24px float-right"></i></h4>
		<h2 class="mb-5">
			<a href="<?php $options->adminUrl('manage-posts.php'); ?>" style="text-decoration:none;">
				<?php _e('<em>%s</em> 篇文章',$stat->myPublishedPostsNum); ?>
			</a>
		</h2>
		<a class="mt-3 mb-0 text-sm">
            <a href="<?php $options->adminUrl('write-post.php'); ?>" class="text-white" style="text-decoration:none;">
            	<i class="mdi mdi-pen"></i>
            	<?php _e('撰写新文章'); ?>
            </a>
            <a href="<?php $options->adminUrl('write-page.php'); ?>" class="text-white" style="text-decoration:none;">
            	<i class="mdi mdi-pen"></i>
            	<?php _e('创建新页面'); ?>
            </a>
        </a>
	  </div>
	</div>
  </div>
  <div class="mdui-col-xs-4">
	<div class="card bg-gradient-info card-img-holder text-white">
	  <div class="card-body">
		<img src="https://s1.ax1x.com/2020/04/21/JGMB4J.png" class="card-img-absolute" alt="circle-image">
		<h4 class="font-weight-normal mb-3">评论总计<i class="mdi mdi-comment-processing-outline mdi-24px float-right"></i></h4>
		<h2 class="mb-5">
			<a href="<?php $options->adminUrl('manage-comments.php'); ?>" style="text-decoration:none;">
				<?php _e('<em>%s</em> 条评论',$stat->myPublishedCommentsNum); ?>
			</a>
		</h2>
		<h6 class="card-text">
			<?php if($user->pass('contributor', true)): ?>
			<?php if($user->pass('editor', true) && 'on' == $request->get('__typecho_all_comments') && $stat->waitingCommentsNum > 0): ?>
				<a style="color:#ffffff;" href="<?php $options->adminUrl('manage-comments.php?status=waiting'); ?>"><?php _e('待审核的评论'); ?></a>
				<span class="balloon"><?php $stat->waitingCommentsNum(); ?></span>
				
			<?php elseif($stat->myWaitingCommentsNum > 0): ?>
				<a style="color:#ffffff;" href="<?php $options->adminUrl('manage-comments.php?status=waiting'); ?>"><?php _e('待审核评论'); ?></a>
				<span class="balloon"><?php $stat->myWaitingCommentsNum(); ?></span>

			<?php endif; ?>
			<?php if($user->pass('editor', true) && 'on' == $request->get('__typecho_all_comments') && $stat->spamCommentsNum > 0): ?>
				<a style="color:#ffffff;" href="<?php $options->adminUrl('manage-comments.php?status=spam'); ?>"><?php _e('垃圾评论'); ?></a>
				<span class="balloon"><?php $stat->spamCommentsNum(); ?></span>
			<?php elseif($stat->mySpamCommentsNum > 0): ?>
				<a style="color:#ffffff;" href="<?php $options->adminUrl('manage-comments.php?status=spam'); ?>"><?php _e('垃圾评论'); ?></a>
				<span class="balloon"><?php $stat->mySpamCommentsNum(); ?></span>
				
			<?php endif; ?>
			<?php endif; ?>
		</h6>
		<a href="<?php $options->adminUrl('manage-comments.php'); ?>" class="text-white mr-2" style="text-decoration:none;">
			<i class="mdi mdi-basket"></i>
			<?php _e('全部评论'); ?>
		</a>
        <a href="<?php $options->adminUrl('options-reading.php'); ?>" class="text-white mr-2 " style="text-decoration:none;">
        	<i class="mdi mdi-book"></i>
        	<?php _e('阅读设置'); ?>
        </a>			
	  </div>
	</div>
  </div>
  <div class="mdui-col-xs-4">
	<div class="card bg-gradient-success card-img-holder text-white">
	  <div class="card-body">
		<img src="https://s1.ax1x.com/2020/04/21/JGMB4J.png" class="card-img-absolute" alt="circle-image">
		<h4 class="font-weight-normal mb-3">分类总计<i class="mdi mdi-buffer mdi-24px float-right"></i></h4>
		<h2 class="mb-5">
			<a href="<?php $options->adminUrl('manage-categories.php'); ?>" style="text-decoration:none;">
				<?php _e('<em>%s</em> 个分类',$stat->categoriesNum); ?>
			</a>
		</h2>
		<a href="<?php $options->adminUrl('themes.php'); ?>" class="text-white" style="text-decoration:none;">
			<i class="mdi mdi-palette"></i>
			<?php _e('更换外观'); ?>
		</a>
        <a href="<?php $options->adminUrl('plugins.php'); ?>" class="text-white" style="text-decoration:none;">
        	<i class="mdi mdi-settings"></i>
        	<?php _e('插件管理'); ?>
        </a>
	  </div>
	</div>
  </div>
</div>
<br>
<div class="page-header">
  <h3 class="page-title">
  	<svg t="1610104031177" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="855" width="30" height="30"><path d="M64 192h896v64H64zM64 320v512h896V320z m64 256h256v64H128z m576 192H128v-64h576z" fill="#2071FF" p-id="856"></path></svg> 相关信息</h3>
</div>
<div class="row">
  <div class="col-md-6 grid-margin stretch-card">
	<div class="card">
	  <div class="card-body">
		<h4 class="card-title"><?php _e('最近发布的文章'); ?></h4>
		<div class="table-responsive">
		  <table class="table">
			<thead>
			  <tr>
				<th> 日期 </th>
				<th> 文章标题 </th>
			  </tr>
			</thead>
		</table>
		<ul class="mdui-list">
			<?php Typecho_Widget::widget('Widget_Contents_Post_Recent', 'pageSize=5')->to($posts); ?>
			<?php if($posts->have()): ?>
			<?php while($posts->next()): ?>
			<a href="<?php $posts->permalink(); ?>" target="_blank">
			<li class="mdui-list-item mdui-ripple">
    			<div class="mdui-list-item-content" style="color:#FF5252;"><?php $posts->date('m.d'); ?></div>
    			<div class="mdui-list-item-content"><?php $posts->title(); ?></div>
			</li>
			</a>
			  <?php endwhile; ?>
			  <?php else: ?>
			<li class="mdui-list-item mdui-ripple">
    			<div class="mdui-list-item-content"><?php _e('暂时没有文章'); ?></div>
			</li>
			  <?php endif; ?>
			</ul>
		</div>
	  </div>
	</div>
  </div>
  <div class="col-md-6 grid-margin stretch-card">
	<div class="card">
	  <div class="card-body">
		<h4 class="card-title"><?php _e('最近收到的回复'); ?></h4>
		<div class="table-responsive">
		  <table class="table">
			<thead>
			  <tr>
				<th> 日期 </th>
				<th> 昵称及评论内容 </th>
			  </tr>
			</thead>
		  </table>
			<?php Typecho_Widget::widget('Widget_Comments_Recent', 'pageSize=10')->to($comments); ?>
			<?php if($comments->have()): ?>
			<?php while($comments->next()): ?>
			<ul class="mdui-list">
			  <div class="mdui-row">
                <div class="mdui-col-xs-2">
                  <li class="mdui-list-item mdui-ripple">
					<div class="mdui-list-item-content" style="color:#FF5252;"><?php $comments->date('m.d'); ?></div>
				  </li>
				</div>
                <div class="mdui-col-xs-9">
			     <a href="<?php $comments->permalink(); ?>" class="title">
				   <li class="mdui-list-item mdui-ripple">
					   <div class="mdui-list-item">
    				   <img src="<?php $email =$comments->mail; if($email){if(strpos($email,'@qq.com') !==false){$email=str_replace('@qq.com','',$email);echo '//q1.qlogo.cn/g?b=qq&nk='.$email.'&s=100';}else{$email= md5($email);echo Typecho_Common::gravatarUrl($comments->mail, 220, 'X', 'mm', $request->isSecure());}}else{echo 'https://sdn.geekzu.org/avatar/null?';} ?>" alt="Image placeholder" class="avatar rounded-circle" style="width:35px;height:35px;margin-right:5px;"> <?php $comments->author(true); ?>
    				   </div>
    			       <div class="mdui-list-item" style="color:#3F51B5;"><?php $comments->excerpt(30, '...'); ?></div>
                   </li>
                 </a>
                </div>
              </div>
            </ul>
			  <?php endwhile; ?>
			  <?php else: ?>
			  <li class="mdui-list-item mdui-ripple">
    			<div class="mdui-list-item-content"><?php _e('暂时没有回复'); ?></div>
			  </li>
			  <?php endif; ?>
		  
		</div>
	  </div>
	</div>
  </div>
</div>
<?php
include 'copyright.php';
include 'common-js.php';
include 'footer.php';
?>