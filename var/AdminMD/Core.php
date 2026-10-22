<?php
/**
 * AdminMD 核心实现（基类）
 *
 * 该文件只负责业务逻辑，不负责"钩子名如何书写"。
 * 具体的注册形态由 Plugin.php 中的门面（facade）决定：
 *   - PHP 8 + Typecho 1.3.0  -> 命名空间式钩子（字符串句柄，规避 nativeClassName 归一化）
 *   - 旧版 Typecho / 旧 PHP   -> 传统 Typecho\Plugin::factory(...) 写法
 *
 * 之所以要拆成两套门面：Typecho 1.3.0 的 Typecho\Plugin::__construct() 会对句柄做
 * Common::nativeClassName() 归一化（'\Widget\Menu' -> 'Widget_Menu'），导致插件若注册
 * 'Widget\Menu' 与核心 `use Widget\Menu; Plugin::factory(Menu::class)` 生成的
 * 'Widget\Menu' 无法命中同一个键，钩子静默失效。因此 1.3.0 分支统一使用斜杠式字符串
 * 句柄，该形态在归一化前后完全一致。
 *
 * @package AdminMD
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * AdminMD 插件基类
 */
abstract class AdminMD_Core
{
    /** 插件版本 */
    const VERSION = '2.1.0';

    /** 插件目录名 */
    const DIR = 'AdminMD';

    /**
     * 已知的美化页面模板（相对插件 admin/ 目录）
     *
     * 说明：Typecho 1.3.0 已重构后台 DOM 与模板职责，index.php / copyright.php /
     * write-post.php / category.php / backup.php 等页面在其上不具备可移植的覆盖价值，
     * 因此本版本仅保留登录页覆盖，其余全部改由样式注入实现。
     *
     * @var array
     */
    protected static $templates = ['login.php'];

    /**
     * 激活时注册的全部钩子
     *
     * 子类必须实现，返回 [[component, type, method, weight], ...]
     *   - component: 1.3.0 形态的字符串句柄（斜杠式）
     *   - type:      call | filter
     *   - method:    本类中的静态方法名
     *   - weight:    权重（越大越靠后执行）
     *
     * @return array
     */
    abstract protected static function hooks();

    /**
     * 把统一的钩子描述注册到当前 Typecho 版本
     *
     * @param array $hook [component, type, method, weight]
     * @return void
     */
    abstract protected static function registerHook(array $hook);

    /**
     * 本插件是否已登记过任何钩子
     *
     * Typecho 只在「未启用」时调用 activate()，但插件重装、从备份恢复 options
     * 等场景仍可能重复调用。重复登记会让同一回调被写入多个权重位（Typecho 在
     * 权重冲突时会自动递增为 -X.001），从而重复执行——例如抽屉被输出多份。
     * 因此这里做一次性判断：已登记则不再重复注册。
     *
     * @return bool
     */
    protected static function hasRegisteredHooks()
    {
        try {
            $export = Typecho_Plugin::export();
        } catch (\Throwable $e) {
            return false;
        }

        $owned = $export['activated'][self::DIR]['handles'] ?? [];

        return !empty($owned) && is_array($owned);
    }

    // ------------------------------------------------------------------
    // 生命周期
    // ------------------------------------------------------------------

    /**
     * 启用插件
     *
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        self::assertEnvironment();

        // 幂等：已登记过就不再重复注册（详见 hasRegisteredHooks 注释）
        if (!self::hasRegisteredHooks()) {
            foreach (static::hooks() as $hook) {
                static::registerHook($hook);
            }
        }

        self::installTemplates();
    }

    /**
     * 禁用插件
     *
     * @return void
     */
    public static function deactivate()
    {
        self::uninstallTemplates();
    }

