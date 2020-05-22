<?php
include 'common.php';
include 'header.php';
include 'menu.php';
include 'TUi.php';
?>


<div class="row">
	<div class="col-md-12 grid-margin stretch-card">
		<div class="card">
		  <div class="card-body" role="main">
				<h4 class="card-title"><?php include 'page-title.php'; ?></h4>
					<div class="dropdown-divider"></div>
					<div class="typecho-page-main" role="form">
						<div class="col-mb-12 col-tb-6">
							<?php Typecho_Widget::widget('Widget_Metas_Category_Edit')->form()->render(); ?>
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
include 'footer.php';
?>