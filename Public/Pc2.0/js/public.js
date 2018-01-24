<<<<<<< .mine

$(function() {
	$(".i-wz-a").hover(function() {
		var a = $(".i-dh");
		"none" == a.css("display") ? a.slideDown(10) : a.slideUp(10);
		$(this).addClass("i-wz-b");
		$("a img", this).attr("src", "../public/Pc2.0/img/header-07b.png")
	}, function() {
		$(this).removeClass("i-wz-b");
		$("a img", this).attr("src", "../public/Pc2.0/img/header-07.png");
		$(".i-dh").slideUp(10)
	})
	$('.one-rf em .myGou').hover(function(){
        $(this).css({'background':'#fff','color':'#61bd52'})
        $('.strong').show()
        $('img',this).css('transform','rotate(0deg)')
    },function(){
        $('.strong').hide();
        $('.one-rf em .myGou').css({'background':'','color':''})
        $('img',this).css('transform','rotate(180deg)')
    })
});
    $(".i-wz-a").hover(function(){
        $('.icon-caret-up').css('transform','rotate(0deg)');
        $('.icon-caret-up').css('margin-top','7.3px');
    },function(){
        $('.icon-caret-up').css('transform','');
        $('.icon-caret-up').css('margin-top','');
    });
function mobile_device_detect(a) {
	for (var c = navigator.platform, d = "iPhone;iPod;iPad;android;Nokia;SymbianOS;Symbian;Windows Phone;Phone;Linux armv71;MAUI;UNTRUSTED/1.0;Windows CE;BlackBerry;IEMobile".split(";"), b = 0; b < d.length; b++) c.match(d[b]) && (window.location = a); - 1 != navigator.platform.indexOf("iPad") && (window.location = a);
	c = navigator.appVersion;
	c.match(/linux/i) && (c.match(/mobile/i) || c.match(/X11/i)) && (window.location = a);
	Array.prototype.in_array = function(a) {
		for (b = 0; b < this.length; b++) if (this[b] == a) return !0;
		return !1
	}
}
mobile_device_detect("http://m.ehuigou.com/");
var _hmt = _hmt || [];
(function() {
  var hm = document.createElement("script");
  hm.src = "//hm.baidu.com/hm.js?748fd8b980391dd89a630ff8b18693f7";
  var s = document.getElementsByTagName("script")[0]; 
  s.parentNode.insertBefore(hm, s);
})();
//客服
$(function(){
	$(".kef-ul li:eq(0)").hover(function(){
		$(this).addClass("kef-ul-litop-b").removeClass("kef-ul-litop");
	},function(){
		$(this).addClass("kef-ul-litop").removeClass("kef-ul-litop-b");
		});
	
	$(".kef-ul li:eq(1)").hover(function(){
		$(this).addClass("kef-ul-libottom-b").removeClass("kef-ul-libottom")
	},function(){
		$(this).addClass("kef-ul-libottom").removeClass("kef-ul-libottom-b")	
		});
	
	$(".kef-ul-libottom").click(function() {
      $("html,body").animate({scrollTop:0}, 500);
    }); 
  	
})

function show_cw (e) {
	$(".cw").text(e);
    $(".cw").slideDown(200).delay(2000).slideUp();
}
function show_cg (e) {
	$(".cg").text(e);
    $(".cg").slideDown(200).delay(2000).slideUp();
}

	var _hmt = _hmt || [];
    (function() {
      var hm = document.createElement("script");
      hm.src = "//hm.baidu.com/hm.js?748fd8b980391dd89a630ff8b18693f7";
      var s = document.getElementsByTagName("script")[0]; 
      s.parentNode.insertBefore(hm, s);
    })();
    
    var preg = /^((\+86)|(86))?((\(\d{3}\))|(\d{3}\-))?13[0-9]\d{8}|15[0-9]\d{8}|18[0-9]\d{8}|17[0-9]\d{8}$/; 
    var wait=60;
    var check = 0;
    
    function time(o) {
      if (wait == 0) {
        o.removeAttribute("disabled"); 
        o.removeAttribute("class");
        o.setAttribute("class","show");
        o.value="免费获取验证码";
        o.setAttribute
        wait = 60;
        check = 1;
      } else {
        check = 0;
        o.setAttribute("class","show1");
        o.setAttribute("disabled", true);
        o.value="重新发送(" + wait + ")";
        wait--;
        setTimeout(function() {
        time(o)
        },
        1000)
      }
    }
  function refresh(){
      setTimeout("location.reload()",2000);
  }

  $(".clear").click(function (){ 
          $(".seek-input").val('');
          $(".seek-ul").css('display','none')
  })||||||| .r0
