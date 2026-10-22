<?php
/**
 * AdminMD 后台菜单（Typecho 1.3.0 精确复刻）
 *
 * 设计约束：本类必须「自包含」——不能继承 Widget\Base。原因是接管方式为在
 * admin/common.php 的 begin 钩子中执行 class_alias('\AdminMD\Widget\Menu', '\Widget\Menu')；
 * 若本类继承 Widget\Base，则触发父类加载时会连带把内核 var/Widget/Menu.php 一并
 * 载入并声明 \Widget\Menu，导致 class_alias 直接失败。
 *
 * 因此这里复刻 Typecho 1.3.0 var/Widget/Menu.php 的全部成员，仅将 output() 的渲染
 * 结果替换为 AdminMD 的侧边抽屉结构，其余逻辑（菜单节点、权限判定、URL 匹配、
 * 扩展菜单合并、$menu->title / $menu->addLink 语义）与内核保持一致。
 *
 * @package AdminMD
 */

namespace AdminMD\Widget;

use Typecho\Common;
use Typecho\Widget;
use Widget\Plugins\Config;
use Widget\Themes\Files;
use Widget\Users\Edit as UsersEdit;
use Widget\Contents\Attachment\Edit as AttachmentEdit;
use Widget\Contents\Post\Edit as PostEdit;
use Widget\Contents\Page\Edit as PageEdit;
use Widget\Contents\Post\Admin as PostAdmin;
use Widget\Contents\Page\Admin as PageAdmin;
use Widget\Comments\Admin as CommentsAdmin;
use Widget\Metas\Category\Admin as CategoryAdmin;
use Widget\Metas\Category\Edit as CategoryEdit;
use Widget\Metas\Tag\Admin as TagAdmin;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * 后台菜单显示
 */
class Menu extends Widget
{
    /**
     * 当前菜单标题
     *
     * @var string
     */
    public string $title = '';

    /**
     * 当前增加项目链接
     *
     * @var string|null
     */
    public ?string $addLink = null;

    /**
     * 父菜单列表
     *
     * @var array
     */
    private array $menu = [];

    /**
     * 当前父菜单
     *
     * @var integer
     */
    private int $currentParent = 1;

    /**
     * 当前子菜单
     *
     * @var integer
     */
    private int $currentChild = 0;

    /**
     * 当前页面
     *
     * @var string
     */
    private string $currentUrl = '';

    /**
     * 当前菜单URL
     *
     * @var string
     */
    private string $currentMenuUrl = '';

    /**
     * 全局选项
     *
     * @var \Widget\Options
     */
    protected $options;

    /**
     * 用户对象
     *
     * @var \Widget\User
     */
    protected $user;

    /**
     * 构造函数,初始化组件
     *
     * 与 Widget\Base 的构造行为保持一致：创建 Request / Response 并调用 execute()。
     *
     * @param mixed $request
     * @param mixed $response
     * @param mixed $params
     * @return void
     */
    public function __construct($request, $response, $params = null)
    {
        parent::__construct($request, $response, $params);

        /** 初始化常用组件 */
        $this->options = self::widget('Widget_Options');
        $this->user = self::widget('Widget_User');

        $this->execute();
    }

