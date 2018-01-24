// JavaScript Document



//导航下拉
$(function(){
	$(".one-a").hover(function(){
		var span = $(".one-b");
		if(span.css("display")=="none"){
			span.slideDown(10);
		}else{
			span.slideUp(10);
		}
			
	$(this).addClass('one-bb')
		$("a img",this).attr("src","../public/Pc2.0/img/nav-02b.png");
		$('.one-a-a',this).addClass('one-a-a1')
	},function(){
		$('.one-a-a',this).removeClass('one-a-a1')
		$(this).removeClass('one-bb')
		$("a img",this).attr("src","../public/Pc2.0/img/nav-02.png");
		$(".one-b").slideUp(10);
		});
		
});



//搜索下拉
$(function(){
		//点击后判断ul是否隐藏
		$("div.search-xl").mouseenter(function(){					
			var ul = $(".listUl");
				ul.slideDown(150);
		});
		$('div.search-xl').mouseleave(function(){
			var ul = $(".listUl");
				ul.slideUp(150);
		})
		//选中某个内容后赋值给p标签，并隐藏ul列表
		$(".listUl li").click(function(){
			var txt = $(this).text();
			var cid = $(this).attr("cid");
				$(".search-xl>em").html(txt);
				$("div.search-xl>.listUl").hide();
				$(".text").attr("cid",cid);
				$("#tids").val(cid);
		});
});
		
//选择品牌	
$(function(){
		$(".column span").hover(function(){
		  $(".column-pp").show();
		})	
		$('.column-pp').hover(function(){
				var tt = $(this).data('name');
				var str = "../public/Pc2.0/img/a"+tt+"-b.png";
				$('.cc'+tt).attr('src',str);
		},function(){
				var tt = $(this).data('name');
				var str = "../public/Pc2.0/img/a"+tt+"-a.png";
				$('.cc'+tt).attr('src',str);
		})
		$(".column").mouseleave(function(){
			$(".column-pp").hide();
			
		})
		$('.column span').hover(function(){
			var t =$(this).find('img').attr('tid');
			var str = "../public/Pc2.0/img/a"+t+"-b.png";
			$(this).find('img').attr('src',str);
			$('.column-pp').data('name',t);
			
			},function(){
			var t =$(this).find('img').attr('tid');
			var str = "../public/Pc2.0/img/a"+t+"-a.png";
			$(this).find('img').attr('src',str);
				}
			
			);
});

//获取高度
$(function(){
	var ulH1=$('.ul_top_a').height();
	$('.pp-lf-top .top_a').height(ulH1)
	var ulH1=$('.ul_top_b').height();
	$('.pp-lf-top .top_b').height(ulH1)
})