=======

$(function() {
	$(".i-wz-a").hover(function() {
		var a = $(".i-dh");
		"none" == a.css("display") ? a.slideDown(10) : a.slideUp(10);
		$(this).addClass("i-wz-b");
		$("a img", this).attr("src", "../public/Pc2.0/img/header-07b.png")
	}, function() {
		$(this).removeClass("i-wz-b");
		$("a img", this).attr("src", "../public/Pc2.0/img/header-07.png");
		$(".i-dh").slideUp(10)
	})
	$('.one-rf em .myGou').hover(function(){
        $(this).css({'background':'#fff','color':'#61bd52'})
        $('.strong').show()
        $('img',this).css('transform','rotate(0deg)')
    },function(){
        $('.strong').hide();
        $('.one-rf em .myGou').css({'background':'','color':''})
        $('img',this).css('transform','rotate(180deg)')
    })
});
    $(".i-wz-a").hover(function(){
        $('.icon-caret-up').css('transform','rotate(0deg)');
        $('.icon-caret-up').css('margin-top','7.3px');
    },function(){
        $('.icon-caret-up').css('transform','');
        $('.icon-caret-up').css('margin-top','');
    });
function mobile_device_detect(a) {
	for (var c = navigator.platform, d = "iPhone;iPod;iPad;android;Nokia;SymbianOS;Symbian;Windows Phone;Phone;Linux armv71;MAUI;UNTRUSTED/1.0;Windows CE;BlackBerry;IEMobile".split(";"), b = 0; b < d.length; b++) c.match(d[b]) && (window.location = a); - 1 != navigator.platform.indexOf("iPad") && (window.location = a);
	c = navigator.appVersion;
	c.match(/linux/i) && (c.match(/mobile/i) || c.match(/X11/i)) && (window.location = a);
	Array.prototype.in_array = function(a) {
		for (b = 0; b < this.length; b++) if (this[b] == a) return !0;
		return !1
	}
}
mobile_device_detect("http://m.ehuigou.com/");
var _hmt = _hmt || [];
(function() {
  var hm = document.createElement("script");
  hm.src = "//hm.baidu.com/hm.js?748fd8b980391dd89a630ff8b18693f7";
  var s = document.getElementsByTagName("script")[0]; 
  s.parentNode.insertBefore(hm, s);
})();
//客服
$(function(){
	$(".kef-ul li:eq(0)").hover(function(){
		$(this).addClass("kef-ul-litop-b").removeClass("kef-ul-litop");
	},function(){
		$(this).addClass("kef-ul-litop").removeClass("kef-ul-litop-b");
		});
	
	$(".kef-ul li:eq(1)").hover(function(){
		$(this).addClass("kef-ul-libottom-b").removeClass("kef-ul-libottom")
	},function(){
		$(this).addClass("kef-ul-libottom").removeClass("kef-ul-libottom-b")	
		});
	
	$(".kef-ul-libottom").click(function() {
      $("html,body").animate({scrollTop:0}, 500);
    }); 
  	
})

function show_cw (e) {
	$(".cw").text(e);
    $(".cw").slideDown(200).delay(2000).slideUp();
}
function show_cg (e) {
	$(".cg").text(e);
    $(".cg").slideDown(200).delay(2000).slideUp();
}

	var _hmt = _hmt || [];
    (function() {
      var hm = document.createElement("script");
      hm.src = "//hm.baidu.com/hm.js?748fd8b980391dd89a630ff8b18693f7";
      var s = document.getElementsByTagName("script")[0]; 
      s.parentNode.insertBefore(hm, s);
    })();
    
    var preg = /^((\+86)|(86))?((\(\d{3}\))|(\d{3}\-))?13[0-9]\d{8}|15[0-9]\d{8}|18[0-9]\d{8}|17[0-9]\d{8}$/; 
    var wait=60;
    var check = 0;
    
    function time(o) {
      if (wait == 0) {
        o.removeAttribute("disabled"); 
        o.removeAttribute("class");
        o.setAttribute("class","show");
        o.value="免费获取验证码";
        o.setAttribute
        wait = 60;
        check = 1;
      } else {
        check = 0;
        o.setAttribute("class","show1");
        o.setAttribute("disabled", true);
        o.value="重新发送(" + wait + ")";
        wait--;
        setTimeout(function() {
        time(o)
        },
        1000)
      }
    }
  function refresh(){
      setTimeout("location.reload()",2000);
  }>>>>>>> .r32