    /**
     * 环境自检
     *
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    protected static function assertEnvironment()
    {
        if (!is_dir(self::rootPath() . '/admin')) {
            throw new Typecho_Plugin_Exception(
                _t('未找到 Typecho 后台目录，AdminMD 需要安装在标准目录结构中')
            );
        }

        $adminDoc = self::rootPath() . '/admin';
        if (!is_writable($adminDoc)) {
            throw new Typecho_Plugin_Exception(
                _t('后台目录不可写，无法安装登录页美化模板：%s', $adminDoc)
            );
        }
    }

    // ------------------------------------------------------------------
    // 路径与版本工具
    // ------------------------------------------------------------------

    /**
     * 站点根目录（不带末尾斜杠）
     *
     * @return string
     */
    public static function rootPath()
    {
        return rtrim(str_replace('\\', '/', __TYPECHO_ROOT_DIR__), '/');
    }

    /**
     * 插件目录绝对路径
     *
     * 注意：本文件位于 <插件根>/var/AdminMD/，需回退两级才是插件根目录。
     *
     * @return string
     */
    public static function pluginPath()
    {
        return rtrim(str_replace('\\', '/', dirname(__FILE__, 3)), '/');
    }

    /**
     * 当前 Typecho 版本号
     *
     * @return string
     */
    public static function typechoVersion()
    {
        if (class_exists('Typecho\Common') && defined('Typecho\Common::VERSION')) {
            return \Typecho\Common::VERSION;
        }

        if (class_exists('Typecho_Common') && defined('Typecho_Common::VERSION')) {
            return Typecho_Common::VERSION;
        }

        return '0.0.0';
    }

    /**
     * 后台静态资源缓存戳
     *
     * 旧实现使用 explode('/', $options->version) 取版本号做 querystring，
     * 该字段在 1.3.0 中可能为空或被版本检查逻辑改写，会触发 undefined offset 警告。
     * 这里改为"插件版本 + 内核版本"的稳定组合。
     *
     * @return string
     */
    public static function assetVersion()
    {
        return self::VERSION . '-' . preg_replace('/[^0-9a-zA-Z.]/', '', self::typechoVersion());
    }

    /**
     * 插件内静态资源的公开 URL 前缀（带尾斜杠）
     *
     * @return string
     */
    public static function assetUrl()
    {
        $options = Helper::options();
        return rtrim($options->pluginUrl, '/') . '/' . self::DIR . '/';
    }

    /**
     * 后台是否已登录
     *
     * @return bool
     */
    protected static function isLoggedIn()
    {
        $user = Typecho_Widget::widget('Widget_User');

        return $user instanceof Typecho_Widget && $user->hasLogin();
    }

    /**
     * 读取 Cookie（兼容 PHP 8.1+ 的 null 语义）
     *
     * Typecho_Cookie::get() 在键不存在时返回 null，直接传给 htmlspecialchars()
     * 会在 PHP 8.1+ 触发 deprecated 警告。此处统一转换为字符串。
     *
     * @param string $name
     * @return string
     */
    public static function readCookie($name)
    {
        $value = Typecho_Cookie::get($name);

        return is_string($value) ? $value : '';
    }

    /**
     * 判断当前请求是否后台请求
     *
     * @return bool
     */
    protected static function isAdminRequest()
    {
        return defined('__TYPECHO_ADMIN__');
    }

    // ------------------------------------------------------------------
    // 模板安装 / 还原
    // ------------------------------------------------------------------

    /**
     * 安装页面覆盖模板
     *
     * 采用"标记块"策略而非 .bak 文件重命名：
     *   - 原文件在 __TYPECHO_ADMIN__ 之前插入 5 行标记，禁用时按标记精确剔除；
     *   - 不产生 .bak 垃圾文件，也不会因多次启用/禁用而嵌套嵌套再嵌套。
     *
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    protected static function installTemplates()
    {
        foreach (self::$templates as $file) {
            $source = self::pluginPath() . '/admin/' . $file;
            $target = self::rootPath() . '/admin/' . $file;

            if (!file_exists($source)) {
                continue;
            }

            // 已经安装过则先还原，保证幂等
            self::restoreTemplate($target);

            $original = @file_get_contents($target);
            if ($original === false) {
                throw new Typecho_Plugin_Exception(_t('无法读取 %s', $target));
            }

            $patched = self::buildPatchedContent($original);
            if ($patched === null) {
                throw new Typecho_Plugin_Exception(
                    _t('%s 的结构与预期不符，AdminMD 无法安全地安装登录页模板', $target)
                );
            }

            if (@file_put_contents($target, $patched) === false) {
                throw new Typecho_Plugin_Exception(_t('无法写入 %s', $target));
            }
        }
    }

    /**
     * 还原全部被覆盖的模板
     *
     * @return void
     */
    protected static function uninstallTemplates()
    {
        foreach (self::$templates as $file) {
            self::restoreTemplate(self::rootPath() . '/admin/' . $file);
        }
    }

