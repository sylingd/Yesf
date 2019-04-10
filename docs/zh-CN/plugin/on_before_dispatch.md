---
title: 分发开始（beforeDispatch）
lang: zh-CN
---

# 分发开始（beforeDispatch）

在路由解析完成后，分发请求开始前调用。若返回非NULL，则终止默认的请求分发流程


### 传入参数

* object $request 《请求处理》章节中的“$request变量”
* object $response 《请求处理》章节中的“$response变量

### 返回

返回NULL则继续默认的分发流程

返回非NULL则终止默认的请求分发流程