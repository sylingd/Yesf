# $request变量

此变量用于储存一些请求信息

名称 | 类型 | 描述 | 
---|---|---|
param | array | 从URL参数中解析出的参数 | 
extension | string/null | 请求扩展名（如果开启了扩展名解析） | 
header | array | HTTP请求的头部信息。所有key均为小写 | 
server | array | HTTP请求相关的服务器信息，对应于PHP的$_SERVER数组。所有key均为小写 | 
get | array | HTTP请求的GET参数 | 
post | array | HTTP请求的POST参数 | 
cookie | array | HTTP请求携带的Cookie | 
files | array | 文件上传信息。类型为以form名称为key的二维数组。与PHP的$_FILES相同。 | 
rawContent | function | 获取原始的POST包体 | 

