<?php

class MStatus
{
    const SYSTEM_TEMPNAME = 'tmp';    // not used
    
    const RETURN_SUCCESS = 1000;
    const RETURN_ILLEGAL_REQUEST = 2000;    //非法请求
    const RETURN_ILLEGAL_PARAM = 2001;    //非法参数
    const RETURN_SYNTAX_ERROR = 2002;    //PHP语法错误
    const RETURN_NONE_EVIL_CODE = 2003;    //不包含危险代码
    const RETURN_NOT_FOUND_IN_DB = 2004;    //指纹库中没找到这个数据
}
?>