    /**
     * 执行函数,初始化菜单
     *
     * @return void
     */
    public function execute()
    {
        $parentNodes = [null, _t('控制台'), _t('撰写'), _t('管理'), _t('设置')];

        $childNodes = [
            [
                [_t('登录'), _t('登录到%s', $this->options->title), 'login.php', 'visitor'],
                [_t('注册'), _t('注册到%s', $this->options->title), 'register.php', 'visitor']
            ],
            [
                [_t('概要'), _t('网站概要'), 'index.php', 'subscriber'],
                [_t('个人设置'), _t('个人设置'), 'profile.php', 'subscriber'],
                [_t('插件'), _t('插件管理'), 'plugins.php', 'administrator'],
                [[Config::class, 'getMenuTitle'], [Config::class, 'getMenuTitle'], 'options-plugin.php?config=', 'administrator', true],
                [_t('外观'), _t('网站外观'), 'themes.php', 'administrator'],
                [[Files::class, 'getMenuTitle'], [Files::class, 'getMenuTitle'], 'theme-editor.php', 'administrator', true],
                [_t('设置外观'), _t('设置外观'), 'options-theme.php', 'administrator', true],
                [_t('备份'), _t('备份'), 'backup.php', 'administrator'],
                [_t('升级'), _t('升级程序'), 'upgrade.php', 'administrator', true],
                [_t('欢迎'), _t('欢迎使用'), 'welcome.php', 'subscriber', true]
            ],
            [
                [_t('撰写文章'), _t('撰写新文章'), 'write-post.php', 'contributor'],
                [[PostEdit::class, 'getMenuTitle'], [PostEdit::class, 'getMenuTitle'], 'write-post.php?cid=', 'contributor', true],
                [_t('创建页面'), _t('创建新页面'), 'write-page.php', 'editor'],
                [[PageEdit::class, 'getMenuTitle'], [PageEdit::class, 'getMenuTitle'], 'write-page.php?cid=', 'editor', true],
                [[PageEdit::class, 'getMenuTitle'], [PageEdit::class, 'getMenuTitle'], 'write-page.php?parent=', 'editor', true],
            ],
            [
                [_t('文章'), _t('管理文章'), 'manage-posts.php', 'contributor', false, 'write-post.php'],
                [[PostAdmin::class, 'getMenuTitle'], [PostAdmin::class, 'getMenuTitle'], 'manage-posts.php?uid=', 'contributor', true],
                [_t('独立页面'), _t('管理独立页面'), 'manage-pages.php', 'editor', false, 'write-page.php'],
                [[PageAdmin::class, 'getMenuTitle'], [PageAdmin::class, 'getMenuTitle'], 'manage-pages.php?parent=', 'editor', true, [PageAdmin::class, 'getAddLink']],
                [_t('评论'), _t('管理评论'), 'manage-comments.php', 'contributor'],
                [[CommentsAdmin::class, 'getMenuTitle'], [CommentsAdmin::class, 'getMenuTitle'], 'manage-comments.php?cid=', 'contributor', true],
                [_t('分类'), _t('管理分类'), 'manage-categories.php', 'editor', false, 'category.php'],
                [_t('新增分类'), _t('新增分类'), 'category.php', 'editor', true],
                [[CategoryAdmin::class, 'getMenuTitle'], [CategoryAdmin::class, 'getMenuTitle'], 'manage-categories.php?parent=', 'editor', true, [CategoryAdmin::class, 'getAddLink']],
                [[CategoryEdit::class, 'getMenuTitle'], [CategoryEdit::class, 'getMenuTitle'], 'category.php?mid=', 'editor', true],
                [[CategoryEdit::class, 'getMenuTitle'], [CategoryEdit::class, 'getMenuTitle'], 'category.php?parent=', 'editor', true],
                [_t('标签'), _t('管理标签'), 'manage-tags.php', 'editor'],
                [[TagAdmin::class, 'getMenuTitle'], [TagAdmin::class, 'getMenuTitle'], 'manage-tags.php?mid=', 'editor', true],
                [_t('文件'), _t('管理文件'), 'manage-medias.php', 'editor'],
                [[AttachmentEdit::class, 'getMenuTitle'], [AttachmentEdit::class, 'getMenuTitle'], 'media.php?cid=', 'contributor', true],
                [_t('用户'), _t('管理用户'), 'manage-users.php', 'administrator', false, 'user.php'],
                [_t('新增用户'), _t('新增用户'), 'user.php', 'administrator', true],
                [[UsersEdit::class, 'getMenuTitle'], [UsersEdit::class, 'getMenuTitle'], 'user.php?uid=', 'administrator', true],
            ],
            [
                [_t('基本'), _t('基本设置'), 'options-general.php', 'administrator'],
                [_t('评论'), _t('评论设置'), 'options-discussion.php', 'administrator'],
                [_t('阅读'), _t('阅读设置'), 'options-reading.php', 'administrator'],
                [_t('永久链接'), _t('永久链接设置'), 'options-permalink.php', 'administrator'],
            ]
        ];

        /** 获取扩展菜单 */
        $panelTable = $this->options->panelTable;
        $extendingParentMenu = empty($panelTable['parent']) ? [] : $panelTable['parent'];
        $extendingChildMenu = empty($panelTable['child']) ? [] : $panelTable['child'];
        $currentUrl = $this->request->getRequestUrl();
        $adminUrl = $this->options->adminUrl;
        $menu = [];
        $defaultChildNode = [null, null, null, 'administrator', false, null];

        $currentUrlParts = parse_url($currentUrl);
        $currentUrlParams = [];
        if (!empty($currentUrlParts['query'])) {
            parse_str($currentUrlParts['query'], $currentUrlParams);
        }

        if ('/' == $currentUrlParts['path'][strlen($currentUrlParts['path']) - 1]) {
            $currentUrlParts['path'] .= 'index.php';
        }

        foreach ($extendingParentMenu as $key => $val) {
            $parentNodes[10 + $key] = $val;
        }

        foreach ($extendingChildMenu as $key => $val) {
            $childNodes[$key] = array_merge($childNodes[$key] ?? [], $val);
        }

        foreach ($parentNodes as $key => $parentNode) {
            $children = [];
            $showedChildrenCount = 0;
            $firstUrl = null;

            foreach ($childNodes[$key] as $inKey => $childNode) {
                // magic merge
                $childNode += $defaultChildNode;
                [$name, $title, $url, $access] = $childNode;

                $hidden = $childNode[4] ?? false;
                $addLink = $childNode[5] ?? null;

                // 保存最原始的hidden信息
                $orgHidden = $hidden;

                // parse url
                $menuUrl = $url;
                $url = Common::url($url, $adminUrl);

                // compare url
                $urlParts = parse_url($url);
                $urlParams = [];
                if (!empty($urlParts['query'])) {
                    parse_str($urlParts['query'], $urlParams);
                }

                $validate = true;
                if (($urlParts['path'] ?? '') != ($currentUrlParts['path'] ?? '')) {
                    $validate = false;
                } else {
                    foreach ($urlParams as $paramName => $paramValue) {
                        if (!isset($currentUrlParams[$paramName])) {
                            $validate = false;
                            break;
                        }
                    }
                }

                if (
                    $validate
                    && basename($urlParts['path']) == 'extending.php'
                    && !empty($currentUrlParams['panel']) && !empty($urlParams['panel'])
                    && $urlParams['panel'] != $currentUrlParams['panel']
                ) {
                    $validate = false;
                }

                if ($hidden && $validate) {
                    $hidden = false;
                }

                if (!$hidden && !$this->user->pass($access, true)) {
                    $hidden = true;
                }

                if (!$hidden) {
                    $showedChildrenCount++;

                    if (empty($firstUrl)) {
                        $firstUrl = $url;
                    }

                    if (is_array($name)) {
                        [$widget, $method] = $name;
                        $name = self::widget($widget)->$method();
                    }

                    if (is_array($title)) {
                        [$widget, $method] = $title;
                        $title = self::widget($widget)->$method();
                    }

                    if (is_array($addLink)) {
                        [$widget, $method] = $addLink;
                        $addLink = self::widget($widget)->$method();
                    }
                }

                if ($validate) {
                    if ('visitor' != $access) {
                        $this->user->pass($access);
                    }

                    $this->currentParent = $key;
                    $this->currentChild = $inKey;
                    $this->title = $title;
                    $this->addLink = $addLink ? Common::url($addLink, $adminUrl) : null;
                    $this->currentMenuUrl = $menuUrl;
                }

                $children[$inKey] = [
                    $name,
                    $title,
                    $url,
                    $access,
                    $hidden,
                    $addLink,
                    $orgHidden
                ];
            }

            $menu[$key] = [$parentNode, $showedChildrenCount > 0, $firstUrl, $children];
        }

        $this->menu = $menu;
        $this->currentUrl = Common::safeUrl($currentUrl);
    }

