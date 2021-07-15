<?php
include 'common.php';
include 'header.php';
include 'menu.php';
?>
<?php Typecho_Widget::widget('Widget_Plugins_List@activated', 'activated=1')->to($activatedPlugins); ?>
<?php if ($activatedPlugins->have() || !empty($activatedPlugins->activatedPlugins)): ?>
	<div class="row">
	  <div class="col-md-12 grid-margin stretch-card">
		<div class="card">
		  <div class="card-body">
			<h4 class="card-title"><?php include 'page-title.php'; ?></h4>
			<div class="dropdown-divider"></div>
			<p class="card-description"><?php _e('启用的插件'); ?></p>
			<div class="table-responsive">
			  <table class="table">
				<thead>
				  <tr>
					<th> 名称 </th>
					<th> 描述 </th>
					<th> 版本 </th>
					<th> 作者 </th>
					<th> 操作 </th>
				  </tr>
				</thead>
				<tbody>
					<?php while ($activatedPlugins->next()): ?>
					<tr id="plugin-<?php $activatedPlugins->name(); ?>">
						<td><?php $activatedPlugins->title(); ?>
						<?php if (!$activatedPlugins->dependence): ?>
						<img src="<?php $options->adminUrl('images/notice.gif'); ?>" title="<?php _e('%s 无法在此版本的typecho下正常工作', $activatedPlugins->title); ?>" alt="<?php _e('%s 无法在此版本的typecho下正常工作', $activatedPlugins->title); ?>" class="tiny" />
						<?php endif; ?>
						</td>
						<td><?php $activatedPlugins->description(); ?></td>
						<td><?php $activatedPlugins->version(); ?></td>
						<td><?php echo empty($activatedPlugins->homepage) ? $activatedPlugins->author : '<a href="' . $activatedPlugins->homepage
						. '">' . $activatedPlugins->author . '</a>'; ?></td>
						<td style="white-space: nowrap;">
							<?php if ($activatedPlugins->activate || $activatedPlugins->deactivate || $activatedPlugins->config || $activatedPlugins->personalConfig): ?>
								<?php if ($activatedPlugins->config): ?>
									<a href="<?php $options->adminUrl('options-plugin.php?config=' . $activatedPlugins->name); ?>"><?php _e('设置'); ?></a>

								<?php endif; ?>
								<a lang="<?php _e('你确认要禁用插件 %s 吗?', $activatedPlugins->name); ?>" href="<?php $security->index('/action/plugins-edit?deactivate=' . $activatedPlugins->name); ?>"><?php _e('禁用'); ?></a>
							<?php else: ?>
								<span class="important"><?php _e('即插即用'); ?></span>
							<?php endif; ?>
						</td>
					</tr>
					<?php endwhile; ?>
					<?php if (!empty($activatedPlugins->activatedPlugins)): ?>
					<?php foreach ($activatedPlugins->activatedPlugins as $key => $val): ?>
					<tr>
					<td><?php echo $key; ?></td>
					<td colspan="3"><span class="warning"><?php _e('此插件文件已经损坏或者被不安全移除, 强烈建议你禁用它'); ?></span></td>
					<td><a lang="<?php _e('你确认要禁用插件 %s 吗?', $key); ?>" href="<?php $security->index('/action/plugins-edit?deactivate=' . $key); ?>"><?php _e('禁用'); ?></a></td>
					</tr>
					<?php endforeach; ?>
					<?php endif; ?>         
				</tbody>
			  </table>
			</div>
		  </div>
		</div>
	  </div>
	</div>

	<?php endif; ?>
	<?php Typecho_Widget::widget('Widget_Plugins_List@unactivated', 'activated=0')->to($deactivatedPlugins); ?>
	<?php if ($deactivatedPlugins->have() || !$activatedPlugins->have()): ?>
	<div class="row">
	  <div class="col-md-12 grid-margin stretch-card">
		<div class="card">
		  <div class="card-body">
			<h4 class="card-title"><?php include 'page-title.php'; ?></h4>
			<div class="dropdown-divider"></div>
			<p class="card-description"><?php _e('禁用的插件'); ?></p>
			<div class="table-responsive">
			  <table class="table">
				<thead>
				  <tr>
					<th> 名称 </th>
					<th> 描述 </th>
					<th> 版本 </th>
					<th> 作者 </th>
					<th> 操作 </th>
				  </tr>
				</thead>
				<tbody>
					<?php if ($deactivatedPlugins->have()): ?>
					<?php while ($deactivatedPlugins->next()): ?>
					<tr id="plugin-<?php $deactivatedPlugins->name(); ?>">
						<td><?php $deactivatedPlugins->title(); ?></td>
						<td><?php $deactivatedPlugins->description(); ?></td>
						<td><?php $deactivatedPlugins->version(); ?></td>
						<td><?php echo empty($deactivatedPlugins->homepage) ? $deactivatedPlugins->author : '<a href="' . $deactivatedPlugins->homepage
						. '">' . $deactivatedPlugins->author . '</a>'; ?></td>
						<td>
							<a href="<?php $security->index('/action/plugins-edit?activate=' . $deactivatedPlugins->name); ?>"><?php _e('启用'); ?></a>
						</td>
					</tr>
					<?php endwhile; ?>
					<?php else: ?>
					<tr>
						<td colspan="5"><h6 class="typecho-list-table-title"><?php _e('没有安装插件'); ?></h6></td>
					</tr>
					<?php endif; ?>
				</tbody>
			  </table>
			</div>
		  </div>
		</div>
	  </div>
	</div>
<?php endif; ?>
<?php
include 'copyright.php';
include 'common-js.php';
include 'footer.php';
?>