<?php
/**
 * AdminMD 集成自检（不依赖数据库）
 *
 * 目的：验证四件最容易被静默破坏的事情
 *   1. 插件类能被 Typecho 1.3.0 的 Plugin::portal() 正确定位并实例化；
 *   2. 钩子句柄在 Common::nativeClassName() 归一化前后能命中同一个键；
 *   3. \Widget\Menu 能在内核 Menu.php 未加载时被 AdminMD 接管；
 *   4. 登录页标记块安装/还原是幂等的。
 */

define('__TYPECHO_ROOT_DIR__', dirname(__DIR__, 4));
define('__TYPECHO_PLUGIN_DIR__', '/usr/plugins');
define('__TYPECHO_ADMIN__', true);

require_once __TYPECHO_ROOT_DIR__ . '/var/Typecho/Common.php';

use Typecho\Common;
use Typecho\Plugin;

$pass = 0;
$fail = 0;

function check($label, $ok, $detail = '')
{
    global $pass, $fail;
    if ($ok) {
        $pass++;
        echo "  PASS  {$label}\n";
    } else {
        $fail++;
        echo "  FAIL  {$label}" . ($detail !== '' ? "  -> {$detail}" : '') . "\n";
    }
}

echo "PHP " . PHP_VERSION . " / Typecho " . Common::VERSION . "\n\n";

// ---------------------------------------------------------------- 1. portal
echo "[1] 插件定位与类加载\n";

// 复刻 Widget/Init.php 中的类别名表（仅取用得到的部分）
define('__TYPECHO_CLASS_ALIASES__', [
    'Typecho_Plugin_Interface' => '\Typecho\Plugin\PluginInterface',
    'Widget_Abstract' => '\Widget\Base',
    'Helper' => '\Utils\Helper',
]);

[$file, $className] = Plugin::portal('AdminMD', __TYPECHO_ROOT_DIR__ . '/usr/plugins');
check('portal() 找到 Plugin.php', realpath($file) === realpath(__TYPECHO_ROOT_DIR__ . '/usr/plugins/AdminMD/Plugin.php'), $file);
check('portal() 返回命名空间类名', $className === '\TypechoPlugin\AdminMD\Plugin', $className);

require_once $file;
check('AdminMD_Plugin 已声明', class_exists('AdminMD_Plugin', false));
check('实现 PluginInterface', isset(class_implements('AdminMD_Plugin')['Typecho\Plugin\PluginInterface']));

// ---------------------------------------------------------------- 2. 钩子句柄
echo "\n[2] 钩子句柄归一化\n";

$cases = [
    'admin/header.php',
    'admin/footer.php',
    'admin/menu.php',
    'admin/common.php',
    'index.php',
    'Widget\Menu',
];

foreach ($cases as $handle) {
    $normalized = Common::nativeClassName($handle);
    check(
        "nativeClassName('{$handle}') 幂等",
        Common::nativeClassName($normalized) === $normalized,
        "{$handle} -> {$normalized} -> " . Common::nativeClassName($normalized)
    );
}

// 关键反例：1.3.0 核心菜单用 Plugin::factory(Menu::class) 生成句柄，
// 插件若注册 'Widget\Menu' 会与 'Widget\Menu' 归一到同一形态吗？
check(
    '斜杠句柄与反斜杠句柄可区分（这正是不能用反斜杠的原因）',
    Common::nativeClassName('admin/header.php') !== Common::nativeClassName('admin\header.php'),
    Common::nativeClassName('admin/header.php') . ' vs ' . Common::nativeClassName('admin\header.php')
);

// ---------------------------------------------------------------- 3. 菜单接管
echo "\n[3] \\Widget\\Menu 接管\n";
check('接管前 \\Widget\\Menu 未加载', !class_exists('\Widget\Menu', false));

$menuImpl = __TYPECHO_ROOT_DIR__ . '/usr/plugins/AdminMD/var/AdminMD/Menu.php';
require_once $menuImpl;
check('AdminMD\\Widget\\Menu 可加载', class_exists('\AdminMD\Widget\Menu'));
check('AdminMD 菜单未继承内核 Widget\\Base', !is_subclass_of('\AdminMD\Widget\Menu', '\Widget\Base'));

$aliased = class_alias('\AdminMD\Widget\Menu', '\Widget\Menu');
check('class_alias 接管成功', $aliased === true);
check('\\Widget\\Menu 指向 AdminMD 实现', (new ReflectionClass('\Widget\Menu'))->getName() === 'AdminMD\Widget\Menu');
check('接管后内核 Menu.php 未载入', !class_exists('\Widget\Menu', false) || (new ReflectionClass('\Widget\Menu'))->getName() === 'AdminMD\Widget\Menu');

// 内核确有条目：确认从模板调用 $menu->output() 不会因签名不符报错
$ref = new ReflectionMethod('\Widget\Menu', 'output');
$params = array_map(fn($p) => $p->getName() . '=' . var_export($p->isDefaultValueAvailable() ? $p->getDefaultValue() : null, true), $ref->getParameters());
check('output() 签名兼容内核调用', implode(',', array_keys(array_flip($params))) !== '' && $ref->getNumberOfParameters() === 2, implode(', ', $params));

