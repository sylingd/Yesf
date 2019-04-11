---
title: 路由
lang: zh-CN
---

# 路由

## 内置路由

> 内置路由位于 src/Http/Router.php

内置路由支持Rewrite、Regex、Map三种方式解析URL。优先级为 Regex > Rewrite > Map。

### 前缀

URL若以此开头，则此部分不会参与解析。如：

```php
use Yesf\Http\Router;

function run(Router $router) {
	$router->setPrefix('/user');
}
```

访问`http://example.com/user/index/user/view`时，实际参与解析的是`index/user/view`。

默认为`/`。

### Regex

以正则方式解析。

```php
use Yesf\Http\Router;

function run(Router $router) {
	$router->addRegex('/^thread-view-([0-9]+)-([0-9]+)\\.html$/', [
		'module' => 'index',
		'controller' => 'forum',
		'action' => 'view'
	], [
		// 完成数字到字符变量的映射
		1 => 'id',
		2 => 'page'
	]);
}

// 当访问 thread-view-123-2.html 时
class Forum extends ControllerAbstract implements ControllerInterface {
	public function ViewAction(Request $request, Response $response) {
		echo $request->param['id']; // 123
		echo $request->param['page']; // 2
	}
}
```

### Rewrite

以简单的重写方式解析。

匹配的路径使用一个特别的标识来告诉路由协议如何匹配到路径中的每一个段，这个标识有有两种，可以帮助我们创建我们的路由协议：

* 冒号（：）：包含一个变量用于传递到我们动作控制器中的变量，例如我们使用`:name`，，我们可以在动作中使用`$request->param['name']`获取到它的值。

* 星号（\*）：一个通配符, 意思是在它后面的所有段都将作为一个通配数据被存储，例如我们使用'path/:name/*'，我们访问的`/path/foo/test/value1/another/value2`，那么我们会得到下面的结果：

```php
echo $request->param['test']; // value1
echo $request->param['another']; // value2
```

Demo：

```php
use Yesf\Http\Router;

function run(Router $router) {
	$router->addRewrite('user/:id/feed/*', [
		'module' => 'index',
		'controller' => 'user',
		'action' => 'feed'
	]);
}

// 当访问 user/123/feed/page/1/filter/video 时
class User extends ControllerAbstract implements ControllerInterface {
	public function FeedAction(Request $request, Response $response) {
		echo $request->param['id']; // 123
		echo $request->param['page']; // 1
		echo $request->param['filter']; // video
	}
}
```

### Map

默认的解析方式，不需要特别配置。将会按`module/controller/action`的规则解析。

如`index/user/view?id=1`会解析至Index模块、User控制器、View功能。

## 自定义路由

可以自定义路由，不使用内置路由。

首先，编写类实现RouterInterface，如：

```php
use Yesf\Http\RouterInterface;

class MyRouter implements RouterInterface {
	public function parse(Request $request) {
		if (strpos($request->server['request_uri'], '/user') === 0) {
			$request->module = 'user';
			$request->controller = 'index';
			$request->action = 'index';
		} else {
			$request->module = 'index';
			$request->controller = 'index';
			$request->action = 'index';
		}
	}
}
```

将其注册至Dispatcher：

```php
namespace YesfApp;
use MyRouter;
use Yesf\Http\Dispatcher;

class Bootstrap {
	public Dispatcher $dispatcher;
	public MyRouter $router;
	public function run() {
		$this->dispatcher->setRouter($this->router);
	}
}
```