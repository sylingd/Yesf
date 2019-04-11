---
title: 综述
lang: zh-CN
---

# Yesf

![Php Version](https://img.shields.io/badge/php-%3E=7.1-brightgreen.svg?maxAge=2592000)
![Swoole Version](https://img.shields.io/badge/swoole-%3E=4.0-brightgreen.svg?maxAge=2592000)
![Packagist](https://img.shields.io/packagist/v/sylingd/yesf-framework.svg)
![license](https://img.shields.io/github/license/sylingd/Yesf.svg)

Yesf是基于Swoole 4.0+的框架。具有以下优点：

* 高性能
* 灵活、扩展能力强
* 单元测试覆盖

Yesf基于Swoole，因此还支持TCP监听、UDP监听、异步任务等功能

# 文档说明

本文档对应Yesf版本为`2.0.0`，如有错误请提交issue至[GitHub](https://github.com/sylingd/Yesf/issues/new)或[Gitee](https://gitee.com/sy/Yesf/issues/new)

# 命名规范

目前Yesf命名规范如下：

### 类库命名

* 所有类库均在`Yesf\\`命名空间下
* 遵循PSR-1，类的命名都遵循大驼峰命名法，方法的命名均为小驼峰命名法

### 变量命名

* 大部分变量都遵循小驼峰命名法
* 部分变量以“_”（下划线）开头时，会遵循以下划线分割的命名法则

### 其他特殊命名

* 抽象类名称均为“名称 + Abstract”，例如`SimpleAbstract`
* Trait名称均为“名称 + Trait”，例如`SimpleTrait`
* 接口名称均为“名称 + Interface”，例如`SimpleInterface`

# PSR规范

目前遵循以下PSR规范：

* [PSR-1: Basic Coding Standard](https://www.php-fig.org/psr/psr-1/)
* [PSR-3: Logger Interface](https://www.php-fig.org/psr/psr-3/)
* [PSR-4: Autoloader](https://www.php-fig.org/psr/psr-4/)
* [PSR-11: Container interface](https://www.php-fig.org/psr/psr-11/)
* [PSR-16: Simple Cache](https://www.php-fig.org/psr/psr-16/)

已经确定不会遵循或不完全遵循的PSR规范有：

* PSR-2: Coding Style Guide 与作者编码习惯有一些差别，不完全遵循
* PSR-6: Caching Interface 过于繁琐