$ref = new ReflectionMethod('\Widget\Menu', 'getCurrentMenuUrl');
check('getCurrentMenuUrl() 返回 string', (string) $ref->getReturnType() === 'string');

// 回归：每个钩子名都必须能在内核源码中找到对应的调用点。
// 历史上曾把 header 误写成 filter_2000 与 bootstrapMenu，导致钩子静默失效。
$hooks = (new ReflectionMethod('AdminMD_Plugin', 'hooks'))->invoke(null);

$expectedCallpoints = [
    'admin/common.php' => "/factory\('admin\/common\.php'\)->call\('([^']+)'\)/",
    'admin/header.php' => "/factory\('admin\/header\.php'\)->filter\('([^']+)'/",
    'admin/footer.php' => "/factory\('admin\/footer\.php'\)->call\('([^']+)'\)/",
    'admin/menu.php' => "/factory\('admin\/menu\.php'\)->call\('([^']+)'\)/",
];

foreach ($hooks as $hook) {
    [$component, $type, $name, $callback, $weight] = $hook;
    $src = file_get_contents(__TYPECHO_ROOT_DIR__ . '/' . $component);

    if ($component === 'admin/footer.php') {
        // footer.php 有 begin 与 end 两处 call，取最后一个
        preg_match_all($expectedCallpoints[$component], $src, $mm);
        $native = end($mm[1]);
    } else {
        preg_match($expectedCallpoints[$component], $src, $mm);
        $native = $mm[1] ?? null;
    }

    check(
        "{$component} 钩子名 '{$name}' 与内核一致",
        $native !== null && $name === $native,
        '内核=' . var_export($native, true) . ' 插件=' . var_export($name, true)
    );

    check(
        "{$component} 回调 {$callback} 存在",
        method_exists('AdminMD_Plugin', $callback)
    );
}

// 回归：模拟内核真实调用，验证 filter('header', …) 能命中插件回调。
// 历史上曾用 `filter_权重` 注册，得到 handle:filter 键，与内核的 handle:header 不符。
\Typecho\Plugin::init(['activated' => [], 'handles' => []]);
$probeClass = new class {
    public static function probe($value = '', ...$rest)
    {
        return $value . '[HIT]';
    }
};
\Typecho\Plugin::factory('admin/header.php')->header_2000 = [$probeClass, 'probe'];
$probeOut = \Typecho\Plugin::factory('admin/header.php')->filter('header', 'BASE');
check('filter 钩子可被内核调用命中', $probeOut === 'BASE[HIT]', var_export($probeOut, true));

// ---------------------------------------------------------------- 4. 登录页补丁
echo "\n[4] 登录页标记块\n";

$loginTarget = __TYPECHO_ROOT_DIR__ . '/admin/login.php';
$backup = __TYPECHO_ROOT_DIR__ . '/admin/login.php.__testbackup';
copy($loginTarget, $backup);
$originalHash = md5_file($loginTarget);

$install = new ReflectionMethod('AdminMD_Plugin', 'installTemplates');
$uninstall = new ReflectionMethod('AdminMD_Plugin', 'uninstallTemplates');

try {
    $install->invoke(null);
    $patched = file_get_contents($loginTarget);
    check('已注入标记块', strpos($patched, 'AdminMD:patch:begin') !== false);
    check('接管行位于 common.php 之后', strpos($patched, 'admin/login.php') > strpos($patched, "include 'common.php';"));
    check('接管使用了 return 终止', strpos($patched, 'return;') !== false);

    // 回归：注入后的文件必须仍是合法 PHP（历史上曾因在 <?php 段内重复插入标签而解析失败）
    $lintOut = [];
    $lintCode = 0;
    exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($loginTarget) . ' 2>&1', $lintOut, $lintCode);
    check('注入后语法合法', $lintCode === 0, implode(' ', $lintOut));

    // 回归：注入后不得出现嵌套的 PHP 开始标签
    check('无嵌套开始标签', substr_count($patched, '<?php') === substr_count(file_get_contents($backup), '<?php'));

    $install->invoke(null);
    $twice = file_get_contents($loginTarget);
    check('重复安装幂等', substr_count($twice, 'AdminMD:patch:begin') === 1, 'count=' . substr_count($twice, 'AdminMD:patch:begin'));

    $uninstall->invoke(null);
    check('禁用后完全还原', md5_file($loginTarget) === $originalHash);
    check('还原后无残留标记', strpos(file_get_contents($loginTarget), 'AdminMD:patch') === false);
} finally {
    copy($backup, $loginTarget);
    @unlink($backup);
}

echo "\n----------------------------------------\n";
echo "通过 {$pass} 项，失败 {$fail} 项\n";
exit($fail === 0 ? 0 : 1);
