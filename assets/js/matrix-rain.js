/**
 * Matrix Rain Effect for AdminMD
 * 黑客帝国数字雨效果
 */
window.onload = function() {
    var canvas = document.getElementById("canvas");
    if (!canvas) return;

    var context = canvas.getContext("2d");
    var W = window.innerWidth;
    var H = window.innerHeight;
    canvas.width = W;
    canvas.height = H;

    var fontSize = 16;
    var columns = Math.floor(W / fontSize);
    var drops = [];

    for (var i = 0; i < columns; i++) {
        drops.push(0);
    }

    var str = "111001101000100010010001111001111000100010110001111001001011110110100000";

    function draw() {
        context.fillStyle = "rgba(0,0,0,0.05)";
        context.fillRect(0, 0, W, H);
        context.font = "700 " + fontSize + "px 微软雅黑";
        context.fillStyle = "#00cc33";

        for (var i = 0; i < columns; i++) {
            var index = Math.floor(Math.random() * str.length);
            var x = i * fontSize;
            var y = drops[i] * fontSize;
            context.fillText(str[index], x, y);

            if (y >= canvas.height && Math.random() > 0.99) {
                drops[i] = 0;
            }
            drops[i]++;
        }
    }

    draw();
    setInterval(draw, 30);
};
