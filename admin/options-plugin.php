<?php
include 'common.php';
include 'header.php';
include 'menu.php';
?>
<div class="row">
	<div class="col-md-12 grid-margin stretch-card">
		<div class="card">
		  <div class="card-body">
			<h4 class="card-title"><?php include 'page-title.php'; ?></h4>
			<div class="dropdown-divider"></div>
			  <div class="col-12" role="form">
			  <?php Typecho_Widget::widget('Widget_Plugins_Config')->config()->render(); ?>
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