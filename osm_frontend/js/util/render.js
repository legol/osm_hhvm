/**
 * Created by ChenJie3 on 2016/2/18.
 */

if (!Render) {
    var Render = function () {
    };

    // 1. 获取 template_url 指向的模板；
    // 2. 将 data 填充到模板；
    // 3. 将填充好的做为text填入target标签内
    Render.prototype.render = function (target, template_url, data) {
        $.ajax({
        url: template_url,
        dataType: 'text',
        async: true,
        type: 'GET',
        success: function (templateText) {
            var obj = target;
            obj.setTemplate(templateText);
            obj.processTemplate(data);
        }
    });
    };
}