    /**
     * 从目标文件中剔除 AdminMD 标记块，恢复原始内容
     *
     * 标记块由 AdminMD:patch:begin 与 AdminMD:patch:end 两条单行注释界定，
     * 二者之间是本插件注入的接管代码。还原时整段剔除，不得残留空白代码段。
     *
     * @param string $target
     * @return bool 是否发生了还原动作
     */
    protected static function restoreTemplate($target)
    {
        if (!file_exists($target)) {
            return false;
        }

        $content = @file_get_contents($target);
        if ($content === false || strpos($content, self::markerBegin()) === false) {
            return false;
        }

        // 标记块位于原代码段内，因此只匹配「前导换行 + 标记内容 + 结尾换行」，
        // 不包含任何 PHP 标签，避免破坏原文件结构
        $pattern = '/\r?\n' . preg_quote(self::markerBegin(), '/') . '\b.*?'
            . preg_quote(self::markerEnd(), '/') . '\b\r?\n/s';

        $restored = preg_replace($pattern, '', $content, 1);

        // 无法完整匹配时不做任何写入，宁可保留现状也不产出半截代码
        if ($restored === null || strpos($restored, self::markerBegin()) !== false) {
            return false;
        }

        return @file_put_contents($target, $restored) !== false;
    }

    /**
     * 生成覆盖后的登录页内容
     *
     * 结构约定：在原文件 `include 'common.php';` 之后插入一段以开始标签包裹的
     * 自包含代码，其中首尾分别以 AdminMD:patch:begin / AdminMD:patch:end 标记。
     *
     * 位置选择的理由：common.php 负责载入 config.inc.php 并定义 __TYPECHO_ROOT_DIR__，
     * 同时把 $options / $user / $security / $menu 注入当前作用域。登录页模板依赖这些
     * 常量与变量，因此接管必须发生在 common.php 之后、页面主体渲染之前。
     *
     * @param string $original
     * @return string|null 结构不符时返回 null
     */
    protected static function buildPatchedContent($original)
    {
        $needle = "include 'common.php';";

        if (strpos($original, $needle) === false) {
            return null;
        }

        // 已注入过则保持幂等
        if (strpos($original, self::markerBegin()) !== false) {
            return $original;
        }

        // 插入点位于 `include 'common.php';` 之后，此时仍处于原文件的代码段内，
        // 因此标记块内部不能再出现任何开始或结束标签，否则会造成标签嵌套解析错误。
        $block = "\n" . self::markerBegin() . "\n"
            . '// AdminMD 美化模板（由插件自动安装，禁用插件后原样还原，请勿手工修改此段）' . "\n"
            . '$AdminMD_plugin = __TYPECHO_ROOT_DIR__ . __TYPECHO_PLUGIN_DIR__ . \'/'
            . self::DIR . '/admin/login.php\';' . "\n"
            . 'if (file_exists($AdminMD_plugin)) {' . "\n"
            . '    require $AdminMD_plugin;' . "\n"
            . '    unset($AdminMD_plugin);' . "\n"
            . '    return;' . "\n"
            . '}' . "\n"
            . 'unset($AdminMD_plugin);' . "\n"
            . self::markerEnd() . "\n";

        $insertAt = strpos($original, $needle) + strlen($needle);

        return substr($original, 0, $insertAt) . $block . substr($original, $insertAt);
    }

    /**
     * 标记块起始行
     *
     * 必须使用单行注释形态：C 风格注释不属于 PHP 指令，一旦落在开始标签之外
     * 会被原样打印到页面上。这里使用 `//` 开头、带唯一令牌的注释，
     * 既不产生任何输出，又能被正则精确匹配。
     *
     * @return string
     */
    protected static function markerBegin()
    {
        return self::markerToken('begin');
    }