    /**
     * 获取当前菜单
     *
     * @return array|null
     */
    public function getCurrentMenu(): ?array
    {
        return $this->currentParent > 0 ? $this->menu[$this->currentParent][3][$this->currentChild] : null;
    }

    /**
     * 获取当前菜单URL
     *
     * @return string
     */
    public function getCurrentMenuUrl(): string
    {
        return $this->currentMenuUrl;
    }

    /**
     * 当前父菜单索引
     *
     * @return int
     */
    public function getCurrentParent(): int
    {
        return $this->currentParent;
    }

    /**
     * 输出菜单
     *
     * 签名与内核 output($class = 'focus', $childClass = 'focus') 完全一致，
     * 但渲染为 AdminMD 的折叠式列表，供后台抽屉使用。
     *
     * @param string $class
     * @param string $childClass
     * @return void
     */
    public function output($class = 'focus', $childClass = 'focus')
    {
        foreach ($this->menu as $key => $node) {
            if (!$node[1] || !$key) {
                continue;
            }

            $isActiveParent = ($key == $this->currentParent);

            echo '<div class="mdui-collapse-item' . ($isActiveParent ? ' mdui-collapse-item-open' : '') . '">';
            echo '<div class="mdui-collapse-item-header mdui-list-item mdui-ripple">';
            echo '<i class="mdui-list-item-icon mdui-icon adminmd-icon">' . $this->icon($key) . '</i>';
            echo '<div class="mdui-list-item-content">' . $node[0] . '</div>';
            echo '<i class="mdui-collapse-item-arrow mdui-icon adminmd-chevron"></i>';
            echo '</div>';
            echo '<div class="mdui-collapse-item-body mdui-list">';

            foreach ($node[3] as $inKey => $inNode) {
                if ($inNode[4]) {
                    continue;
                }

                $focus = false;
                if ($key == $this->currentParent && $inKey == $this->currentChild) {
                    $focus = true;
                } elseif ($inNode[6]) {
                    continue;
                }

                $href = $focus ? $this->currentUrl : $inNode[2];
                $anchorClass = 'mdui-list-item mdui-ripple' . ($focus ? ' mdui-list-item-active' : '');

                echo '<a href="' . Common::safeUrl($href) . '" class="' . $anchorClass . '">';
                echo '<div class="mdui-list-item-content">' . $inNode[0] . '</div>';
                echo '</a>';
            }

            echo '</div></div>';
        }
    }

