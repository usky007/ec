<div id="header_2014_box">
	<div id="header_2014">
		<?php
			$showlogo = config::ditem('gamerule.homepage.showlogo',false,1);
			$showlogo = $showlogo?'show':"";
		?>
	    <div class="logo" >
			<a href="<?php echo  url::home(); ?>" class="<?php echo $showlogo ;?>"></a>
		</div>
		<?php
			$account = new Account();
			$loginuser = $account->get_loginuser();
			$msgcnt = is_null($loginuser)?0:$loginuser->getNewMessageCount();
		?>

		<div id="user-menu_2014">
			<ul id="user-menu-ul_2014">
			<?php
			if($msgcnt>0)
			{
			?>

			<li class="notifications new">
			<a class="readedmsg" href="#">
			<span class="count"><?php echo $msgcnt;?></span>
			短消息
			</a>
			<ul id="notifications-panel" style="display:none">

			<?php
				$messages = $loginuser->getMessages();
				foreach($messages as $msg)
				{

					$sender = new User_Model($msg->senderId);
					if(!$sender->find()->loaded())
					{
						//continue;
					}
					$status = $msg->status == Message_Model::STATUS_NEW?"":"read";
			?>
				<li class="new-activity <?php echo $status;?>" >
				<a href="<?php echo url::site($msg->getMessages(Message_Model::MSG_LINK));?>">
				<img  class="notification-image"  width="60" height="60" src="<?php echo user::avatar($sender, $layout->resource_path("images/fce8c0181.png"),60,60); ?>" />
				<div class="notification-content">
					<?php echo $msg->getMessages();?>
					<div class="notification-meta"><?php echo date::timespan_string($msg->created,time())?></div>
				</div>
				</a>
				</li>
			<?php
				}
			?>
			</ul>
			</li>

		<?php
			}

		?>


		<?php

			if(is_null($loginuser))
			{
		?>
				<?php if(config_Core::item('account.allow_register')){ ?>
				<li><a class="show-signup-button" href="<?php echo url::site("/signup?return=".urlencode(url::home())) ?>">注册</a></li>
				<li>或</li>
				<?php }?>
				<li><a class="show-login-button" href="<?php echo url::site("/login?return=".urlencode(url::home())) ?>">登录</a></li>

		<?php
			}else{
		?>
				<li class="user">
					<a href="<?php echo format::getLink_UserGuides(); ?>">
						<img width="24" height="24" src="<?php echo user::avatar($loginuser, $layout->resource_path("images/fce8c0181.png"),24,24); ?>" />
						<span><?php echo $loginuser->nickname?>的地图</span>
					</a>
				</li>
		<?php }?>
			</ul>
		</div><!-- #user-menu -->

	    <ul id="main-tabs_2014">
	        <li class="current">
	        	<a href="<?php echo  url::home(); ?>">首页</a>
	        </li>
	        <li class="active">
	        	<a href="http://www.uutuu.com/fotolog">相册</a>
	        </li>
	        <li class="active">
	        	<a href="http://wp.uutuu.com/category/infos/">资讯</a>
	        </li>
	        <li class="active">
	        	<a href="http://www.uutuu.com/activity">活动</a>
	        </li>
	    </ul><!-- #main-tabs -->
	</div>
</div><!-- #header -->