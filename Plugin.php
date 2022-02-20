<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * AdminMD是一款基于Material Design设计的typecho后台美化插件
 * 
 * @package AdminMD
 * @author Magneto
 * @version 1.7
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
        <link rel="stylesheet" href="<?php Helper::options()->pluginUrl(); ?>/AdminMD/assets/css/login.css">
        <?php

        $url = Helper::options()->pluginUrl . '/AdminMD/';
        $zz1 = '<div class="zz">绿色[你的名字]</div>';
        $zz2 = '<div class="zz">蓝天和远山[网络]</div>';
        $zz3 = '<div class="zz">后花园[Pixiv 95741043]</div>';
        $zz4 = '<div class="zz">Default</div>';
        $bgfengge = new Typecho_Widget_Helper_Form_Element_Radio(
            'bgfengge', array(
            'Green' => _t('<div class="kuai"><img src="http://p5.qhimg.com/bdm/960_593_0/t01573b4f467fdf51e2.jpg" loading="lazy">' . $zz1 . '</div>'),
            'BlueSkyAndMountains' => _t('<div class="kuai"><img src="https://cdn.jsdelivr.net/gh/fyhgay/CDNS@latest/2021/07/15/0531f7895a5627b8737e0690d7dcb4e5.png" loading="lazy">' . $zz2 . '</div>'),
            'Back_garden' => _t('<div class="kuai"><img src="https://tva4.sinaimg.cn/large/008aATBzly8gze1cpfkuuj31i00u0k56.jpg" loading="lazy">' . $zz3 . '</div>'),
            'Default' => _t('<div class="kuai"><img src="https://cdn.jsdelivr.net/gh/fyhgay/CDNS@latest/2021/07/15/05b54e433729eb89a067ff992176c442.png" loading="lazy">' . $zz4 . '</div>'),
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
     *
     * 函数名:readdir($dir)
     * 作用:读取目录所有的文件名
     * 参数:$dir 目录地址
     * 返回值:文件名数组
     *
     * */
    public static function readdir($dir) {
        $handle=opendir($dir);
        $i=0;
        while(!!$file = readdir($handle)) {
            if (($file!=".")and($file!="..")) {
                $list[$i]=$file;
                $i=$i+1;
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
                $hed = $hed . '<style>' . $diycss . '</style>';
            } else {
                if ($skin == 'heike') {
                    $hed = $hed . '<link rel="stylesheet" href="' . $url . 'assets/skin/' . $skin . '.css?20220220">';
                } else {
                    $bgUrl = Typecho_Widget::widget('Widget_Options')->plugin('AdminMD')->bgUrl;
                    $zidingyi = "";
                    if ($bgUrl) {
                        $zidingyi = "<style>body{background-image: url(" . $bgUrl . ");}</style>";
                    }
                    $hed = $hed . '<link rel="stylesheet" href="' . $url . 'assets/skin/' . $skin . '.css?20220220">' . $zidingyi;
                }
            }

            echo $hed;
        }else{
            /* 添加 Material Design style */
            $hed = $hed . '<link rel="stylesheet" href="' . $url . 'assets/css/style.min.css?v=' . $suffixVersion.'">';
            $hed = $hed.'<link rel="stylesheet" href="' . $url . 'assets/vendors/mdi/css/materialdesignicons.min.css?v=' . $suffixVersion.'">';
            $hed = $hed.'<link rel="stylesheet" href="' . $url . 'assets/vendors/css/vendor.bundle.base.css?v=' . $suffixVersion.'">';
            $hed = $hed.'<script src="' . $url . 'assets/vendors/js/vendor.bundle.base.js?v=' . $suffixVersion.'"></script>';
            $hed = $hed.'<script src="' . $url . 'assets/js/off-canvas.js?v=' . $suffixVersion.'"></script>';
            $hed = $hed.'<script src="' . $url . 'assets/js/hoverable-collapse.js?v=' . $suffixVersion.'"></script>';
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
                $ft = '<canvas id="canvas"></canvas><script type="text/javascript">window.onload=function(){var canvas=document.getElementById("canvas");var context=canvas.getContext("2d");var W=window.innerWidth;var H=window.innerHeight;canvas.width=W;canvas.height=H;var fontSize=16;var colunms=Math.floor(W/fontSize);var drops=[];for(var i=0;i<colunms;i++){drops.push(0)}var str="111001101000100010010001111001111000100010110001111001001011110110100000";function draw(){context.fillStyle="rgba(0,0,0,0.05)";context.fillRect(0,0,W,H);context.font="700 "+fontSize+"px  微软雅黑";context.fillStyle="#00cc33";for(var i=0;i<colunms;i++){var index=Math.floor(Math.random()*str.length);var x=i*fontSize;var y=drops[i]*fontSize;context.fillText(str[index],x,y);if(y>=canvas.height&&Math.random()>0.99){drops[i]=0}drops[i]++}}function randColor(){var r=Math.floor(Math.random()*256);var g=Math.floor(Math.random()*256);var b=Math.floor(Math.random()*256);return"rgb("+r+","+g+","+b+")"}draw();setInterval(draw,30)};</script>';
            }
            if ($skin == 'lv') {
                $ft = '<ul class="bg-bubbles"><li></li><li></li><li></li><li></li><li></li><li></li><li></li><li></li><li></li><li></li></ul>';
            }
            echo $ft;
        }else{
            echo '<script src="' . $url . 'assets/js/misc.js?v=' . $suffixVersion.'"></script>';
        }

    }


}
