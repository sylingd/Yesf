# 分发完成（afterDispatcher）

在默认的分发流程完成后调用，仅分发成功时会触发

### 传入参数

* object $request 《请求处理》章节中的“$request变量”
* object $response 《请求处理》章节中的“$response变量
* mixed $result 如果调用相应的Action中有return语句，则此变量为return的结果，否则为NULL

### 返回

无需返回