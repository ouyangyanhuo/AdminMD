<?php
/**
 * AdminMD 后台抽屉与导航（Typecho 1.3.0+）
 *
 * 由 admin/menu.php 的 navBar 钩子引入。1.3.0 后台为「顶部导航 + 侧边抽屉」，
 * 这里输出与之契合的轻量结构，不依赖 mdui / 图标字体等外部 CDN。
 *
 * 关键约束：本文件在 admin/header.php 输出 <body> 之后才被 include，
 * 因此这里输出的 <aside> 会落在 <body> 内、可能位于 <nav> 内部。
 * 浏览器对 aside 的嵌套是宽容的，但为了结构合法，脚本会在 DOMContentLoaded 时
 * 把抽屉节点移动到 body 末尾（见 assets/js/admin.js 的 adopt 逻辑）。
 *
 * 可用变量：$menu、$user、$options。
 *
 * @package AdminMD
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

if (!isset($menu) || !is_object($menu) || !method_exists($menu, 'output')) {
    return;
}

$adminmdVersion = AdminMD_Core::assetVersion();
$adminmdUrl = AdminMD_Core::assetUrl();

$adminmdTitle = isset($menu->title) && $menu->title !== '' ? (string) $menu->title : _t('后台');
$adminmdAddLink = !empty($menu->addLink) ? (string) $menu->addLink : '';

$adminmdJsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
if (defined('JSON_UNESCAPED_UNICODE')) {
    $adminmdJsonFlags |= JSON_UNESCAPED_UNICODE;
}
?>
<button type="button" class="adminmd-trigger" id="adminmd-trigger"
        aria-label="<?php _e('菜单'); ?>" aria-expanded="false" aria-controls="adminmd-drawer">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M3 6h18v2H3V6zm0 5h18v2H3v-2zm0 5h18v2H3v-2z"/>
    </svg>
    <span class="adminmd-trigger-label"><?php _e('菜单'); ?></span>
</button>

<div class="adminmd-overlay" id="adminmd-overlay" hidden></div>

<aside class="adminmd-drawer" id="adminmd-drawer" role="navigation" aria-label="<?php _e('后台菜单'); ?>">
    <div class="adminmd-drawer-brand">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1a73e8" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 2L2 7l10 5 10-5-10-5z"/>
            <path d="M2 17l10 5 10-5"/>
            <path d="M2 12l10 5 10-5"/>
        </svg>
        <span><?php _e('%s 控制台', $options->title); ?></span>
    </div>

    <nav class="adminmd-drawer-list" id="adminmd-menu">
        <?php $menu->output(); ?>
    </nav>

    <div class="adminmd-drawer-foot">
        <span>AdminMD <?php echo htmlspecialchars(AdminMD_Core::VERSION, ENT_QUOTES, 'UTF-8'); ?></span>
        <span>Typecho <?php echo htmlspecialchars(AdminMD_Core::typechoVersion(), ENT_QUOTES, 'UTF-8'); ?></span>
    </div>
</aside>

<?php if ($adminmdAddLink !== ''): ?>
<div class="adminmd-addlink" hidden>
    <a href="<?php echo htmlspecialchars($adminmdAddLink, ENT_QUOTES, 'UTF-8'); ?>"><?php _e('新增'); ?></a>
</div>
<?php endif; ?>

<script>
window.AdminMD = window.AdminMD || {};
window.AdminMD.version = <?php echo json_encode($adminmdVersion, $adminmdJsonFlags); ?>;
window.AdminMD.currentTitle = <?php echo json_encode($adminmdTitle, $adminmdJsonFlags); ?>;
<?php if ($adminmdAddLink !== ''): ?>
window.AdminMD.addLink = <?php echo json_encode($adminmdAddLink, $adminmdJsonFlags); ?>;
<?php endif; ?>
</script>
