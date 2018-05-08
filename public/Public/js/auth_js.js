/*
*  note:配合后台验证权限的返回并针对验证后的消息跳转到登录或者错误页面
*  author:lanlc
*  datetime:2016年11月2日12:01:36
*  realse: 
*
*
*/
!function ($) {
    "use strict";
    $.extend({
        loginPage: "/Manager/Login/LoginOut",
        exceptionPage: "/Manager/Fail/Index",
        ToastLogin: function (msg) {
            alert(msg);
            window.location.href = $.loginPage;
        },
        GoException: function (msg) {
            window.location.href = $.exceptionPage + "?errorMsg=" + msg;
        },
        AuthGet : function (url, callback) {
            $.cacheGet(url, function (result) {
                if (result.IsError) {
                    if (result.IsGoLogin) {
                        //提示错误并跳转到登录按钮
                        $.ToastLogin(result.ErrorMessage);
                    }
                    else {
                        $.GoException(result.ErrorMessage);
                    }
                }
                else {
                    callback(result.Data);//带回具体数据
                }
            });
        },
        AuthPost : function (url,data,callback)
        {
            $.post(url, data, function (result) {
                if (result.IsError) {
                    if (result.IsGoLogin) {
                        //提示错误并跳转到登录按钮
                        $.ToastLogin(result.ErrorMessage);
                    }
                    else {
                        swal(result.ErrorMessage);
                        //$.GoException(result.ErrorMessage);
                    }
                }
                else {
                    callback(result.Data);//带回具体数据
                }
            });
        }
    });

    $.extend({
        cacheGet: function (url, callback)
        {
            //如果有缓存功能
            if (window.sessionStorage) {
                //如果有缓存数据
                if (window.sessionStorage.getItem(url) != null) {
                    var jsonStr = window.sessionStorage.getItem(url);
                    callback(JSON.parse(jsonStr));
                }
                else {
                    //请求数据并缓存
                    $.get(url, {}, function (result) {
                        window.sessionStorage.setItem(url, JSON.stringify(result));
                        callback(result);
                    });
                }
            }
            else {
                //直接请求返回
                $.get(url, {}, function (result) {
                    callback(result);
                });
            }
        }
    });

}(window.jQuery);