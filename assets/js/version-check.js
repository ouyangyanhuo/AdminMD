/**
 * AdminMD 版本检测
 */
(function() {
    var currentVersion = '2.0.0';
    var container = document.getElementById('AdminMD-version');

    if (!container) {
        return;
    }

    container.style.display = 'block';
    container.innerHTML = '版本检测中...';

    var ajax = new XMLHttpRequest();
    ajax.open('GET', 'https://api.github.com/repos/ouyangyanhuo/AdminMD/releases/latest', true);
    ajax.timeout = 5000;

    ajax.onreadystatechange = function() {
        if (ajax.readyState === 4) {
            if (ajax.status === 200) {
                try {
                    var obj = JSON.parse(ajax.responseText);
                    var newest = obj.tag_name;

                    if (newest > currentVersion) {
                        container.innerHTML = '发现新版本：' + escapeHtml(obj.name) +
                            '。下载地址：<a href="' + escapeHtml(obj.zipball_url) + '">点击下载</a>' +
                            '<br>您目前的版本：' + escapeHtml(currentVersion) +
                            '。<a target="_blank" href="' + escapeHtml(obj.html_url) + '">👉查看新版亮点</a>';
                    } else {
                        container.innerHTML = '您目前的版本：' + escapeHtml(currentVersion) + '。您目前使用的是最新版。';
                    }
                } catch (e) {
                    container.innerHTML = '版本检测失败，请手动检查更新。';
                }
            } else {
                container.innerHTML = '版本检测失败，请手动检查更新。';
            }
        }
    };

    ajax.onerror = function() {
        container.innerHTML = '版本检测失败，请手动检查更新。';
    };

    ajax.send();

    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }
})();
