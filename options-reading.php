<?php
include 'common.php';
include 'header.php';
include 'menu.php';
include 'TUi.php';
?>


<div class="row">
	<div class="col-md-12 grid-margin stretch-card">
		<div class="card">
		  <div class="card-body">
			<h4 class="card-title"><?php include 'page-title.php'; ?></h4>
			<div class="dropdown-divider"></div>
			  <div class="col-md-6" role="form">
			  <?php Typecho_Widget::widget('Widget_Options_Reading')->form()->render(); ?>
			  </div>
			</div>
		</div>
	</div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
include 'form-js.php';
?>
<script>
$('#frontPage-recent,#frontPage-page,#frontPage-file').change(function () {
    var t = $(this);
    if (t.prop('checked')) {
        if ('frontPage-recent' == t.attr('id')) {
            $('.front-archive').addClass('hidden');
        } else {
            $('.front-archive').insertAfter(t.parent()).removeClass('hidden');
        }
    }
});
</script>
<?php
include 'footer.php';
?>
