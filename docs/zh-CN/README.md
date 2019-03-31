# Yesf

[![Php Version](https://img.shields.io/badge/php-%3E=7.1-brightgreen.svg?maxAge=2592000)](https://secure.php.net/)
[![Swoole Version](https://img.shields.io/badge/swoole-%3E=4.0-brightgreen.svg?maxAge=2592000)](https://github.com/swoole/swoole-src)
[![Packagist](https://img.shields.io/packagist/v/sylingd/yesf-framework.svg)](https://packagist.org/packages/sylingd/yesf-framework)
[![GitHub stars](https://img.shields.io/github/stars/sylingd/Yesf.svg?logo=github&label=Stars)](https://github.com/sylingd/Yesf)
[![Gitee stars](https://gitee.com/sy/Yesf/badge/star.svg?theme=dark)](https://gitee.com/sy/Yesf)
[![license](https://img.shields.io/github/license/sylingd/Yesf.svg)](https://github.com/sylingd/Yesf/blob/master/LICENSE)

Yesf是基于Swoole 4.0+，主要针对网站而编写的框架。具有以下优点：
1.高性能
2.灵活的自动加载
3.灵活可扩展
4.内建多种路由, 可以兼容目前常见的各种路由协议
5.支持多种配置方式
同时，Yesf基于Swoole，因此还支持TCP监听、UDP监听、异步任务等功能

# 文档说明

本文档对应Yesf版本为`1.0.0-rc9`，如有错误请提交issue至[GitHub](https://github.com/sylingd/Yesf/issues/new)或[Gitee](https://gitee.com/sy/Yesf/issues/new)

# 命名规范

目前Yesf命名规范如下：

### 类库命名
1.所有类库均在`yesf\library`命名空间下
2.所有类库均在`library`目录下
3.类的命名都遵循大驼峰命名法
4.方法的命名均为小驼峰命名法

### 变量命名
1.大部分变量都遵循小驼峰命名法
2.部分变量以“_”（下划线）开头时，会遵循以下划线分割的命名法则

### 常量命名
1.框架的基本常量均以`YESF_`开头
2.其他常量均位于`yesf\Constant`中，且基本遵循“模块\_类型\_描述”的命名法，例如“ROUTER_ERR_CONTROLLER”

### 其他特殊命名
1.用于继承的抽象类，名称均为“名称 + Abstract”，例如`SimpleAbstract`
2.用于规范的接口，名称均为“名称 + Interface”，例如`SimpleInterface`