    /**
     * 标记块结束行
     *
     * @return string
     */
    protected static function markerEnd()
    {
        return self::markerToken('end');
    }

    /**
     * 生成标记行
     *
     * @param string $phase
     * @return string
     */
    protected static function markerToken($phase)
    {
        return '// AdminMD:patch:' . $phase;
    }

    // ------------------------------------------------------------------
    // 配置面板
    // ------------------------------------------------------------------

    /**
     * 获取插件配置面板
     *
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $url = self::assetUrl();

        echo '<link rel="stylesheet" href="' . htmlspecialchars($url . 'login.css', ENT_QUOTES, 'UTF-8') . '">'
            . '<div id="AdminMD-version" style="display:none; padding:10px; margin-bottom:15px; background:#f8f9fa;'
            . 'border-radius:4px; border-left:4px solid #4285f4;"></div>'
            . '<script src="' . htmlspecialchars($url . 'js/version-check.js', ENT_QUOTES, 'UTF-8') . '"></script>';

        $notice = new Typecho_Widget_Helper_Form_Element_Text('TheNotice', null, null, _t('<h2>基础外观</h2>'));
        $notice->input->setAttribute('style', 'display:none');
        $form->addInput($notice);

        $zz1 = '<div class="zz">绿色[你的名字]</div>';
        $zz2 = '<div class="zz">蓝天和远山[网络]</div>';
        $zz3 = '<div class="zz">后花园[Pixiv 95741043]</div>';
        $zz4 = '<div class="zz">枫[Pixiv 66986409]</div>';
        $zz5 = '<div class="zz">Default</div>';

        $skin = new Typecho_Widget_Helper_Form_Element_Radio('bgfengge', [
            'Green' => _t('<div class="kuai"><img src="https://p5.qhimg.com/bdm/960_593_0/t01573b4f467fdf51e2.jpg" loading="lazy" alt="">' . $zz1 . '</div>'),
            'BlueSkyAndMountains' => _t('<div class="kuai"><img src="https://cdn.jsdelivr.net/gh/fyhgay/CDNS@latest/2021/07/15/0531f7895a5627b8737e0690d7dcb4e5.png" loading="lazy" alt="">' . $zz2 . '</div>'),
            'Back_garden' => _t('<div class="kuai"><img src="https://tva4.sinaimg.cn/large/008aATBzly8gze1cpfkuuj31i00u0k56.jpg" loading="lazy" alt="">' . $zz3 . '</div>'),
            'Maple' => _t('<div class="kuai"><img src="https://tva4.sinaimg.cn/large/008aATBzly1h07hmpmbj5j30yq0g6dvm.jpg" loading="lazy" alt="">' . $zz4 . '</div>'),
            'Default' => _t('<div class="kuai"><img src="https://fastly.jsdelivr.net/gh/fyhgay/CDNS@latest/2021/07/15/05b54e433729eb89a067ff992176c442.png" loading="lazy" alt="">' . $zz5 . '</div>'),
        ], 'Green', _t('登录页样式'), _t(''));
        $skin->setAttribute('id', 'yangshi');
        $form->addInput($skin);

        $bgUrl = new Typecho_Widget_Helper_Form_Element_Text(
            'bgUrl',
            null,
            null,
            _t('自定义背景图'),
            _t('选中上方的基础样式后，可以在这里填写图片地址自定义背景图')
        );
        $form->addInput($bgUrl);

        $diyCss = new Typecho_Widget_Helper_Form_Element_Textarea(
            'diycss',
            null,
            null,
            _t('自定义样式'),
            _t('选择【Default】样式后在这里编写 CSS；该功能与【自定义背景图】冲突，如需背景图请一并写入 CSS。')
        );
        $form->addInput($diyCss);

        $adminCss = new Typecho_Widget_Helper_Form_Element_Textarea(
            'admincss',
            null,
            null,
            _t('后台自定义样式'),
            _t('追加到后台每个页面的 CSS，用于微调 1.3.0 新界面。留空则使用内置样式。')
        );
        $form->addInput($adminCss);

        $disableSkin = new Typecho_Widget_Helper_Form_Element_Radio(
            'disableskin',
            ['0' => _t('启用（使用内置后台美化）'), '1' => _t('禁用（完全保留 Typecho 原生后台外观）')],
            '0',
            _t('后台美化开关'),
            _t('当内置样式与你的插件冲突时，可临时关闭后台美化，保留登录页美化。')
        );
        $form->addInput($disableSkin);
    }

    /**
     * 个人用户的配置面板
     *
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    // ------------------------------------------------------------------
    // 安全过滤
    // ------------------------------------------------------------------

    /**
     * 过滤 CSS 内容，防止 XSS
     *
     * @param string $css
     * @return string
     */
    public static function sanitizeCss($css)
    {
        $css = strip_tags((string) $css);
        $css = preg_replace('/expression\s*\(/i', '(', $css);
        $css = preg_replace('/url\s*\(\s*["\']?\s*javascript:/i', 'url(', $css);
        $css = preg_replace('/@import\s+url\s*\(\s*["\']?\s*javascript:/i', '@import url(', $css);

        // 阻断 CSS 中可能触发的闭合标签注入
        return str_replace(['</style', '</script'], '', $css);
    }

