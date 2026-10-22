/**
 * AdminMD 后台交互（Typecho 1.3.0）
 *
 * 职责：
 *   1. 抽屉开关（按钮 / 遮罩 / Esc / 路由变化后自动收起）
 *   2. 抽屉内菜单折叠展开，并保持当前分组可见
 *   3. 无障碍：aria-expanded 同步、焦点回到触发按钮
 *   4. 回到顶部
 *
 * 不依赖 jQuery 与任何第三方 UI 框架，避免与后台插件争抢全局变量。
 *
 * @package AdminMD
 */
(function () {
    'use strict';

    var DRAWER = 'adminmd-drawer';
    var OVERLAY = 'adminmd-overlay';
    var TRIGGER = 'adminmd-trigger';
    var OPEN = 'adminmd-open';
    var ACTIVE = 'mdui-list-item-active';
    var COLLAPSE_OPEN = 'mdui-collapse-item-open';

    function onReady(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    onReady(function () {
        var drawer = document.getElementById(DRAWER);
        var overlay = document.getElementById(OVERLAY);
        var trigger = document.getElementById(TRIGGER);

        if (!drawer) {
            return;
        }

        document.body.classList.add('adminmd-has-drawer');

        // ---------- 结构调整 ----------
        // 抽屉由 menu.php 的 navBar 钩子输出，此时已位于 <nav> 内部。
        // 挪到 body 末尾，保证 fixed 定位与层级不受父级影响。
        if (drawer.parentNode !== document.body) {
            document.body.appendChild(drawer);
        }
        if (overlay && overlay.parentNode !== document.body) {
            document.body.appendChild(overlay);
        }

        // ---------- 折叠菜单 ----------
        var headers = drawer.querySelectorAll('.mdui-collapse-item-header');
        Array.prototype.forEach.call(headers, function (header) {
            header.setAttribute('role', 'button');
            header.setAttribute('tabindex', '0');

            var toggle = function () {
                var item = header.parentNode;
                var opened = item.classList.toggle(COLLAPSE_OPEN);
                header.setAttribute('aria-expanded', opened ? 'true' : 'false');
            };

            header.addEventListener('click', toggle);
            header.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggle();
                }
            });
        });

        // 当前分组默认展开并滚动到可视区域
        var active = drawer.querySelector('.' + ACTIVE);
        if (active) {
            var parent = active.closest('.mdui-collapse-item');
            if (parent) {
                parent.classList.add(COLLAPSE_OPEN);
            }
            window.setTimeout(function () {
                if (typeof active.scrollIntoView === 'function') {
                    active.scrollIntoView({ block: 'nearest' });
                }
            }, 60);
        }

        // ---------- 抽屉开关 ----------
        var lastFocus = null;

        function isDesktop() {
            return window.matchMedia('(min-width: 1024px)').matches;
        }

        function openDrawer() {
            lastFocus = document.activeElement;
            drawer.classList.add(OPEN);
            if (overlay) {
                overlay.hidden = false;
                overlay.classList.add(OPEN);
            }
            if (trigger) {
                trigger.setAttribute('aria-expanded', 'true');
            }
            document.body.style.overflow = 'hidden';

            var first = drawer.querySelector('.mdui-collapse-item-header');
            if (first) {
                first.focus();
            }
        }

        function closeDrawer(restoreFocus) {
            drawer.classList.remove(OPEN);
            if (overlay) {
                overlay.classList.remove(OPEN);
                window.setTimeout(function () {
                    if (!overlay.classList.contains(OPEN)) {
                        overlay.hidden = true;
                    }
                }, 200);
            }
            if (trigger) {
                trigger.setAttribute('aria-expanded', 'false');
            }
            document.body.style.overflow = '';

            if (restoreFocus && lastFocus && typeof lastFocus.focus === 'function') {
                lastFocus.focus();
            }
        }

        if (trigger) {
            trigger.addEventListener('click', function () {
                if (drawer.classList.contains(OPEN)) {
                    closeDrawer(true);
                } else {
                    openDrawer();
                }
            });
        }

        if (overlay) {
            overlay.addEventListener('click', function () {
                closeDrawer(true);
            });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && drawer.classList.contains(OPEN) && !isDesktop()) {
                closeDrawer(true);
            }
        });

        // 视口跨越断点时复位，避免桌面/移动态样式互相残留
        window.addEventListener('resize', function () {
            if (isDesktop()) {
                closeDrawer(false);
            }
        });

        // ---------- 回到顶部 ----------
        var top = document.createElement('button');
        top.type = 'button';
        top.id = 'adminmd-totop';
        top.setAttribute('aria-label', '返回顶部');
        top.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">'
            + '<path d="M12 4l-8 8h5v8h6v-8h5z"/></svg>';
        document.body.appendChild(top);

        top.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        window.addEventListener('scroll', function () {
            top.classList.toggle('show', window.pageYOffset > 360);
        }, { passive: true });
    });
})();
