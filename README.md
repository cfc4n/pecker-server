pecker-server
=============

pecker server是一个存储大量开源php产品源码中，危险函数token记录的服务器。用与[pecker][1] 扫描[结果][2]的服务器校对。



pecker-server的适用范围
=============

+ 使用者为大型公司的安全运维，负责对公司代码的安全审核，在项目初次上线时，将项目代码使用[pecker][1]扫描一遍，将包含危险函数的token存入pecker server的数据库中，标识为安全。
当定期检查服务器上的代码时，只需要将pecker的扫描结果([案例][2])跟pecker server校对一下即可知道是否为原有安全代码。无需对比文件md5、大小等等。并且，服务器上的文件做了适配当前服务器的改动时，只要不涉及危险函数，均不会被误报。

+ 安全公司人员，对网络上已经开源的项目，挨个版本做收录，并收录流行木马特征。站长使用时，只需要下载自己定制的[pecker][1]到其站点目录下，根据扫描结果，批量将扫描打包发送到服务端验证，服务端根据站长的程序的版本标识，细化的给出这些token代码的安全程度，更精确的反馈扫描结果。




DEMO
=============
[http://pecker.cnxct.com][3]为demo程序，只提供API接口的调用，结果输出支持jsonp回调，以及支持普通http结果回调。若请求参数中包含jsonp回调标识pecker_jsonp则返回jsonp格式。否则，返回标准json字符串。
使用案例可参考[pecker scanner report][2]内的JS代码。



















[1]:[https://github.com/cfc4n/pecker-server]
[2]:[http://www.cnxct.com/cfc4n/pecker-scanner-report.html]
[3]:[http://pecker.cnxct.com]
