# 路由启动（beforeRoute）

在路由启动前触发。可以用于用户的自定义路由并拦截默认的路由解析方法。

**注意：此事件仅会在HTTP请求中触发**

### 传入参数

* string $uri 请求的URL（不包含QUERY_STRING）

### 返回

返回NULL则继续默认的路由解析

返回非NULL则终止默认的路由解析