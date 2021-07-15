<?php
include 'common.php';
include 'header.php';
include 'menu.php';
$stat = Typecho_Widget::widget('Widget_Stat');
?>
<div class="row">
	<div class="col-md-12 grid-margin stretch-card">
		<div class="card">
			<div class="card-body">
				<h4 class="card-title"><?php include 'page-title.php'; ?></h4>
				<div class="dropdown-divider"></div>
					<div class="default-tab" role="form">
						<ul class="nav nav-tabs mb-3" role="tablist">
								<li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#profile"><?php _e('个人资料'); ?></a></li>
								<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#write_set"><?php _e('撰写设置'); ?></a></li>
								<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#password"><?php _e('密码修改'); ?></a></li>
						</ul>
						<div class="tab-content">

							<div class="tab-pane fade show active" id="profile" role="tabpanel">
								<div class="p-t-15">
									<?php Typecho_Widget::widget('Widget_Users_Profile')->profileForm()->render(); ?>
								</div>
							</div>
							
							<div class="tab-pane fade" id="write_set">
								<div class="p-t-15">
								<?php if($user->pass('contributor', true)): ?>
								<section id="writing-option">
									<?php Typecho_Widget::widget('Widget_Users_Profile')->optionsForm()->render(); ?>
								</section>
								<?php endif; ?>
								</div>
							</div>
							
							<div class="tab-pane fade" id="password">
								<div class="p-t-15">
								<section id="change-password">
									<?php Typecho_Widget::widget('Widget_Users_Profile')->passwordForm()->render(); ?>
								</section>
								<?php Typecho_Widget::widget('Widget_Users_Profile')->personalFormList(); ?>
								</div>
							</div>
						</div>
					</div>
			</div>
		</div>
	</div>
</div>
<?php
include 'copyright.php';
include 'common-js.php';
include 'form-js.php';
Typecho_Plugin::factory('admin/profile.php')->bottom();
include 'footer.php';
?>