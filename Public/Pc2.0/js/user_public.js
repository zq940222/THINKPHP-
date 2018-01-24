	$(function(){
		var navBtn=$('#content2 .f-nav li span')
		navBtn.click(function(){
			navBtn.removeClass('hot')
			$(this).addClass('hot')
		})
		$('header li.tab').hover(function(){
			$('em',this).css({'background':'#fff','color':'#61bd52'})
			$('img',this).attr('src','../public/Pc2.0/img/h2_.png');
			$('span',this).show();
			$('.tab-box',this).css({'height':'172px','box-shadow':'#999 0px 0px 3px 0px'});
		},function(){
			$('em',this).css({'background':'','color':''})
			$('img',this).attr('src','../public/Pc2.0/img/h2.jpg');
			$('span',this).hide();
			$('.tab-box',this).css('display','');
			$('.tab-box',this).css({'height':'','box-shadow':''});
		})
	})
	$(function(){
		$("#bg").hide();
		$("#R").click(function(){
			$("#bg").hide();
		})
		$("#X").click(function(){
			$("#bg").hide();
		})
		$("#T").click(function(){
			$("#bg").show();
		})
    });