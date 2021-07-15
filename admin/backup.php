<?php
include 'common.php';
include 'header.php';
include 'menu.php';
$actionUrl = $security->getTokenUrl(
    Typecho_Router::url('do', array('action' => 'backup', 'widget' => 'Backup'),
        Typecho_Common::url('index.php', $options->rootUrl)));

$backupFiles = Typecho_Widget::widget('Widget_Backup')->listFiles();
?>
<div class="row">
	<div class="col-md-12 grid-margin stretch-card">
		<div class="card">
		  <div class="card-body" role="main">
			<h4 class="card-title"><?php include 'page-title.php'; ?></h4>
			<div class="dropdown-divider"></div>
			  <div class="col-md-6">
				<form action="<?php echo $actionUrl; ?>" method="post">
				<h3><?php _e('备份您的数据'); ?></h3>
				<ol>
					<li><?php _e('此备份操作仅包含<strong>内容数据</strong>, 并不会涉及任何<strong>设置信息</strong>'); ?></li>
					<li><?php _e('如果您的数据量过大, 为了避免操作超时, 建议您直接使用数据库提供的备份工具备份数据'); ?></li>
					<li><strong class="warning"><?php _e('为了缩小备份文件体积, 建议您在备份前删除不必要的数据'); ?></strong></li>
				</ol>
				<p><button class="mdui-btn mdui-btn-raised mdui-ripple mdui-color-blue mdui-text-color-white-text" type="submit"><?php _e('开始备份 &raquo;'); ?></button></p>
					<input tabindex="1" type="hidden" name="do" value="export">
				</form>
			  </div>
	  
			  <div id="backup-secondary" class="col-md-6" role="form">
				<h3><?php _e('恢复数据'); ?></h3>
				<ul class="typecho-option-tabs clearfix">
					<li class="active w-50"><a href="#from-upload">上传</a></li>
					<li class="w-50"><a href="#from-server">从服务器</a></li>
				</ul>
				<form action="<?php echo $actionUrl; ?>" id="from-upload" class="tab-content" method="post" enctype="multipart/form-data">
					<ul class="typecho-option">
						<li>
							<input tabindex="2" id="backup-upload-file" name="file" type="file" class="file">
						</li>
					</ul>
					<ul class="typecho-option typecho-option-submit">
						<li>
							<button tabindex="3" type="submit" class="mdui-btn mdui-btn-raised mdui-ripple mdui-color-blue mdui-text-color-white-text"><?php _e('上传并恢复 &raquo;'); ?></button>
							<input type="hidden" name="do" value="import">
						</li>
					</ul>
				</form>
				<form action="<?php echo $actionUrl; ?>" id="from-server" class="tab-content hidden" method="post">
					<ul class="typecho-option">
						<li>
							<?php if (empty($backupFiles)): ?>
								<p class="description"><?php _e('将备份文件手动上传至服务器的 %s 目录下后, 这里会出现文件选项', __TYPECHO_BACKUP_DIR__); ?></p>
							<?php else: ?>
								<label class="typecho-label" for="backup-select-file"><?php _e('选择一个备份文件恢复数据'); ?></label>
								<select name="file" id="backup-select-file">
									<?php foreach ($backupFiles as $file): ?>
										<option value="<?php echo $file; ?>"><?php echo $file; ?></option>
									<?php endforeach; ?>
								</select>
							<?php endif; ?>
						</li>
					</ul>
					<ul class="typecho-option typecho-option-submit">
						<li>
							<button tabindex="5" type="submit" class="mdui-btn mdui-btn-raised mdui-ripple mdui-color-blue mdui-text-color-white-text"><?php _e('选择并恢复 &raquo;'); ?></button>
							<input type="hidden" name="do" value="import">
						</li>
					</ul>
				</form>
			  </div>
		  </div>
		</div>
	</div>
</div>
<?php
include 'copyright.php';
include 'common-js.php';
?>
<script>
    $('#backup-secondary .typecho-option-tabs li').click(function() {
        $('#backup-secondary .typecho-option-tabs li').removeClass('active');
        $(this).addClass('active');
        $(this).parents('#backup-secondary').find('.tab-content').addClass('hidden');

        var selected_tab = $(this).find('a').attr('href');
        $(selected_tab).removeClass('hidden');

        return false;
    });

    $('#backup-secondary form').submit(function (e) {
        if (!confirm('<?php _e('恢复操作将清除所有现有数据, 是否继续?'); ?>')) {
            return false;
        }
    });
</script>
<?php include 'footer.php'; ?>