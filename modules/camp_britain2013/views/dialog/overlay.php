<!-- 弹出层开始 -->
	<div id='TB_overlay'></div>
	<div id='TB_window'>
	
	<!-- 活动规则开始 -->    
   	 <div class="rule_box" style="display: none;">
      <div class="box_close"><a><img src="<?php echo $layout->resource_path('images/britain/close.png');?>" /></a></div>
      <div class="box_title">活动规则</div>
      <ul>
        <li style="background-image:url(<?php echo $layout->resource_path('images/britain/icon_list01.jpg');?>)">在新浪微博上传英国美图，并@旅行者传媒 加上话题 #免费游Great英国#，既有机会获得英国旅游局提供的<br />
        <span class="t_red b">免费游英国大奖！</span><br />
          <span class="t_grey">Tips:  微博文字中写明照片地点</span><span class="small">「伦敦，威尔士，英格兰，苏格兰」</span><span class="t_grey">可增加中奖几率哦！</span><br />
          <br />
          <img src="<?php echo $layout->resource_path('images/britain/rule_01.jpg');?>" width="662" height="174" /> </li>
        <li style="background-image:url(<?php echo $layout->resource_path('images/britain/icon_list02.jpg');?>)">图片内容包括但不局限于英国历史遗迹、文化、购物、乡村及美食，微博文字中写明照片地点（伦敦，威<br />
          尔士，英格兰，苏格兰）可增加中奖几率哦！</li>
        <li style="background-image:url(<?php echo $layout->resource_path('images/britain/icon_list03.jpg');?>)">微博发布后回到活动页面，点击<font class='t_red b'>“我要参加”</font>填写您的准确联系方式，以便顺利领取奖品。</li>
      </ul>
    </div>
    <!-- 活动规则结束 --> 
    
    <!-- 我要参加开始  -->
     <div class="jion_box" style="display: none;">
      <div class="box_close"><a><img src="<?php echo $layout->resource_path('images/britain/close.png');?>" /></a></div>
      <div class="jion_info"> 
      	<span class="jion_box_title"> 为确保您能顺利领取奖品，请填写准确的联系方式</span>
      	<span id="join_info_error_alert" style="color:red;width:400px;padding-left:100px;"></span>
       <!-- join form start -->
        <ul>
          <li>
            <div class="title">真实姓名</div>
            <label>
              <input type="text" name="realName" id="realName" class="inputbox"/>
              <span class="red">*</span></label>
          </li>
          <li>
            <div class="title">电子邮箱</div>
            <label>
              <input type="text" name="email" id="email" class="inputbox"/>
              <span class="red">*</span></label>
          </li>
          <li>
            <div class="title">新浪微博</div>
            <label>
              <input type="text" name="weibo" id="weibo" class="inputbox" placeHolder='此处填写微博的链接'/>
            </label>
          </li>
          <li>
            <div class="title">手机</div>
            <label>
              <input type="text" name="mobile" id="mobile" class="inputbox"/>
            </label>
          </li>
          <div class="clear"></div>
        </ul>
        <div class="center"><a href="#" id='join_submit'><img src="<?php echo $layout->resource_path('images/britain/btn_01.jpg');?>" /></a></div>
        </form>
      </div>
      <div class="box_title">如何参与</div>
      <div class="jion_box_text"> 在新浪微博上传英国美图，并@旅行者传媒 加上话题 #免费游Great英国# ,<br />
       微博发布后回到活动页面，填写您的准确联系方式， 既有机会获得英国旅游局提供的<span class="t_red b">免费游英国大奖！</span><br />
        <span class="t_grey">Tips:  微博文字中写明照片地点</span><span class="small">「伦敦，威尔士，英格兰，苏格兰」</span><span class="t_grey">可增加中奖几率哦！</span><br />
        <br />
        <img src="<?php echo $layout->resource_path('images/britain/rule_01.jpg');?>" width="662" height="174" /> </div>
     </div>
     <!-- 我要参加结束 -->
     
     <!-- 微博图片弹框开始 -->
     <div class="photo_box" style="display: none;">
      <div class="box_close"><a><img src="<?php echo $layout->resource_path('images/britain/close.png');?>" /></a></div>
      <div class="photo"></div>
     </div>
     <!-- 微博图片弹框结束 -->
     
    </div>
<!-- 弹出层结束 -->