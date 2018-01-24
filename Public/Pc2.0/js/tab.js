<<<<<<< .mine
$(function(){
	/*选项卡*/
	$(function(){
		window.onload = function()
		{
			var $div = $('.lf-left div');
			var $ul = $('.conter ul');
			$div.click(function(){
				var $this = $(this);
				var $t = $this.index();
				var qok = $(this).attr("qok");
				if (qok != 1) {
					return false;
				};
				$div.removeClass('left-div');
				$this.removeClass("cli-b");
				$this.addClass('cli');
				$this.siblings("div").removeClass("cli").addClass('left-div');
				$ul.slideUp(1);
				$ul.eq($t).slideDown(1);
				$this.each(function(){
					var qok = $(this).attr("qok");
					if (qok == 1) {
						$(this).addClass('cli-b');						
					};
				})
			})
		}
	});
	$(".right-li").click(function(){
			var type=$(this).attr("type");
			if (type =="left") {
				$(this).removeClass("right-li").addClass("right-libox").addClass("item_checkd").siblings("li").removeClass("right-libox").addClass('right-li').removeClass("item_checkd");
			}else if(type =="conf"){
				$(this).removeClass("right-li").addClass("right-libox").addClass("item3_checkd").siblings("li").removeClass("right-libox").addClass('right-li').removeClass("item3_checkd");				
			}else{
				$(this).removeClass("right-li").addClass("right-libox").addClass("item2_checkd").siblings("li").removeClass("right-libox").addClass('right-li').removeClass("item2_checkd");				
			}
			$(".cli").attr("qok","1");
			var txt = $(this).text();
			$(".cli span a:last").html(txt);
			$(".cli").addClass("cli-b");
			$(".cli").next().attr("qok","1");
			$(".cli").next().click();
	})
	$(".ul-f li").click(function(){
			$(".main-rf-b").css('display','block')
			$(".main-rf-a").css('display','none')
	})
})
$(function(){

	$(".position-iu").click(function(){
		$(".position-b").hide()
	})		
})



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





||||||| .r0
=======
$(function(){
	/*选项卡*/
	$(function(){
		window.onload = function()
		{
			var $div = $('.lf-left div');
			var $ul = $('.conter ul');
			$div.click(function(){
				var $this = $(this);
				var $t = $this.index();
				var qok = $(this).attr("qok");
				if (qok != 1) {
					return false;
				};
				$div.removeClass('left-div');
				$this.removeClass("cli-b");
				$this.addClass('cli');
				$this.siblings("div").removeClass("cli").addClass('left-div');
				$ul.slideUp(1);
				$ul.eq($t).slideDown(1);
				$this.each(function(){
					var qok = $(this).attr("qok");
					if (qok == 1) {
						$(this).addClass('cli-b');						
					};
				})
			})
		}
	});
	$(".right-li").click(function(){
			var type=$(this).attr("type");
			if (type =="left") {
				$(this).removeClass("right-li").addClass("right-libox").addClass("item_checkd").siblings("li").removeClass("right-libox").addClass('right-li').removeClass("item_checkd");
			}else if(type =="conf"){
				$(this).removeClass("right-li").addClass("right-libox").addClass("item3_checkd").siblings("li").removeClass("right-libox").addClass('right-li').removeClass("item3_checkd");				
			}else{
				$(this).removeClass("right-li").addClass("right-libox").addClass("item2_checkd").siblings("li").removeClass("right-libox").addClass('right-li').removeClass("item2_checkd");				
			}
			$(".cli").attr("qok","1");
			var txt = $(this).text();
			$(".cli span a:last").html(txt);
			$(".cli").addClass("cli-b");
			$(".cli").next().attr("qok","1");
			$(".cli").next().click();
	})
	$(".ul-f li").click(function(){
			$(".main-rf-b").css('display','block')
			$(".main-rf-a").css('display','none')
	})
})
$(function(){

	$(".position-iu").click(function(){
		$(".position-b").hide()
	})		
})
>>>>>>> .r32
