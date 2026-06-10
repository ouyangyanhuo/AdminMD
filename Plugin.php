<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * AdminMD是一款基于Material Design设计的typecho后台美化插件
 *
 * @package AdminMD
 * @author Magneto
 * @version 2.0.0
 * @link https://www.fmcf.cc
 */

class AdminMD_Plugin implements Typecho_Plugin_Interface
{
    public static $file_map = [];

    /**
     * @return array
     */
    public static function getFileMap()
    {
        return self::$file_map;
    }

    /**
     * @param array $file_map
     */
    public static function setFileMap($file_map)
    {
        self::$file_map = $file_map;
    }

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        $plugin_path = dirname(__FILE__);

        Typecho_Plugin::factory('admin/header.php')->header_2000 = array('AdminMD_Plugin', 'renderHeader');
        Typecho_Plugin::factory('admin/footer.php')->end_2000 = array('AdminMD_Plugin', 'renderFooter');
        if(file_exists("var/Widget/Menu.php")){
            //挂载menu.php
            rename("var/Widget/Menu.php", "var/Widget/Menu.php.bak");
            copy("usr/plugins/AdminMD/var/Widget/Menu.php", "var/Widget/Menu.php");
        }


        $admin_files = self::readdir($plugin_path.'/admin/');
        self::setFileMap($admin_files);