    /**
     * 父菜单图标（内联 SVG，避免依赖图标字体 CDN）
     *
     * @param int $key
     * @return string
     */
    private function icon($key)
    {
        $icons = [
            1 => '<path d="M4 11h6V3H4v8zm0 10h6v-8H4v8zm10 0h6v-8h-6v8zm0-18v8h6V3h-6z"/>',
            2 => '<path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1 1 0 000-1.41l-2.34-2.34a1 1 0 00-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>',
            3 => '<path d="M19 3H5a2 2 0 00-2 2v14l4-4h12a2 2 0 002-2V5a2 2 0 00-2-2z"/>',
            4 => '<path d="M19.14 12.94a7.07 7.07 0 000-1.88l2.03-1.58a.5.5 0 00.12-.64l-1.92-3.32a.5.5 0 00-.6-.22l-2.39.96a7.3 7.3 0 00-1.62-.94l-.36-2.54a.5.5 0 00-.5-.42h-3.84a.5.5 0 00-.5.42l-.36 2.54c-.58.24-1.12.55-1.62.94l-2.39-.96a.5.5 0 00-.6.22L2.71 8.84a.5.5 0 00.12.64l2.03 1.58a7.07 7.07 0 000 1.88l-2.03 1.58a.5.5 0 00-.12.64l1.92 3.32c.14.24.42.34.6.22l2.39-.96c.5.39 1.04.7 1.62.94l.36 2.54c.05.24.25.42.5.42h3.84c.25 0 .45-.18.5-.42l.36-2.54c.58-.24 1.12-.55 1.62-.94l2.39.96c.18.12.46.02.6-.22l1.92-3.32a.5.5 0 00-.12-.64l-2.03-1.58zM12 15.6A3.6 3.6 0 1112 8.4a3.6 3.6 0 010 7.2z"/>',
        ];

        $path = $icons[$key] ?? '<path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>';

        return '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="currentColor">'
            . $path . '</svg>';
    }
}
