<?php
/**
 * AdminMD 是一款基于 Material Design 的 Typecho 后台美化插件
 *
 * 兼容 Typecho 1.1 / 1.2.x / 1.3.0；兼容 PHP 7.0 - 8.5（1.3.0 分支需 PHP 8.0+）。
 *
 * 三条设计约束：
 *   1. 不修改 var/Widget/Menu.php。1.3.0 中该类为强类型 Widget，整类替换文件必然
 *      致命错误；本插件改用 class_alias 在「内核 Menu.php 尚未被加载」的时间窗内
 *      就近接管，接管失败会自动回退原生菜单，绝不导致后台白屏。
 *   2. 不在 admin/ 下生成 .bak。仅对登录页做「标记块」式覆盖，禁用时精确剔除，
 *      还原结果与原始文件逐字节一致。
 *   3. 钩子形态按内核版本分流。1.3.0 起插件句柄会被 Common::nativeClassName()
 *      归一化，注册时必须使用归一化前后一致的斜杠式字符串句柄，否则钩子静默失效。
 *
 * @package AdminMD
 * @author Magneto
 * @version 2.1.0
 * @link https://www.fmcf.cc
 * @since 1.1
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

require_once __DIR__ . '/var/AdminMD/Core.php';

/**
 * 当前环境是否走 1.3.0+ 现代形态
 *
 * 1.3.0 同时要求 PHP 8（其源码已使用联合类型、str_starts_with 等特性）。
 *
 * @return bool
 */
function adminmd_is_modern()
{
    return version_compare(PHP_VERSION, '8.0.0', '>=')
        && version_compare(AdminMD_Core::typechoVersion(), '1.3.0', '>=');
}

/**
 * 关于 Typecho_Plugin_Interface 的解析
 *
 * 1.2.x 及以下：接口定义在 var/Typecho/Plugin.php，由自动加载器直接解析。
 * 1.3.0 及以上：接口更名为 Typecho\Plugin\PluginInterface，Typecho_Plugin_Interface
 *   作为别名登记在 __TYPECHO_CLASS_ALIASES__ 中；自动加载该类名时，内核会自动
 *   把两个名字都建立好。
 *
 * 因此这里不做任何手工 class_alias —— 实测表明手工建立别名会与内核加载器冲突，
 * 触发「Cannot redeclare interface Typecho_Plugin_Interface」。
 */

if (adminmd_is_modern()) {
    require_once __DIR__ . '/var/AdminMD/Menu.php';
}

