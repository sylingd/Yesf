# 分发失败（dispatchFailed）

当分发因为找不到相应模块/控制器/功能，或分发途中发生任何其他异常时，均会触发此事件

### 传入参数

* object $request 《请求处理》章节中的“$request变量”
* object $response 《请求处理》章节中的“$response变量
* object/null $error 如果是因为发生异常而触发，则为Throwable类，否则为null

### 返回

返回NULL则显示默认的错误页面

返回非NULL则不显示默认的错误页面