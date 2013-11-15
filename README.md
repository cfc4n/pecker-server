介绍
=============

pecker server是一个存储大量开源php产品源码中，危险函数token记录的服务器。用与[pecker][1] 扫描[结果][2]的服务器校对。

安装
=============
+ 依赖[yaf][4]拓展开发的程序，务必确保yaf拓展已经安装。
+ 依赖Pecker Scanner 类库来分析检测php代码，以剥离正确token。必无下载pecker scanner的类库，放到lib目录下。
+ 导入db目录下的数据库
+ 更改config目录下的配置文件
+ 使用[pecker scanner][1]扫描出来的结果，更改报告文件源代码中的pecker scanner的域名以及路径，保存之后，点击扫描即可。

适用范围
=============

+ 使用者为大型公司的安全运维，负责对公司代码的安全审核，在项目初次上线时，将项目代码使用[pecker][1]扫描一遍，将包含危险函数的token存入pecker server的数据库中，标识为安全。
当定期检查服务器上的代码时，只需要将pecker的[扫描结果][2]跟pecker server校对一下即可知道是否为原有安全代码。无需对比文件md5、大小等等。并且，服务器上的文件做了适配当前服务器的改动时，只要不涉及危险函数，均不会被误报。

+ 安全公司人员，对网络上已经开源的项目，挨个版本做收录，并收录流行木马特征。站长使用时，只需要下载自己定制的[pecker][1]到其站点目录下，根据扫描结果，批量将扫描打包发送到服务端验证，服务端根据站长的程序的版本标识，细化的给出这些token代码的安全程度，更精确的反馈扫描结果。




DEMO
=============
[http://pecker.cnxct.com][3]为demo程序，只提供API接口的调用，结果输出支持jsonp回调，以及支持普通http结果回调。若请求参数中包含jsonp回调标识pecker_jsonp则返回jsonp格式。否则，返回标准json字符串。
使用案例可参考[pecker scanner report][2]内的JS代码。


Info
=====
+ [Home Page][6]
+ [WeiBo][5]








  [1]: https://github.com/cfc4n/pecker  "pecker"
  [2]: http://www.cnxct.com/cfc4n/pecker-scanner-report.html    "pecker scanner report"
  [3]: http://pecker.cnxct.com    "pecker server demo"
  [4]: http://pecl.php.net/package/yaf "Yaf Framework"
  [5]: http://weibo.com/n/CFC4N   "CFC4N的微博"
  [6]: http://www.cnxct.com/pecker-scanner/   "CFC4N的博客"
