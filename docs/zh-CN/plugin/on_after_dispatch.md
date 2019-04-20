---
title: 分发完成（afterDispatch）
lang: zh-CN
---

# 分发完成（afterDispatch）

在默认的分发流程完成后调用，仅分发成功时会触发

### 传入参数

* object $request [Request](../process_request/request.md)
* object $response [Response](../process_request/response.md)
* mixed $result 如果调用相应的Action中有return语句，则此变量为return的结果，否则为NULL

### 返回

无需返回