if (adminmd_is_modern() && !class_exists('AdminMD_Plugin', false)) {
    /**
     * AdminMD 插件（Typecho 1.3.0+ / PHP 8+）
     */
    class AdminMD_Plugin extends AdminMD_Core implements Typecho_Plugin_Interface
    {
        /**
         * 声明全部钩子
         *
         * 每项格式：[组件, 调用方式, 钩子名, 回调方法, 权重]
         *   - 钩子名必须与内核源码中 `->call('xxx')` / `->filter('xxx', …)` 的 xxx 完全一致；
         *   - 调用方式只决定注册写法（call 用 `名_权重`，filter 用 `filter_权重`），
         *     它不参与钩子键名的匹配。
         *
         * @return array
         */
        protected static function hooks()
        {
            return [
                // admin/common.php:18 ->call('begin')
                // 必须最靠前：在 Widget\Menu 被实例化之前完成接管
                ['admin/common.php', 'call', 'begin', 'begin', 1],

                // admin/header.php:11 ->filter('header', $header)
                ['admin/header.php', 'filter', 'header', 'renderHeader', 2000],

                // admin/footer.php ->call('end')
                ['admin/footer.php', 'call', 'end', 'renderFooter', 2000],

                // admin/menu.php ->call('navBar')
                ['admin/menu.php', 'call', 'navBar', 'renderMenuBar', 2000],
            ];
        }

        /**
         * 注册单个钩子
         *
         * 句柄使用斜杠式字符串，与内核 Plugin::factory('admin/xxx.php') 归一化后一致。
         *
         * 注意 Typecho 的 __set 语义：`$handle->{X_Y} = $cb` 会被解析为
         * 「组件名 X、权重 Y」，最终键为 handle:X。因此 filter 与 call 的写法完全一致，
         * 都是「钩子名_权重」，**不存在** `filter_权重` 这种写法——那样得到的键是
         * handle:filter，永远不会被内核的 filter('header', …) 命中。
         *
         * @param array $hook
         * @return void
         */
        protected static function registerHook(array $hook)
        {
            [$component, $type, $name, $callback, $weight] = $hook;

            \Typecho\Plugin::factory($component)->{$name . '_' . $weight} = [static::class, $callback];
        }

        /**
         * admin/menu.php 的 navBar 钩子：挂载 AdminMD 的侧边抽屉
         *
         * 仅在已登录的后台页面输出；未登录时保持原生登录页布局不受影响。
         *
         * @return void
         */
        public static function renderMenuBar()
        {
            if (!self::isAdminRequest() || !self::isLoggedIn()) {
                return;
            }

            if (self::configValue('disableskin', '0') === '1') {
                return;
            }

            $view = __DIR__ . '/var/AdminMD/menubar.php';
            if (file_exists($view)) {
                require $view;
            }
        }

        /**
         * admin/header.php 的 filter 钩子
         *
         * @return string
         */
        public static function renderHeader()
        {
            $header = parent::renderHeader();

            // 1.3.0 登录页的「下次自动登录」复选框缺少 name 属性，勾选后不会被提交，
            // 这里补一行内联脚本修正，不影响其余逻辑。
            if (self::isAdminRequest() && !self::isLoggedIn()) {
                $header .= '<script>(function(){var b=document.getElementById("remember");'
                    . 'if(b&&!b.getAttribute("name")){b.setAttribute("name","remember");}})();</script>';
            }

            return $header;
        }

        /**
         * admin/common.php 的 begin 钩子：接管后台菜单
         *
         * 时机：Widget\Init 已完成类别名注册，但 Widget\Menu 尚未被任何代码加载。
         * 只要 AdminMD 的菜单实现不继承 Widget\Base（否则会连带加载内核 Menu.php），
         * class_alias 即可在此刻成功。
         *
         * @return void
         */
        public static function begin()
        {
            self::bootstrapMenu();
        }

        /**
         * 就近接管 \Widget\Menu
         *
         * @return void
         */
        public static function bootstrapMenu()
        {
            if (!self::isMenuReplacementAllowed()) {
                return;
            }

            // 内核已加载（例如其他插件提前触发）则不再干预，避免覆盖失败
            if (class_exists('\\Widget\\Menu', false)) {
                return;
            }

            if (!class_exists('\\AdminMD\\Widget\\Menu')) {
                return;
            }

            try {
                class_alias('\\AdminMD\\Widget\\Menu', '\\Widget\\Menu');
            } catch (\Throwable $e) {
                // 接管失败时静默回退原生菜单，保证后台始终可用
            }
        }

        /**
         * 是否允许接管菜单
         *
         * 任一条件不满足即回退原生菜单，确保上游 1.3.x 后续改动不会造成致命错误。
         *
         * @return bool
         */
        protected static function isMenuReplacementAllowed()
        {
            if (self::configValue('disableskin', '0') === '1') {
                return false;
            }

            $menuClass = 'AdminMD\\Widget\\Menu';

            if (!class_exists($menuClass)) {
                return false;
            }

            foreach (['execute', 'getCurrentMenu', 'getCurrentMenuUrl', 'output'] as $method) {
                if (!method_exists($menuClass, $method)) {
                    return false;
                }
            }

            // 自行实现若意外继承了 Widget\Base，class_alias 必然失败，提前让位
            return !is_subclass_of($menuClass, 'Widget\\Base');
        }
    }
}

if (!adminmd_is_modern() && !class_exists('AdminMD_Plugin', false)) {
    /**
     * AdminMD 插件（Typecho 1.2.x 及以下 / PHP 7.0+）
     *
     * 1.3.0 之前没有 Typecho\Plugin 命名空间类，也没有 filter() 钩子，
     * 内核以 call 语义触发 header/footer；内核 Menu 仍是非强类型实现，
     * 且旧版后台模板由本插件整体接管，故不做菜单替换。
     */
    class AdminMD_Plugin extends AdminMD_Core implements Typecho_Plugin_Interface
    {
        /**
         * 格式：[组件, 调用方式, 钩子名, 回调方法, 权重]
         *
         * @return array
         */
        protected static function hooks()
        {
            return [
                ['admin/header.php', 'call', 'header', 'renderHeader', 2000],
                ['admin/footer.php', 'call', 'end', 'renderFooter', 2000],
            ];
        }

        /**
         * @param array $hook
         * @return void
         */
        protected static function registerHook(array $hook)
        {
            [$component, $type, $name, $callback, $weight] = $hook;

            Typecho_Plugin::factory($component)->{$name . '_' . $weight} = [static::class, $callback];
        }

        /**
         * header 钩子（传统 call 语义：无返回值，直接输出到页面）
         *
         * @param string $header
         * @return void
         */
        public static function renderHeader($header = '')
        {
            echo self::headHtml(is_string($header) ? $header : '');
        }

        /**
         * footer 钩子（传统 call 语义）
         *
         * @return void
         */
        public static function renderFooter()
        {
            echo self::footHtml();
        }
    }
}
