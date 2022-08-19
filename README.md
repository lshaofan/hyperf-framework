# Framework

## 发布配置文件
```bash
php bin/hyperf.php vendor:publish gb-hyperf/framework
```


## response 介绍
response 主要用来统一 API 开发过程中「成功」、「失败」以及「异常」情况下的响应数据格式。
实现过程简单，在原有的 $response->json()进行封装，使用时不需要有额外的心理负担。
遵循一定的规范，返回易于理解的 HTTP 状态码，并支持定义 ResponseCodeEnum 来满足不同场景下返回描述性的业务操作码。 

### 概览

- 统一的数据响应格式，固定包含：`code`、`type`、`result`、`message`、`error` (响应格式设计源于：[RESTful服务最佳实践](https://www.cnblogs.com/jaxu/p/7908111.html#a_8_2) )
- 支持配置返回字段是否显示，以及为她们设置别名，比如，将 `message` 别名设置为 `msg`，或者 分页数据第二层的 `data` 改成 `list`(res.data.data -> res.data.list)
- 内置 Http 标准状态码支持，同时支持扩展 ResponseCodeEnum 来根据不同业务模块定义响应码(可选，需要安装 `jiannei/laravel-enum`)

### 成功响应
```json
{
    "status": "success",
    "code": 200,
    "message": "操作成功",
    "result": 
         [
            {
                "nickname": "Joaquin Ondricka",
                "email": "lowe.chaim@example.org"
            },
            {
                "nickname": "Jermain D'Amore",
                "email": "reanna.marks@example.com"
            },
            {
                "nickname": "Erich Moore",
                "email": "ernestine.koch@example.org"
            }
        ]
    ,
    "error": {}
}
```
#### 示例代码
```php
    #[RequestMapping(path: '/api/admin/auth/login', methods: ['POST', 'GET'])]
    public function handle(): ResponseInterface
    {
        return $this->success(['token' => 'admin'], '登录成功', 200);
    }
```
#### 返回单条数据

```json
{
    "status": "success",
    "code": 200,
    "message": "操作成功",
    "data": {
        "nickname": "liushaofan",
        "email": "liushaofan@greenbirds.cn"
    },
    "error": {}
}
```

#### 其他快捷方法

```php
$this->success(['token' => 'admin'], '登录成功', 200); // 成功响应
$this->ok();// 无需返回 data，只返回 message 情形的快捷方法
$this->localize(200101);// 无需返回 data，message 根据响应码配置返回的快捷方法
$this->accepted(); // 无需返回 data，message 根据响应码配置返回的快捷方法
$this->created(); // 使用创建的响应进行响应并关联位置（如果提供） 固定返回状态码201。
$this->noContent(); // 以无内容响应进行响应。
$this->fail('失败',500,$errors); // 默认返回状态码500。
```