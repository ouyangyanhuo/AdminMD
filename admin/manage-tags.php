<?php
include 'common.php';
include 'header.php';
include 'menu.php';
Typecho_Widget::widget('Widget_Metas_Tag_Admin')->to($tags);
?>
<div class="row">
  <div class="col-md-7 grid-margin stretch-card" role="main">
	<div class="card">
	  <div class="card-body">
		<h4 class="card-title"><?php include 'page-title.php'; ?></h4>
		<div class="dropdown-divider"></div>
		  <form method="post" name="manage_tags" class="operate-form">
			<div class="typecho-list-operate clearfix">
				<div class="operate">
				<label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
				<div class="btn-group btn-drop">
				<button class="btn dropdown-toggle btn-s" type="button"><i class="sr-only"><?php _e('操作'); ?></i><?php _e('选中项'); ?> <i class="i-caret-down"></i></button>
				<ul class="dropdown-menu">
					<li><a lang="<?php _e('你确认要删除这些标签吗?'); ?>" href="<?php $security->index('/action/metas-tag-edit?do=delete'); ?>"><?php _e('删除'); ?></a></li>
					<li><a lang="<?php _e('刷新标签可能需要等待较长时间, 你确认要刷新这些标签吗?'); ?>" href="<?php $security->index('/action/metas-tag-edit?do=refresh'); ?>"><?php _e('刷新'); ?></a></li>
					<li class="multiline">
						<button type="button" class="btn btn-s merge" rel="<?php $security->index('/action/metas-tag-edit?do=merge'); ?>"><?php _e('合并到'); ?></button>
						<input type="text" name="merge" class="text-s" />
					</li>
				</ul>
				</div>
				</div>
			</div>
			<ul class="typecho-list-notable tag-list clearfix">
				<?php if($tags->have()): ?>
				<?php while ($tags->next()): ?>
				<li style="list-style:none;" class="size-<?php $tags->split(5, 10, 20, 30); ?>" id="<?php $tags->theId(); ?>">
				<input type="checkbox" value="<?php $tags->mid(); ?>" name="mid[]"/>
				<span rel="<?php echo $request->makeUriByRequest('mid=' . $tags->mid); ?>"><?php $tags->name(); ?></span>
				<a class="tag-edit-link" href="<?php echo $request->makeUriByRequest('mid=' . $tags->mid); ?>"><i class="i-edit"></i></a>
				</li>
				<?php endwhile; ?>
				<?php else: ?>
				<h6 class="typecho-list-table-title"><?php _e('没有任何标签'); ?></h6>
				<?php endif; ?>
			</ul>
			<input type="hidden" name="do" value="delete" />
			</form>
	  </div>
	</div>
  </div>

  <div class="col-md-5 grid-margin stretch-card" role="form">
	<div class="card">
	  <div class="card-body">
		<?php Typecho_Widget::widget('Widget_Metas_Tag_Edit')->form()->render(); ?>
	  </div>
	</div>
  </div>
</div>
<?php
include 'copyright.php';
include 'common-js.php';
?>
<script type="text/javascript">
(function () {
    $(document).ready(function () {

        $('.typecho-list-notable').tableSelectable({
            checkEl     :   'input[type=checkbox]',
            rowEl       :   'li',
            selectAllEl :   '.typecho-table-select-all',
            actionEl    :   '.dropdown-menu a'
        });

        $('.btn-drop').dropdownMenu({
            btnEl       :   '.dropdown-toggle',
            menuEl      :   '.dropdown-menu'
        });

        $('.dropdown-menu button.merge').click(function () {
            var btn = $(this);
            btn.parents('form').attr('action', btn.attr('rel')).submit();
        });

        <?php if (isset($request->mid)): ?>
        $('.typecho-mini-panel').effect('highlight', '#AACB36');
        <?php endif; ?>
    });
})();
</script>
<?php include 'footer.php'; ?>