        foreach ($admin_files as $tmp_file){
            $tmp_file_path = "usr/plugins/AdminMD/admin/".$tmp_file;
            $target_file_path = "admin/".$tmp_file;
            $target_file_bak = "admin/".$tmp_file.".bak";

            if(file_exists($target_file_path)){
                //挂载
                rename($target_file_path, $target_file_bak);
                copy($tmp_file_path, $target_file_path);
            }
        }

    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {
        //还原Menu.php
        if(file_exists("var/Widget/Menu.php.bak")){
            unlink("var/Widget/Menu.php");
            rename("var/Widget/Menu.php.bak", "var/Widget/Menu.php");
        }
        $plugin_path = dirname(__FILE__);

        $admin_files = self::readdir($plugin_path.'/admin/');

        foreach ($admin_files as $tmp_file){
            $target_file_path = "admin/".$tmp_file;
            $target_file_bak = "admin/".$tmp_file.".bak";

            if(file_exists($target_file_bak)){
                unlink($target_file_path);
                rename($target_file_bak, $target_file_path);
            }
        }
    }

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {

        ?>
        <link rel="stylesheet" href="<?php Helper::options()->pluginUrl(); ?>/AdminMD/assets/login.css">
        <div id="AdminMD-version" style="display:none; padding: 10px; margin-bottom: 15px; background: #f8f9fa; border-radius: 4px; border-left: 4px solid #4285f4;"></div>
        <script src="<?php Helper::options()->pluginUrl(); ?>/AdminMD/assets/js/version-check.js"></script>
        <?php
        
        $TheNotice = new Typecho_Widget_Helper_Form_Element_Text('TheNotice', NULL, NULL, _t('<h2>基础外观</h2>'));
        $TheNotice->input->setAttribute('style', 'display:none');
        $form->addInput($TheNotice);
        
        $url = Helper::options()->pluginUrl . '/AdminMD/';
        $zz1 = '<div class="zz">绿色[你的名字]</div>';
        $zz2 = '<div class="zz">蓝天和远山[网络]</div>';
        $zz3 = '<div class="zz">后花园[Pixiv 95741043]</div>';
        $zz4 = '<div class="zz">枫[Pixiv 66986409]</div>';
        $zz5 = '<div class="zz">Default</div>';
        $bgfengge = new Typecho_Widget_Helper_Form_Element_Radio(
            'bgfengge', array(
            'Green' => _t('<div class="kuai"><img src="https://p5.qhimg.com/bdm/960_593_0/t01573b4f467fdf51e2.jpg" loading="lazy">' . $zz1 . '</div>'),
            'BlueSkyAndMountains' => _t('<div class="kuai"><img src="https://cdn.jsdelivr.net/gh/fyhgay/CDNS@latest/2021/07/15/0531f7895a5627b8737e0690d7dcb4e5.png" loading="lazy">' . $zz2 . '</div>'),
            'Back_garden' => _t('<div class="kuai"><img src="https://tva4.sinaimg.cn/large/008aATBzly8gze1cpfkuuj31i00u0k56.jpg" loading="lazy">' . $zz3 . '</div>'),
            'Maple' => _t('<div class="kuai"><img src="https://tva4.sinaimg.cn/large/008aATBzly1h07hmpmbj5j30yq0g6dvm.jpg" loading="lazy">' . $zz4 . '</div>'),
            'Default' => _t('<div class="kuai"><img src="https://fastly.jsdelivr.net/gh/fyhgay/CDNS@latest/2021/07/15/05b54e433729eb89a067ff992176c442.png" loading="lazy">' . $zz5 . '</div>'),
        ), 'Green', _t('登陆/注册页面样式'), _t(''));
        $bgfengge->setAttribute('id', 'yangshi');
        $form->addInput($bgfengge);

        $bgUrl = new Typecho_Widget_Helper_Form_Element_Text('bgUrl', NULL, NULL, _t('自定义背景图'), _t('选中上方的基础样式后，可以在这里填写图片地址自定义背景图'));
        $form->addInput($bgUrl);

        $diycss = new Typecho_Widget_Helper_Form_Element_Textarea('diycss', NULL, NULL, '自定义样式', _t('上边的样式选择【Default】，然后在这里自己写css美化注册/登录页面；<b>注意</b>：该功能与【自定义背景图】功能冲突，使用该功能时如果想设置背景图请写css里面。'));
        $form->addInput($diycss);

    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    /**
     * 过滤 CSS 内容，防止 XSS 攻击
     *
     * @access public
     * @param string $css CSS 内容
     * @return string 过滤后的 CSS
     */
    public static function sanitizeCss($css)
    {
        // 移除 HTML 标签
        $css = strip_tags($css);
        // 移除 expression() 和 url() 中的 javascript 协议
        $css = preg_replace('/expression\s*\(/i', '(', $css);
        $css = preg_replace('/url\s*\(\s*["\']?\s*javascript:/i', 'url(', $css);
        // 移除 @import 中的 javascript 协议
        $css = preg_replace('/@import\s+url\s*\(\s*["\']?\s*javascript:/i', '@import url(', $css);
        return $css;
    }

    /**
     * 过滤和验证 URL
     *
     * @access public
     * @param string $url URL 地址
     * @return string 过滤后的 URL
     */
    public static function sanitizeUrl($url)
    {
        // 移除 HTML 标签
        $url = strip_tags($url);
        // 只允许 http:// 和 https:// 协议
        if (!preg_match('/^https?:\/\//i', $url)) {
            $url = 'https://' . $url;
        }
        // 编码特殊字符
        $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        return $url;
    }

    /**
     *
     * 函数名:readdir($dir)
     * 作用:读取目录所有的文件名
     * 参数:$dir 目录地址
     * 返回值:文件名数组
     *
     * */
    public static function readdir($dir) {
        $list = [];
        $handle = opendir($dir);
        if ($handle === false) {
            return $list;
        }
        while (($file = readdir($handle)) !== false) {
            if ($file !== '.' && $file !== '..') {
                $list[] = $file;
            }
        }
        closedir($handle);
        return $list;
    }

    /**
     * 插件实现方法
     *
     * @access public
     * @return string
     */
    public static function renderHeader($hed)
    {
        $options = Helper::options();

        $url = $options->pluginUrl . '/AdminMD/';
        list($prefixVersion, $suffixVersion) = explode('/', $options->version);

        if (!Typecho_Widget::widget('Widget_User')->hasLogin()) {
            $skin = Typecho_Widget::widget('Widget_Options')->plugin('AdminMD')->bgfengge;
            $diycss = Typecho_Widget::widget('Widget_Options')->plugin('AdminMD')->diycss;
            if ($skin == 'kongbai') {
                // 过滤 CSS 内容，移除潜在的 XSS 攻击代码
                $diycss = self::sanitizeCss($diycss);
                $hed = $hed . '<style>' . $diycss . '</style>';
            } else {
                if ($skin == 'heike') {
                    $hed = $hed . '<link rel="stylesheet" href="' . $url . 'assets/skin/' . $skin . '.css?' . $suffixVersion.'">';
                } else {
                    $bgUrl = Typecho_Widget::widget('Widget_Options')->plugin('AdminMD')->bgUrl;
                    $zidingyi = "";
                    if ($bgUrl) {
                        // 验证和过滤 URL
                        $bgUrl = self::sanitizeUrl($bgUrl);
                        $zidingyi = "<style>body{background-image: url(" . $bgUrl . ");}</style>";
                    }
                    $hed = $hed . '<link rel="stylesheet" href="' . $url . 'assets/skin/' . $skin . '.css?' . $suffixVersion.'">' . $zidingyi;
                }
            }

            echo $hed;
        }else{
            /* 添加 Material Design style */
            $hed = $hed . '<link rel="stylesheet" href="https://fastly.jsdelivr.net/gh/ouyangyanhuo/AdminMD@Version1.7/assets/css/style.min.css">';
            $hed = $hed.'<link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/MaterialDesign-Webfont/6.6.96/css/materialdesignicons.min.css">';
            $hed = $hed.'<link rel="stylesheet" href="https://fastly.jsdelivr.net/gh/ouyangyanhuo/AdminMD@Version1.7/assets/vendors/css/vendor.bundle.base.css?">';
            $hed = $hed.'<script src="https://fastly.jsdelivr.net/gh/ouyangyanhuo/AdminMD@Version1.7/assets/vendors/js/vendor.bundle.base.js"></script>';
            $hed = $hed.'<script src="https://fastly.jsdelivr.net/gh/ouyangyanhuo/AdminMD@Version1.7/assets/js/off-canvas.js"></script>';
            $hed = $hed.'<script src="https://fastly.jsdelivr.net/gh/ouyangyanhuo/AdminMD@Version1.7/assets/js/hoverable-collapse.js"></script>';
        }

        return $hed;
    }

    public static function renderFooter()
    {
        $options = Helper::options();

        $url = $options->pluginUrl . '/AdminMD/';
        list($prefixVersion, $suffixVersion) = explode('/', $options->version);
        if (!Typecho_Widget::widget('Widget_User')->hasLogin()) {
            $url = Helper::options()->pluginUrl . '/AdminMD/';
            $skin = Typecho_Widget::widget('Widget_Options')->plugin('AdminMD')->bgfengge;
            $ft = '';
            if ($skin == 'heike') {
                $ft = '<canvas id="canvas"></canvas><script src="' . $url . 'assets/js/matrix-rain.js?' . $suffixVersion . '"></script>';
            }
            if ($skin == 'lv') {
                $ft = '<ul class="bg-bubbles"><li></li><li></li><li></li><li></li><li></li><li></li><li></li><li></li><li></li><li></li></ul>';
            }
            echo $ft;
        }else{
            echo '<script src="https://fastly.jsdelivr.net/gh/ouyangyanhuo/AdminMD@Version1.7/assets/js/misc.js"></script>';
        }

    }


}