    /**
     * 过滤 URL
     *
     * @param string $url
     * @return string
     */
    public static function sanitizeUrl($url)
    {
        $url = trim(strip_tags((string) $url));

        if ($url === '') {
            return '';
        }

        if (!preg_match('/^https?:/i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }

        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }

    /**
     * 读取插件配置项（缺失时返回默认值）
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected static function configValue($name, $default = null)
    {
        $options = Helper::options();

        // 插件未激活：直接返回默认值
        if (!isset($options->plugins['activated'][self::DIR])) {
            return $default;
        }

        try {
            $config = $options->plugin(self::DIR);
        } catch (\Throwable $e) {
            // 插件已激活但从未保存过配置时，Widget\Options::plugin() 会抛出
            // 「插件配置信息没有找到」。这是正常状态（激活后未进设置页），
            // 不能让它中断钩子链路，否则菜单接管会静默失效。
            return $default;
        }

        if (!is_object($config) || !isset($config->$name)) {
            return $default;
        }

        $value = $config->$name;

        return ($value === null || $value === '') ? $default : $value;
    }

    // ------------------------------------------------------------------
    // 钩子实现：登录页与后台样式
    // ------------------------------------------------------------------

    /**
     * 构造 <head> 追加片段
     *
     * 同时兼容两种钩子语义：
     *   - 1.3.0 的 filter 钩子：回调收到 ($value, ...$args, $value)，首参即原值；
     *   - 1.2.x 的 call 钩子：回调不接收原值，这里视为空串。
     *
     * @param string $header 原始 <head> 内容
     * @return string
     */
    public static function headHtml($header = '')
    {
        if (!self::isAdminRequest()) {
            return $header;
        }

        $url = self::assetUrl();
        $version = self::assetVersion();

        if (!self::isLoggedIn()) {
            return $header . self::loginHeader($url, $version);
        }

        if (self::configValue('disableskin', '0') === '1') {
            return $header;
        }

        return $header . self::adminHeader($url, $version);
    }

    /**
     * admin/header.php 钩子实现
     *
     * @return string
     */
    public static function renderHeader()
    {
        $args = func_get_args();
        $header = isset($args[0]) && is_string($args[0]) ? $args[0] : '';

        return self::headHtml($header);
    }

    /**
     * 构造页面末尾追加片段
     *
     * @return string
     */
    public static function footHtml()
    {
        if (!self::isAdminRequest()) {
            return '';
        }

        $url = self::assetUrl();
        $version = self::assetVersion();

        if (!self::isLoggedIn()) {
            return self::loginFooter($url, $version);
        }

        if (self::configValue('disableskin', '0') === '1') {
            return '';
        }

        return self::adminFooter($url, $version);
    }

    /**
     * admin/footer.php 的 end 钩子
     *
     * @return void
     */
    public static function renderFooter()
    {
        echo self::footHtml();
    }

    /**
     * 登录页 head
     *
     * @param string $url
     * @param string $version
     * @return string
     */
    protected static function loginHeader($url, $version)
    {
        $skin = (string) self::configValue('bgfengge', 'Green');
        $css = '';

        if ($skin === 'kongbai' || $skin === 'Default') {
            $diy = (string) self::configValue('diycss', '');
            if ($diy !== '') {
                $css = '<style>' . self::sanitizeCss($diy) . '</style>';
            }
        }

        $skinFile = self::pluginPath() . '/assets/skin/' . $skin . '.css';
        if ($css === '' && file_exists($skinFile)) {
            $css = '<link rel="stylesheet" href="' . htmlspecialchars($url . 'assets/skin/' . $skin . '.css?v=' . $version, ENT_QUOTES, 'UTF-8') . '">';

            $bgUrl = self::sanitizeUrl((string) self::configValue('bgUrl', ''));
            if ($bgUrl !== '') {
                $css .= '<style>body{background-image:url(' . $bgUrl . ');}</style>';
            }
        }

        return $css;
    }

    /**
     * 登录页 footer
     *
     * @param string $url
     * @param string $version
     * @return string
     */
    protected static function loginFooter($url, $version)
    {
        $skin = (string) self::configValue('bgfengge', 'Green');

        if ($skin === 'heike') {
            return '<canvas id="canvas"></canvas><script src="'
                . htmlspecialchars($url . 'assets/js/matrix-rain.js?v=' . $version, ENT_QUOTES, 'UTF-8') . '"></script>';
        }

        if ($skin === 'lv') {
            return '<ul class="bg-bubbles"><li></li><li></li><li></li><li></li><li></li>'
                . '<li></li><li></li><li></li><li></li><li></li></ul>';
        }

        return '';
    }

    /**
     * 后台 head：本地样式优先，避免 jsdelivr 上 Version1.7 的历史资源失效
     *
     * @param string $url
     * @param string $version
     * @return string
     */
    protected static function adminHeader($url, $version)
    {
        $html = '';

        $files = [
            'assets/css/admin.css' => 'css',
            'assets/css/cursor.css' => 'css',
            'assets/js/cursor.js' => 'js',
        ];

        foreach ($files as $file => $kind) {
            $path = self::pluginPath() . '/' . $file;
            if (!file_exists($path)) {
                continue;
            }

            $href = htmlspecialchars($url . $file . '?v=' . $version, ENT_QUOTES, 'UTF-8');
            $html .= $kind === 'css'
                ? '<link rel="stylesheet" href="' . $href . '">'
                : '<script src="' . $href . '"></script>\n';
        }

        $custom = (string) self::configValue('admincss', '');
        if ($custom !== '') {
            $html .= '<style>' . self::sanitizeCss($custom) . '</style>';
        }

        return $html;
    }

    /**
     * 后台 footer
     *
     * @param string $url
     * @param string $version
     * @return string
     */
    protected static function adminFooter($url, $version)
    {
        $path = self::pluginPath() . '/assets/js/admin.js';
        if (!file_exists($path)) {
            return '';
        }

        return '<script src="' . htmlspecialchars($url . 'assets/js/admin.js?v=' . $version, ENT_QUOTES, 'UTF-8') . '"></script>';
    }

    // ------------------------------------------------------------------
    // 登录页表单兼容处理
    // ------------------------------------------------------------------

    /**
     * 修正 1.3.0 登录页「下次自动登录」复选框缺失 name 的问题
     *
     * Typecho 1.3.0 的 admin/login.php 中该复选框渲染为 <input ... value="1" id="remember" />，
     * 缺少 name="remember"，导致勾选后不会被提交，功能实际失效。这里在登录页注入极小的
     * 内联脚本补齐 name 属性，不影响其他任何逻辑。
     *
     * @param string $header
     * @return string
     */
    protected static function patchRememberCheckbox($header)
    {
        $script = '<script>(function(){var b=document.getElementById("remember");'
            . 'if(b&&!b.getAttribute("name")){b.setAttribute("name","remember");}})();</script>';

        return $header . $script;
    }
}
