# 请求处理

请求流程：

* Yesf接收请求
* beforeRoute事件
* 路由解析
* beforeDispatch事件
* 调用相应Controoler、Action
* afterDispatch事件
* 结束请求