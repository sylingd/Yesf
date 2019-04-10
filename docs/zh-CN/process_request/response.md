---
title: response变量
lang: zh-CN
---

# response变量

用于响应一个请求

方法 | 说明 | 参数 | 返回 | 
---|---|---|---|
setTplPath | 设置模板路径 | * string $tpl_path 模板路径 | 无 | 
disableView | 关闭模板自动渲染 | 无 | 无 | 
display | 将一个模板的渲染结果输出至浏览器 | * string $tpl 模板文件 | 无 | 
render | 获取一个模板的渲染结果但不输出 | * string $tpl 模板文件 | string | 
write | 将一个字符串输出至浏览器 | * string $content 要输出的字符串 | 无 | 
assign | 注册一个模板变量
（注：例如名称为key，则模板中使用$key获取） | * string $k 名称
* mixed $v 值 | 无 | 
header | 向浏览器发送一个header信息，例如`header('Content-Type', 'text/html')` | * string $k 名称
* mixed $v 内容 | 无 | 
status | 向浏览器发送一个状态码 | * int $code 状态码 | 无 | 
cookie | 设置Cookie | * array $param 下面各项均为此数组中的具体项目
* string $param[name] 名称
* string $param[value] 内容
* int $param[expire] 过期时间，-1为失效，0为SESSION，不传递为从config读取，其他为当前时间+$expire
* string $param[path] 若不传递，则从config读取
* string $param[domain] 若不传递，则从config读取
* boolean $param[https] 是否仅https传递，默认为否
* boolean $param[httponly] 是否为httponly | 无 | 
mimeType | 发送mimeType的header，基于`header`方法封装 | * string $extension 扩展名，例如`json` | 无 | 

