# README
这是一个简易的框架核心。
## 容器
App 类是框架的核心类，继承了 Illuminate\Container\Container。此类除了实现 Psr\Container\ContainerInterface 容器接口之外，还提供了创建对象和执行方法时自动注入参数的功能。
## 服务提供者
服务提供者需要继承 ServiceProvider 类，并且要在在框架初始化之前注册。
## 请求调度
请求调度由 Http 类完成，此类负责调用全局中间件和路由，最终生成响应对象并输出。

## 中间件
全局中间件由 Http::loadMiddleware() 方法载入。

## 路由
框架基于 nikic/fast-route 封装了 Router 类，你也可以使用自己喜欢的路由组件。

## 路由中间件
框架提供的路由提供了路由中间件的功能。

## 异常
Error 类捕获了所有级别（E_ALL）的异常，并抛出 Exception\ErrorException。Exception\Handler 类报告并输出异常，你也可以继承此类并将自己的实现绑定到这个类上。

