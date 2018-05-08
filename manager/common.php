<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/8
 * Time: 15:37
 */
function getAlert($detail='',$url=''){
    $str = "<script>";
    if(!empty($detail)) $str .= "alert('".$detail."');";
    if(!empty($url)){
        if($url=='back'){
            $str  .= "history.back(-1);";
        } else if($url=='reload'){
            $str .= 'history.reload();';
        }else{
            $str .= "location.href='".$url."';";
        }
    }
        $str .="</script>";
    echo $str;

}