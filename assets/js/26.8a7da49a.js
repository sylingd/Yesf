(window.webpackJsonp=window.webpackJsonp||[]).push([[26],{200:function(t,s,a){"use strict";a.r(s);var e=a(0),r=Object(e.a)({},function(){var t=this,s=t.$createElement,a=t._self._c||s;return a("ContentSlotsDistributor",{attrs:{"slot-key":t.$parent.slotKey}},[a("h1",{attrs:{id:"response：响应"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#response：响应","aria-hidden":"true"}},[t._v("#")]),t._v(" Response：响应")]),t._v(" "),a("p",[t._v("用于响应一个请求")]),t._v(" "),a("h2",{attrs:{id:"header"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#header","aria-hidden":"true"}},[t._v("#")]),t._v(" header")]),t._v(" "),a("p",[a("code",[t._v("header('名称', '内容')")]),t._v("，如："),a("code",[t._v("$response->header('Content-Type', 'text/html');")])]),t._v(" "),a("p",[t._v("特别的，你可以使用"),a("code",[t._v("mimeType")]),t._v("来发送"),a("code",[t._v("Content-Type")]),t._v("头，如"),a("code",[t._v("$response->mimeType('html');")])]),t._v(" "),a("h2",{attrs:{id:"http状态码"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#http状态码","aria-hidden":"true"}},[t._v("#")]),t._v(" HTTP状态码")]),t._v(" "),a("p",[a("code",[t._v("status(状态码)")])]),t._v(" "),a("h2",{attrs:{id:"输出至浏览器"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#输出至浏览器","aria-hidden":"true"}},[t._v("#")]),t._v(" 输出至浏览器")]),t._v(" "),a("p",[a("code",[t._v("write('内容')")]),t._v("，如："),a("code",[t._v("$response->write('Hello World');")])]),t._v(" "),a("p",[t._v("特别的，你可以使用"),a("code",[t._v("json()")]),t._v("来输出JSON内容，如："),a("code",[t._v("$response->json($res);")])]),t._v(" "),a("h2",{attrs:{id:"发送文件"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#发送文件","aria-hidden":"true"}},[t._v("#")]),t._v(" 发送文件")]),t._v(" "),a("p",[t._v("当文件较大时，你可以使用此方法发送文件，而无需将其读入内存中，如：")]),t._v(" "),a("div",{staticClass:"language-php line-numbers-mode"},[a("pre",{pre:!0,attrs:{class:"language-php"}},[a("code",[a("span",{pre:!0,attrs:{class:"token variable"}},[t._v("$response")]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("-")]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(">")]),a("span",{pre:!0,attrs:{class:"token function"}},[t._v("mimeType")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),a("span",{pre:!0,attrs:{class:"token single-quoted-string string"}},[t._v("'zip'")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token variable"}},[t._v("$response")]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("-")]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(">")]),a("span",{pre:!0,attrs:{class:"token function"}},[t._v("sendfile")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),a("span",{pre:!0,attrs:{class:"token single-quoted-string string"}},[t._v("'/path/to/file.zip'")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(";")]),t._v("\n")])]),t._v(" "),a("div",{staticClass:"line-numbers-wrapper"},[a("span",{staticClass:"line-number"},[t._v("1")]),a("br"),a("span",{staticClass:"line-number"},[t._v("2")]),a("br")])]),a("h2",{attrs:{id:"cookie"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#cookie","aria-hidden":"true"}},[t._v("#")]),t._v(" Cookie")]),t._v(" "),a("p",[a("code",[t._v("cookie(Cookie信息)")])]),t._v(" "),a("table",[a("thead",[a("tr",[a("th",[t._v("名称")]),t._v(" "),a("th",[t._v("类型")]),t._v(" "),a("th",[t._v("内容")])])]),t._v(" "),a("tbody",[a("tr",[a("td",[t._v("name")]),t._v(" "),a("td",[t._v("string")]),t._v(" "),a("td",[t._v("名称")])]),t._v(" "),a("tr",[a("td",[t._v("value")]),t._v(" "),a("td",[t._v("string")]),t._v(" "),a("td",[t._v("内容")])]),t._v(" "),a("tr",[a("td",[t._v("expire")]),t._v(" "),a("td",[t._v("int")]),t._v(" "),a("td",[t._v("过期时间，-1为失效，不传递或0为SESSION，其他为"),a("code",[t._v("当前时间+$expire")])])]),t._v(" "),a("tr",[a("td",[t._v("path")]),t._v(" "),a("td",[t._v("string")]),t._v(" "),a("td",[t._v("Cookie有效的服务器路径。若不传递，则从环境配置读取")])]),t._v(" "),a("tr",[a("td",[t._v("domain")]),t._v(" "),a("td",[t._v("string")]),t._v(" "),a("td",[t._v("Cookie的有效域名。若不传递，则从环境配置读取")])]),t._v(" "),a("tr",[a("td",[t._v("httponly")]),t._v(" "),a("td",[t._v("bool")]),t._v(" "),a("td",[t._v("是否仅http传递，默认为否")])])])]),t._v(" "),a("p",[t._v("如：")]),t._v(" "),a("div",{staticClass:"language-php line-numbers-mode"},[a("pre",{pre:!0,attrs:{class:"language-php"}},[a("code",[a("span",{pre:!0,attrs:{class:"token variable"}},[t._v("$response")]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("-")]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(">")]),a("span",{pre:!0,attrs:{class:"token function"}},[t._v("cookie")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("(")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("[")]),t._v("\n\t"),a("span",{pre:!0,attrs:{class:"token single-quoted-string string"}},[t._v("'name'")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("=")]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(">")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token single-quoted-string string"}},[t._v("'token'")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n\t"),a("span",{pre:!0,attrs:{class:"token single-quoted-string string"}},[t._v("'value'")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("=")]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(">")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token single-quoted-string string"}},[t._v("'123456'")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n\t"),a("span",{pre:!0,attrs:{class:"token single-quoted-string string"}},[t._v("'expire'")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("=")]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(">")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token single-quoted-string string"}},[t._v("'3600'")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token comment"}},[t._v("//一小时有效")]),t._v("\n\t"),a("span",{pre:!0,attrs:{class:"token single-quoted-string string"}},[t._v("'path'")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("=")]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(">")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token single-quoted-string string"}},[t._v("'/'")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(",")]),t._v("\n\t"),a("span",{pre:!0,attrs:{class:"token single-quoted-string string"}},[t._v("'httponly'")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("=")]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(">")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token boolean constant"}},[t._v("true")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("]")]),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v(")")]),t._v("\n")])]),t._v(" "),a("div",{staticClass:"line-numbers-wrapper"},[a("span",{staticClass:"line-number"},[t._v("1")]),a("br"),a("span",{staticClass:"line-number"},[t._v("2")]),a("br"),a("span",{staticClass:"line-number"},[t._v("3")]),a("br"),a("span",{staticClass:"line-number"},[t._v("4")]),a("br"),a("span",{staticClass:"line-number"},[t._v("5")]),a("br"),a("span",{staticClass:"line-number"},[t._v("6")]),a("br"),a("span",{staticClass:"line-number"},[t._v("7")]),a("br")])]),a("h2",{attrs:{id:"模板"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#模板","aria-hidden":"true"}},[t._v("#")]),t._v(" 模板")]),t._v(" "),a("h3",{attrs:{id:"注册一个模板变量"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#注册一个模板变量","aria-hidden":"true"}},[t._v("#")]),t._v(" 注册一个模板变量")]),t._v(" "),a("p",[a("code",[t._v("assign('名称', '内容')")]),t._v("，如："),a("code",[t._v("$response->assign('name', 'Admin');")])]),t._v(" "),a("h3",{attrs:{id:"设置模板引擎"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#设置模板引擎","aria-hidden":"true"}},[t._v("#")]),t._v(" 设置模板引擎")]),t._v(" "),a("ul",[a("li",[t._v("设置全部："),a("code",[t._v("Response::setTemplateEngine(MyTemplate::class);")])]),t._v(" "),a("li",[t._v("设置当前响应："),a("code",[t._v("$response->setCurrentTemplateEngine(MyTemplate::class);")])])]),t._v(" "),a("h3",{attrs:{id:"关闭模板自动渲染"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#关闭模板自动渲染","aria-hidden":"true"}},[t._v("#")]),t._v(" 关闭模板自动渲染")]),t._v(" "),a("p",[t._v("在项目配置中：")]),t._v(" "),a("div",{staticClass:"language-php line-numbers-mode"},[a("pre",{pre:!0,attrs:{class:"language-php"}},[a("code",[a("span",{pre:!0,attrs:{class:"token single-quoted-string string"}},[t._v("'view'")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("=")]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(">")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("[")]),t._v("\n    "),a("span",{pre:!0,attrs:{class:"token single-quoted-string string"}},[t._v("'auto'")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v("=")]),a("span",{pre:!0,attrs:{class:"token operator"}},[t._v(">")]),t._v(" "),a("span",{pre:!0,attrs:{class:"token boolean constant"}},[t._v("false")]),t._v("\n"),a("span",{pre:!0,attrs:{class:"token punctuation"}},[t._v("]")]),t._v("\n")])]),t._v(" "),a("div",{staticClass:"line-numbers-wrapper"},[a("span",{staticClass:"line-number"},[t._v("1")]),a("br"),a("span",{staticClass:"line-number"},[t._v("2")]),a("br"),a("span",{staticClass:"line-number"},[t._v("3")]),a("br")])]),a("p",[t._v("当前响应："),a("code",[t._v("$response->disableView();")])]),t._v(" "),a("h3",{attrs:{id:"渲染指定模板并输出至浏览器"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#渲染指定模板并输出至浏览器","aria-hidden":"true"}},[t._v("#")]),t._v(" 渲染指定模板并输出至浏览器")]),t._v(" "),a("p",[t._v("默认会自动渲染View目录下同名的模板，使用此方法并不会关闭默认的渲染。")]),t._v(" "),a("p",[a("code",[t._v("display('模板路径，相对于当前模块的View目录')")])]),t._v(" "),a("p",[t._v("如："),a("code",[t._v("$response->display('user/view');")])])])},[],!1,null,null,null);s.default=r.exports}}]);