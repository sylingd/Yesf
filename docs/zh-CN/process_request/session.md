---
title: Session
lang: zh-CN
---

# Session

## 配置

在环境配置中：

```ini
; Session最大生命时长，单位：s，默认为720
session.lifetime=720

; Session位于cookie中，或get参数中，默认为cookie
session.type=cookie
; session.type=get

; Cookie或Get参数名称，默认为yesfsessid
session.name=testsessid
```

## 获得Session

通过Request的session方法获得：

```php
public function MyAction(Request $request, Response $response) {
	$session = $request->session();
}
```

## 操作Session

### 使用数组操作

你可以像操作数组一样，操作Session

```php
public function MyAction(Request $request, Response $response) {
	$session = $request->session();
	$session['user'] = 'admin';
	echo $session['user'];
	unset($session['user']);
	var_dump(isset($session['user']));
}
```

### 使用方法操作

| 方法 | 描述 | 参数 | 返回 | 示例 |
| --- | ---- | --- | --- | ---- |
| id | 获得SessionID | 无 | string | `$id = $session->id();` | 
| set | 赋值 | `set(mixed 名称, mixed 值)` | 无 | `$session->set('name', 'admin');` |
| get | 获取 | `get(mixed 名称)` | mixed | `echo $session->get('name');` |
| has | 判断是否存在 | `has(mixed 名称)` | bool | `var_dump($session->has('name'));` |
| delete | 删除 | `delete(mixed 名称)` | 无 | `$session->delete('name');` |
| clear | 清空全部 | 无 | 无 | `$session->clear();` |

```php
public function MyAction(Request $request, Response $response) {
	$session = $request->session();
	echo $session->id();
	$session->set('user', 'admin');
	echo $session->get('user');
	$session->delete('user');
	var_dump($session->has('user'));
}
```

## 自定义SessionHandler

编写类，实现[SessionHandlerInterface](https://www.php.net/manual/zh/class.sessionhandlerinterface.php)。其中，open、close、gc均只需一个空方法。主要实现read、write、destroy，如：

```php
public function read($session_id) {
	return $this->cache->get('sess_' . $session_id, '');
}

public function write($session_id, $session_data) {
	return $this->cache->set('sess_' . $session_id, $session_data, $this->lifetime);
}

public function destroy($session_id) {
	return $this->cache->delete('sess_' . $session_id);
}
```

在Dispatcher上注册：

```php
namespace YesfApp;

class Bootstrap {
	/** @Autowired Yesf\Http\Dispatcher */
	public  $dispatcher;

	/** @Autowired MySessionHandler */
	public $session_handler;

	public function run() {
		$this->dispatcher->setSessionHandler($this->session_handler);
	}
}
```