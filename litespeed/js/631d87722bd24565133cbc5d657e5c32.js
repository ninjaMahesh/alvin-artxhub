(function(e){'use strict';e(window).ready(function(){Widget_swiperSlider()})})(jQuery);function Widget_swiperSlider(){if(jQuery('.socialv-widget-swiper').length>0){jQuery('.socialv-widget-swiper').each(function(i){let slider=jQuery(this);var r=(slider.data('navnext'))?'#'+slider.data('navnext'):'',l=(slider.data('navprev'))?'#'+slider.data('navprev'):'',n=(slider.data('pagination'))?'#'+slider.data('pagination'):'',e=slider.data('autoplay');if(e){e={delay:slider.data('autoplay')}};var s={el:n,dynamicBullets:!0,clickable:!0};var t={0:{slidesPerView:slider.data('mobile'),},768:{slidesPerView:slider.data('tab'),},999:{slidesPerView:slider.data('laptop'),},1400:{slidesPerView:slider.data('slide'),}};var d={loop:slider.data('loop'),speed:slider.data('speed'),spaceBetween:slider.data('spacebtslide'),slidesPerView:slider.data('slide'),navigation:{nextEl:r,prevEl:l},autoplay:e,pagination:(slider.data('pagination'))?s:'',breakpoints:t,};var a=new Swiper(slider[0],d);document.addEventListener('theme_scheme_direction',(i)=>{a.destroy(!0,!0);setTimeout(()=>{a=new Swiper('.socialv-widget-swiper',d)},500)})});setTimeout(function(){window.dispatchEvent(new Event('resize'))},500)}};
;