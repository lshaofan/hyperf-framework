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


# 命令行介绍


## 添加Action

-   根据项目规范快速创建Action。
-  参数一class名称，无需定义根命名空间会根据path自动匹配命名空间前缀
-  参数二 --path 即生成Action的路径
```bash
php bin/hyperf.php gbGen:action Backend/UserInfo --path=app/core/user/src/Action
```

### 生成文件结构

```

./app/core/user
|—— Action --------------------------------- 用户动作	
│   ├── Admin------------------------------- admin后台控制器  
│   │   ├── UserInfo------------------------ 用户信息  
│   │   │   ├── Index.php----------------------- 查询 - 多条 (列表 - 分页搜索)  
│   │   │   ├── Show.php------------------------ 查询 - 单条 
│   │   │   ├── Create.php---------------------- 添加 - 页面
│   │   │   ├── Store.php----------------------- 添加 - 动作
│   │   │   ├── Edit.php------------------------ 修改 - 页面
│   │   │   ├── Update.php---------------------- 修改 - 动作
│   │   │   ├── Destroy.php--------------------- 删除 - 动作
```

## 生成model

-   重写Hyperf框架gen:model 命令。
-  参数同gen:model参数。
> 请先阅读  [Hyperf 框架模型章节]([模型 (hyperf.wiki)](https://hyperf.wiki/2.2/#/zh-cn/db/model))
```bash
php bin/hyperf.php gbGen:model admin_user --path=app/core/user/src/Model
```

执行命令后会同时在Model目录下创建`Contract/${model}Contract`接口类，及`SERVICE/${MODEL}Service`服务类。
>服务契约绑定如果命名规范不是  `${model}Contract`=>`SERVICE/${MODEL}Service`
>需要自行在 `config/autoload/dependencies.php`  内添加绑定契约接口服务的绑定关系。
>请参阅  [Hyperf 框架DI章节]([依赖注入 (hyperf.wiki)](https://hyperf.wiki/2.2/#/zh-cn/di?id=%e6%8a%bd%e8%b1%a1%e5%af%b9%e8%b1%a1%e6%b3%a8%e5%85%a5)))

