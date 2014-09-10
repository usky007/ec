<?php if(!$ucsynclogin):?>        	<div class="page_label_min">
            	填写基本信息 <a href="<?php echo $bindurl;?>">已有迷世界账号？现在绑定</a>
            </div>
            <hr>
			<div class="form_box">
			<form method="post" action="<?php echo url::site("ajax/social/register/$provider")?>" id="registerForm">
				<table class="form">
                	<tbody><tr>
                    	<td class="label">你的姓名:</td>
                        <td><input class="text" type="text" value="<?php echo isset($ms['name']) ? $ms['name'] : ''?>" name="nickname" autocomplete="off"> 填写后不可修改</td>
                    </tr>
                	<tr <?php if(isset($theme) && $theme = 'sina'):?>style="display: none;"<?php endif;?>>
                    	<td class="label">你的邮箱:</td>
                        <td><input class="text" value="@" type="text" name="email"></td>
                    </tr>
                	<tr>
                    	<td class="label">服务条款:</td>
                        <td>
                        	<textarea style="width: 500px;" class="text" readonly>　　迷世界通过国际互联网络为您提供一种全新的基于位置的在线社交方式；您只有完全同意下列所有服务条款并完成注册程序，才能成为迷世界的用户并使用相应服务。您在使用迷世界提供的各项服务之前，应仔细阅读本用户协议。

　　您在注册程序过程中点击"同意条款，立即注册"按钮即表示您与迷世界达成协议，完全接受本服务条款项下的全部条款。您一旦使用迷世界的服务，即视为您已了解并完全同意本服务条款各项内容，包括迷世界对服务条款随时做的任何修改。

一．服务内容
　　迷世界的具体服务内容由迷世界根据实际情况提供。迷世界保留变更、中断或终止部分网络服务的权利。
　　迷世界保留根据实际情况随时调整迷世界平台提供的服务种类、形式。迷世界不承担因业务调整给用户造成的损失。

二．内容使用权
　　我们鼓励用户充分利用迷世界平台自由地张贴和共享他们自己的信息。您可以自由张贴从迷世界个人主页或其他网站复制的图片等内容，但这些内容必须位于公共领域内，或者您拥有这些内容的使用权。同时，用户不应在自己的个人主页或社区中张贴其他受版权保护的内容。我们如果收到按下述程序提起的正式版权投诉，将会删除这些内容。
　　用户对于其创作并在迷世界上发布的合法内容依法享有著作权及其相关权利。

三．隐私保护
　　保护用户隐私是迷世界的重点原则，迷世界通过技术手段、提供隐私保护服务功能、强化内部管理等办法充分保护用户的个人资料安全。
　　迷世界保证不对外公开或向第三方提供用户注册的个人资料，及用户在使用服务时存储的非公开内容，但下列情况除外：
　　　◇ 事先获得用户的明确授权；
　　　◇ 按照相关司法机构或政府主管部门的要求。

四．社区准则
　　用户在申请使用迷世界服务时，必须提供真实的个人资料，并不断更新注册资料。如果因注册信息不真实而引起的问题及其后果，迷世界不承担任何责任。
　　用户在使用迷世界服务过程中，必须遵循国家的相关法律法规，不得利用迷世界平台，发布危害国家安全、色情、暴力等非法内容；不得利用迷世界平台发布含有虚假、有害、胁迫、侵害他人隐私、骚扰、侵害、中伤、粗俗、或其它道德上令人反感的内容。
　　用户使用本服务的行为若有任何违反国家法律法规或侵犯任何第三方的合法权益的情形时，迷世界有权直接删除该等违反规定之内容。
　　除非与迷世界单独签订合同，否则不得将社区用于商业目的；迷世界仅供个人使用。
　　不可以通过自动方式创建账户，也不可以对账户使用自动系统执行操作。
　　用户影响系统总体稳定性或完整性的操作可能会被暂停或终止，直到问题得到解决。

五．免责声明
　　互联网是一个开放平台，用户将照片等个人资料上传到互联网上，有可能会被其他组织或个人复制、转载、擅改或做其它非法用途，用户必须充分意识此类风险的存在。用户明确同意其使用迷世界服务所存在的风险将完全由其自己承担；因其使用迷世界服务而产生的一切后果也由其自己承担，迷世界对用户不承担任何责任。
　　迷世界不保证服务一定能满足用户的要求，也不保证服务不会中断，对服务的及时性、安全性、准确性也都不作保证。对于因不可抗力或迷世界无法控制的原因造成的网络服务中断或其他缺陷，迷世界不承担任何责任。

六．服务变更、中断或终止
　　如因系统维护或升级的需要而需暂停网络服务、服务功能的调整，迷世界将尽可能事先在网站上进行通告。
　　如发生下列任何一种情形，迷世界有权单方面中断或终止向用户提供服务而无需通知用户：
　　　◇ 用户提供的个人资料不真实；
　　　◇ 用户违反本服务条款中规定的使用规则；
　　　◇ 未经迷世界同意，将迷世界平台用于商业目的。

七．服务条款的完善和修改
　　迷世界会有权根据互联网的发展和中华人民共和国有关法律、法规的变化，不时地完善和修改迷世界服务条款。迷世界保留随时修改服务条款的权利，用户在使用迷世界平台服务时，有必要对最新的迷世界服务条款进行仔细阅读和重新确认，当发生有关争议时，请以最新的服务条款为准。

八．特别约定
　　用户使用本服务的行为若有任何违反国家法律法规或侵犯任何第三方的合法权益的情形时，迷世界有权直接删除该等违反规定之信息，并可以暂停或终止向该用户提供服务。
　　若用户利用本服务从事任何违法或侵权行为，由用户自行承担全部责任，因此给迷世界或任何第三方造成任何损失，用户应负责全额赔偿。

* 本条款的最终解释权归迷世界所有</textarea>
                        </td>
                    </tr>
                	<tr>
                    	<td class="label"></td>
                        <td>
                        	<input type="checkbox" name="accept" value="1">我已阅读，并同意以上条款
                        </td>
                    </tr>
                	<tr>
                    	<td class="label"></td>
                        <td>
                        	<input class="btn_blue_130" value="同意条款,立即注册" type="submit">
                        	<input type="hidden" name="location" value="<?php echo isset($ms['location']) ? $ms['location'] : '';?>"/>
                        	<input type="hidden" name="invite_uid" value="<?php echo $invite_uid;?>"/>
                        	<input type="hidden" name="app" value="<?php echo $app;?>"/>
                        	<input type="hidden" name="code" value="<?php echo $code;?>"/>
                        </td>
                    </tr>
                    <tr>
                    	<td class="label"></td>
                    	<td><div id="registerinfo"></div></td>
                    </tr>
                </tbody></table>
                </form>
            </div>
<?php else:?>

<style>
<!--
div.main {float: none;}
-->
</style>
<div class="tip_gray" style="width: 59%; margin: 50px auto;padding-top:3px;">
	<h2 style="font-size:14px;">信息提示</h2>
	<p style="font-size:14px;padding:2em 1em;margin:0px" id="logininfo">
		<?php if(isset($errorinfo)):?>
			<?php echo $errorinfo,'，返回<a href="'.url::site("login").'">登录</a>界面'?>
		<?php elseif(isset($msg) && $msg != ''):?>
			<span id="msginfo"><?php echo $msg;?></span>
		<?php endif;?>
	</p>
	<p class="op" style="text-align:right;font-size:12px;padding:2em 1em;margin:0px">
		<a href="<?php echo url::site('main')?>">返回首页</a>
	</p>
</div>
<?php endif;?>
<div id="info"></div>