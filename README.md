# PHP实现异步和并发请求

  通过CURL实现并发请求，请求时间等于请求时间最长的请求

  通过Sock实现异步请求

# 安装
  通过Composer安装
```json
    "require": {
        ……
        "lee/request": "1.0.*",
        ……
    },
    "repositories": [
        ……
        {
            "type": "vcs",
            "url": "git@github.com:xiaoyao-work/lee-request.git"
        }
        ……
    ],
```

# Demos
  直接运行 demos/Concurrence.php 来查看并发结果
  运行 demos/Async.php 来查看异步结果
