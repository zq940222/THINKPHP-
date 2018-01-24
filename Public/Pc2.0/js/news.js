<<<<<<< .mine
// JavaScript Document

//导航下拉



//头部滚动固定
    $(function () {
        var ie6 = document.all;
        var dv = $('#fixedMenu_top_com'), st;
        dv.attr('otop', dv.offset().top); //存储原来的距离顶部的距离
        $(window).scroll(function () {
            st = Math.max(document.body.scrollTop || document.documentElement.scrollTop);
            if (st > parseInt(dv.attr('otop'))) {
                if (ie6) {//IE6不支持fixed属性，所以只能靠设置position为absolute和top实现此效果
                    dv.css({ position: 'absolute', top: st });
                }
                else if (dv.css('position') != 'fixed'){dv.css({ 'position': 'fixed', top: 0 });dv.find('.top-one').css('margin-left','-600');$('.conter').height('');$('.seek').css('display','block');}
            } else if (dv.css('position') != 'static'){dv.css({ 'position': 'static' });$('.conter').height('');$('.seek').css('display','none');}
        });
    });

//回收分类

//品牌展开		
function liopen(){
	$(".pinp-yc ul:eq(0)").toggleClass('none');
	$(".pinp-yc ul:eq(1)").toggleClass('none');
	}



//机型
$(function(){
	$('.jix-ullf span').click(function(){
		$('.jix-ullf span').removeClass('ullf-li')
		$(this).addClass("b-ullf-li").siblings().removeClass("b-ullf-li");
	})
})


//热门机型图片展示
$(".mr_frbox").slide({
	titCell:"",
	mainCell:".mr_frUl ul",
	autoPage:true,
	effect:"leftLoop",
	autoPlay:true/*false关闭自动*/,
	vis:6,
	scroll:6,
});


//专业质检流程轮播
  $(document).ready(function(e) {
	  $autorf=rfclick(); //引用自动单击事件
    $(".scrollrf").click(function(){
		clearTimeout($autorf);  //清除自动单击方法
		
		$cc=$(".mnu-rf-yj ul").find(".cli");
		$k=$(".mnu-rf-yj ul li").index($cc);
		if($k>=4){
			$index=0;	
		}else{
			$index=$k+1;
		}
	   $(".mnu-rf-yj ul li").eq($index).addClass("cli").siblings().removeClass("cli");
	   $(".mnu-rf-yj ul li").eq($index).fadeIn(500).siblings().fadeOut(500);
	   	
		$autorf=setTimeout(rfclick,3000);	 

	})
   $(".scrolllf").click(function(e) {
    $cc=$(".mnu-rf-yj ul").find(".cli");
		$k=$(".mnu-rf-yj ul li").index($cc);
		if($k==0){
			$index=4;	
		}else{
			$index=$k-1;
		}
	   $(".mnu-rf-yj ul li").eq($index).addClass("cli").siblings().removeClass("cli");
	   $(".mnu-rf-yj ul li").eq($index).fadeIn(500).siblings().fadeOut(500);
});
  $(".mnu-rf-yj").hover(function(){
	  $(".scrollrf,.scrolllf").fadeIn("fast");
	    $(".mnu-rf-yj").mouseleave(function(e) {
	  $(".scrollrf,.scrolllf").fadeOut("fast");
        });
	  })
});
function rfclick(){
		$(".scrollrf").trigger("click");
	$autorf=setTimeout(rfclick,3000)	;
		
	return $autorf;
}


||||||| .r0
=======
// JavaScript Document

//导航下拉



//头部滚动固定
    $(function () {
        var ie6 = document.all;
        var dv = $('#fixedMenu_top_com'), st;
        dv.attr('otop', dv.offset().top); //存储原来的距离顶部的距离
        $(window).scroll(function () {
            st = Math.max(document.body.scrollTop || document.documentElement.scrollTop);
            if (st > parseInt(dv.attr('otop'))) {
                if (ie6) {//IE6不支持fixed属性，所以只能靠设置position为absolute和top实现此效果
                    dv.css({ position: 'absolute', top: st });
                }
                else if (dv.css('position') != 'fixed'){dv.css({ 'position': 'fixed', top: 0 });dv.find('.top-one').css('margin-left','-600');$('.conter').height('');}
            } else if (dv.css('position') != 'static'){dv.css({ 'position': 'static' });$('.conter').height('');}
        });
    });

//回收分类

//品牌展开		
function liopen(){
	$(".pinp-yc ul:eq(0)").toggleClass('none');
	$(".pinp-yc ul:eq(1)").toggleClass('none');
	}



//机型
$(function(){
	$('.jix-ullf span').click(function(){
		$('.jix-ullf span').removeClass('ullf-li')
		$(this).addClass("b-ullf-li").siblings().removeClass("b-ullf-li");
	})
})


//热门机型图片展示
$(".mr_frbox").slide({
	titCell:"",
	mainCell:".mr_frUl ul",
	autoPage:true,
	effect:"leftLoop",
	autoPlay:true/*false关闭自动*/,
	vis:6,
	scroll:6,
});


//专业质检流程轮播
  $(document).ready(function(e) {
	  $autorf=rfclick(); //引用自动单击事件
    $(".scrollrf").click(function(){
		clearTimeout($autorf);  //清除自动单击方法
		
		$cc=$(".mnu-rf-yj ul").find(".cli");
		$k=$(".mnu-rf-yj ul li").index($cc);
		if($k>=4){
			$index=0;	
		}else{
			$index=$k+1;
		}
	   $(".mnu-rf-yj ul li").eq($index).addClass("cli").siblings().removeClass("cli");
	   $(".mnu-rf-yj ul li").eq($index).fadeIn(500).siblings().fadeOut(500);
	   	
		$autorf=setTimeout(rfclick,3000);	 

	})
   $(".scrolllf").click(function(e) {
    $cc=$(".mnu-rf-yj ul").find(".cli");
		$k=$(".mnu-rf-yj ul li").index($cc);
		if($k==0){
			$index=4;	
		}else{
			$index=$k-1;
		}
	   $(".mnu-rf-yj ul li").eq($index).addClass("cli").siblings().removeClass("cli");
	   $(".mnu-rf-yj ul li").eq($index).fadeIn(500).siblings().fadeOut(500);
});
  $(".mnu-rf-yj").hover(function(){
	  $(".scrollrf,.scrolllf").fadeIn("fast");
	    $(".mnu-rf-yj").mouseleave(function(e) {
	  $(".scrollrf,.scrolllf").fadeOut("fast");
        });
	  })
});
function rfclick(){
		$(".scrollrf").trigger("click");
	$autorf=setTimeout(rfclick,3000)	;
		
	return $autorf;
}


>>>>>>> .r32
