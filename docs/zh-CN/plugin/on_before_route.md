---
title: 路由启动（beforeRoute）
lang: zh-CN
---

# 路由启动（beforeRoute）

在路由启动前触发。可以用于拦截默认的路由解析方法。可以直接设置$request的module、controller、action进行解析

### 传入参数

* object $request [Request](../process_request/request.md)
* object $response [Response](../process_request/response.md)

### 返回

返回NULL则继续默认的路由解析

返回非NULL则终止默认的路由解析
