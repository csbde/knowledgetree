(function($){
	$.fn.buttontabs=function(opts){
		if(typeof(opts)!='object')opts={};
		opts=$.extend({
			containerId:'',
			animSpeed:200,
			containerClass:''
		},opts);
		this.each(function(){
			if(this.tagName == 'UL'){
				var self=this;
				//add buttonTabs class & container
				$(this).addClass('buttonTabs').wrap('<div class="buttonTabContainer '+opts.containerClass+'">');
				$(self).parent('.buttonTabContainer').append($('<div />').addClass('buttonTabContentContainer'));
				if(opts.containerId)$(self).parent('.buttonTabContainer').attr('id',opts.containerId);
				//enumerate the valid items
				var firstItem=null;
				
				$(this).children('li[title]').each(function(){
					if(!firstItem)firstItem=this; //Default Tab Item
					var content=$(this).children();
					$(this).children().remove();
					var contents=$('<div />').addClass('buttonTabContents').addClass(this.title).append(content);
					$(self).parent('.buttonTabContainer').find('.buttonTabContentContainer').append(contents);
					
					//Set Tab
					$(this).html(this.title).addClass('buttonTab');
					this.title='';
					
					
					$(this).click(function(){
						var container=$($(this).parents('.buttonTabContainer')[0]);
						var others=$($(this).parents('.buttonTabs')[0]).find('li.buttonTab');
						
						//mark all tabs as inactive
						others.removeClass('buttonTabActive');
						//mark this tab as active
						$(this).addClass('buttonTabActive');
						
						//hide all content
						if(opts.animSpeed>0){
							container.find('.buttonTabContents').fadeOut(opts.animSpeed);
							container.find('.buttonTabContents.'+$(this).text()).fadeIn(opts.animSpeed);
						}else{
							container.find('.buttonTabContents').hide();
							container.find('.buttonTabContents.'+$(this).text()).show();
						}
					});
				});
				
				$($(this).parents('.buttonTabContainer')[0]).find('.buttonTabContents').hide();
				$(firstItem).click();
			}
		});
		return this;
	}
	
	$(document).ready(function(){
		var page=(jQuery.url.attr('anchor')+'').toLowerCase();
		if(page){
			if(typeof(console)!='undefined'){
				console.log("@@@ SEARCHING FOR TAB: "+page);
				console.log($('.page_'+page));
			}
			$('.page_'+page).click();
		}
		//kt_path_info=instaview.processor.link
			
		//Convert the current preview link to a javascript action
		$('#middle_nav a').each(function(){
            if(this.href.search("kt_path_info=instaview\.processor\.link")>-1){
                var elem=$(this);
                elem.click(function(){
                	$('.page_preview').click();
                }).attr('href','#');
            }			
		});
		
	});
	
})(jQuery);