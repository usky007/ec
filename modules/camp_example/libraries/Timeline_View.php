<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * View extension for maggie timeline
 *
 * $Id: Timeline_View.php 54 2011-07-22 12:43:48Z zhangjyr $
 *
 * @package    timeline
 * @author	   Tianium
 * @copyright  (c) 2010 ukohana
 */
class Timeline_View extends View {
	protected $layout;

	public function __construct($data = NULL, $layout = NULL)
	{
		$this->kohana_local_data = $data;
		if (is_null($layout)) {
			$layout = new AppLayout_View();
		}
		$this->layout = $layout;
	}

	public function render($print = FALSE, $renderer = FALSE)
	{
		$result = "";
		$ec = 0;
		$pc = 0;
		
		foreach ($this->kohana_local_data as $id => $entry) {
			
//			// no pic will be override.
//			if (isset($entry['media'])) {
//				if (empty($entry['retweeted_status'])) {
//					$override =  &$entry;
//				}
//				else {
//					$override = &$entry['retweeted_status'];
//				}
//				
//				if (isset($entry['media']['pic']) && empty($entry['bmiddle_pic'])) {
//					$override['bmiddle_pic'] = $entry['media']['pic'];
//				}
//				if (isset($entry['media']['video'])) {
//					$override['original_pic'] = $entry['media']['video'];
//				}
//			}
				
			$ec++;
			if ( ! empty( $entry['retweeted_status'] ) ) {
				if ( ! empty( $entry['retweeted_status']['bmiddle_pic'] ) ) {
		
					if ( $pc  == 0 ) {
		      			$fragment = $this->render_photoWithCaption( $entry, true);
		      			$pc = 4;
					}
					else if  ( ($ec-1) % 5 == 2 ) {
		      			$fragment = $this->render_mixed( $entry, '', true);
		      		}      
		      		else if ( ($ec-1) % 3 == 1 ) {
		      			$fragment = $this->render_mixed( $entry, 'white rev', true);
		      		}
		      		else {
		      			$fragment = $this->render_mixed( $entry, 'white', true);
		      		}
		      		
		      		$pc = $pc - 1;
		
			    }
		    	else {
		        	$fragment = $this->render_answer( $entry, 'white', true);
		      	}
			}
		  	elseif ( empty( $entry['bmiddle_pic']) ) {
		    	$fragment = $this->render_answer( $entry, 'white', false);
		  	}
		  	else {
		    	if ( $ec == 1 ) {
		      		$fragment = $this->render_photo( $entry );
		      		$pc = 2;
		    	}
		    	else {
		      		$fragment = $this->render_photoWithCaption( $entry );
		    	}
		  	}
		  	
		  	$result .= $fragment;
		}

		if($print)
		{
			echo $result;
			return;
		}
		
		return $result;
	}
	
	protected function url_render( $text )
	{
	//	return $text;
		
		$text = preg_replace( "/ *http:\/\/([a-zA-Z0-9\.\/]*) ?/u", " <a class=\"tu_iframe_1024x600 mag_outerlink\" href=\"http://\\1\">&nbsp;http://\\1&nbsp;</a> ", $text);
		
		$text = preg_replace_callback( "/ *@([\x{4e00}-\x{9fa5}A-Za-z0-9_]*) ?/u", array($this, "user_url_callback"), $text);
	
	    $text = preg_replace_callback( "/ *#([^#]+)#/u", array($this, "topic_url_callback"), $text);	
	    
	    
	    $faceReg = "/\[[^\[\]]*\]/";
 		$facelist = config::item('timeline.face');
 
		preg_match_all( $faceReg, $text , $matches );
		$matches = $matches[0];
		foreach($matches as $m)
		{
			$key = substr($m,1,strlen($m)-2);
			if(isset($facelist[$key]))
			{
				$img = '<img src="http://dev.mico.cc/maggie/res/images/timeline/face/'.$facelist[$key].'" alt="'.$key.'">';
				$text = str_replace($m,$img,$text);
			}				
		}
 
	
		return $text;
		 
	}
	
	protected function post_qq_link( $msg)
	{
		$msgid = $msg['id'];
		if ( empty( $msg['retweeted_status'] ) ) {
			$msgtext = "";
		}
		else {
			$msgtext = '//@' .  $msg['user']['name'] . ': ' . htmlspecialchars( $msg['text'] );
		}
		$comments = (isset($msg['comments']) && $msg['comments'] > 0)  ? "({$msg['comments']})" : "";
		$rts = (isset($msg['rt']) && $msg['rt'] > 0)  ? "({$msg['rt']})" : "";
		
		$s = '<div id="'.$msgid.'" class="comments" style="display:none">';
		$s .= '<form op="comment" class="commentform" method="POST" action="'. miurl::subsite("maggie/", "ajax/comment/$msgid") .'"> <input type="text" name="status" class="commentinput"><input class="commentbtn" type="submit" name="pt" value="发送"><br/><br/><input type="checkbox" name="repost" value="1">同时转发</form><ul><div class="navi"></div></ul><div class="loading"></div></div>';
		
		$s .= '<div class="notes" style="display: none;"><span class="q"><a href="#'.
			miurl::subsite("maggie/","ajax/comments/$msgid"). "\" id=\"$msgid\" op=\"show_comments\">评$comments</a> | <a class=\"fwd_report\" href=\"#\" id=\"$msgid\" op=\"newrepost\" ft=\"$msgtext\">转$rts</a></span></div>";
		
		return $s;
	}
	
	protected function render_mixed( $msg, $surfix = '', $retweet = false )
	{
	  $s = '<div style="position: absolute" class="post mixed '.$surfix.'" id="msg-'.$msg['tcid'].'">';
	  $s = $s.'<div class="inside"><div class="content">';
	
	  if ( $retweet ) {
	    $s = $s.'<div class="media"><a href="'.$msg['retweeted_status']['original_pic'].'" class="top_up"><img alt="" src="'.$msg['retweeted_status']['bmiddle_pic'].'">';
	  }
	  else {
	    $s = $s.'<div class="media"><a href="'.$msp['original_pic'].'" class="top_up"><img alt="" src="'.$msg['bmiddle_pic'].'">';
	  }
		
	  $s .= '<img class="play" src="'. $this->layout->resource_path("images/play_s.png") . '"/></a></div>';
	  $s = $s.'<div class="microblog">'.$this->url_render( $msg['text']).'</div>';
	
	  if ( ( $retweet ) && ( $msg['user']['id'] != $msg['retweeted_status']['user']['id'] ) ) {
	    $s = $s.'<div class="info"><div class="author"><div class="avatar"><img src="'.$msg['user']['profile_image_url'].'"></div>';
	    $s = $s.'<div class="name"><a href="'.miurl::subsite("maggie/", 'user/'.$msg['user']['id']).'">'.$msg['user']['screen_name'].'</a>';
	    $s = $s.'</div></div></div>';
	  }
	
	  if ( $retweet ) {
	    $s = $s.'<div class="microblog forward">'.$this->url_render( $msg['retweeted_status']['text']).'</div>';
	    $s = $s.'<div class="info"><div class="author"><div class="avatar"><img src="'.$msg['retweeted_status']['user']['profile_image_url'].'"></div>';
	    $s = $s.'<div class="name"><a href="'.miurl::subsite("maggie/", 'user/'.$msg['retweeted_status']['user']['id']).'">'.$msg['retweeted_status']['user']['screen_name'].'</a>';
	    $s = $s.'<span class="date">&nbsp; , '. date::timespan_string(strtotime($msg['retweeted_status']['created_at'])) .'</span></div></div></div>';
	  }
	
	  $s = $s.'</div>'.$this->post_qq_link( $msg ).'</div>';

	
	  $s = $s . '</div>';
	
	  return $s;
	}
	
	protected function render_photo( $msg )
	{	
	  $s  = '<div style="position: absolute;" class="post photo" id="msg-'.$msg['tcid'].'">';
	  $s .= '<div class="inside"><div class="hiddencontent">' . $this->url_render( $msg['text']) . '</div>';
	  $s .= '<div class="media"><a title="'.$msg['text'].'" href="'.$msg['original_pic'].'" class="top_up"><img alt="" src="'.$msg['bmiddle_pic'].'"><img class="play" src="'. $this->layout->resource_path("images/play.png") . '"/></a></div>'.$this->post_qq_link( $msg ).'</div>';
	
	  $s = $s . '</div>';
	
	  return $s;
	}
	
	protected function render_answer( $msg, $surfix = '', $retweet = false)
	{
		$s = '<div style="position: absolute" class="post answer ' . $surfix . '" id="msg-' . $msg['tcid'] . '">';
	  $s = $s . '<div class="inside"><div class="content">';
	  $s = $s . '<div class="question">' . $this->url_render( $msg['text']) . '</div>';
	  $s = $s . '<div class="ainfo"><span class="name"><a href="'.miurl::subsite("maggie/", 'user/'.$msg['user']['id']) . '">' . $msg['user']['screen_name']. '</a></span>';
	  $s = $s . '<span class="adate"> , ' . date::timespan_string(strtotime($msg['created_at'])) . '</span></div>';
	
	  if ( $retweet ) {
	    $s = $s . '<div class="word">' . $this->url_render( $msg['retweeted_status']['text']) . '</div>';
	    $s = $s . '<div class="ainfo"><span class="name"><a href="'.miurl::subsite("maggie/", 'user/'.$msg['retweeted_status']['user']['id']) . '">' . $msg['retweeted_status']['user']['screen_name']. '</a></span>';
	    $s = $s . '<span class="adate"> , ' . date::timespan_string(strtotime($msg['retweeted_status']['created_at'])) . '</span></div>';
	  }
	
	  if ( $retweet ) {
	    $s = $s . '</div><div class="asker"> <img width="70" height="70" alt="" src="' . str_replace( '/50/', '/180/', $msg['retweeted_status']['user']['profile_image_url']) . '"> </div>';
	  }
	  else {
	    $s = $s . '</div><div class="asker"> <img width="70" height="70" alt="" src="' . str_replace( '/50/', '/180/', $msg['user']['profile_image_url'] ) . '"> </div>';
	}
	
	  $s = $s . $this->post_qq_link( $msg ).'</div>';
	
	  $s = $s . '</div>';
		
	  return $s;
	}
	
	protected function render_quote( $msg )
	{
	  $s = '<div style="position: absolute" class="post quote" id="msg-' . $msg['tcid'] . '">';
	  $s = $s . '<div class="inside"><div class="content"> <span class="ldquo">“</span>';
	  $s = $s . '<div class="meta"> <span class="date">' . date::timespan_string(strtotime($msg['created_at'])) . '</span> </div>';
	  $s = $s . '<blockquote class="featured">' . $this->url_render( $msg['text']) . '</blockquote> <span class="rdquo">”</span> ';
	  $s = $s . '<div class="info"><div class="author"><div class="avatar"><img src="' . $msg['user']['profile_image_url'] . '"></div>';
	  $s = $s . '<div class="name"><a href="'.miurl::subsite("maggie/", 'user/'.$msg['user']['id']) . '">' . $msg['user']['screen_name']. '</a></div>';
	  $s = $s . '</div></div></div>';
	
	  $s = $s . $this->post_qq_link( $msg ).'</div>';
	
	  $s = $s . '</div>';
	
	  return $s;
	}
	
	protected function render_photoWithCaption( $msg,  $retweet = false )
	{
	  $s  = '<div style="position: absolute" class="post photo withcaption" id="msg-' . $msg['tcid'] . '">';
	  $s .= '<div class="inside">';
	
	  if ( $retweet ) {
	    $s .= '<div class="media"><a href="'  . $msg['retweeted_status']['original_pic'] . '" class="top_up"><img alt="" src="' . $msg['retweeted_status']['bmiddle_pic'] . '">';
	  }
	  else {
	    $s .= '<div class="media"><a href="'  . $msg['original_pic'] . '" class="top_up"><img alt="" src="' . $msg['bmiddle_pic'] . '">';
	  }
	  $s .= '<img class="play" src="'. $this->layout->resource_path("images/play.png") . '"/></a></div>';
	  $s .= '<div class="content"><div class="microblog">' . $this->url_render( $msg['text']) . '</div>';
	  $s .= '<div class="info"><div class="author"><div class="avatar"><img src="' . $msg['user']['profile_image_url'] . '"></div>';
	  $s .= '<div class="name"><a href="'.miurl::subsite("maggie/", 'user/'.$msg['user']['id']) . '">' . $msg['user']['screen_name']. '</a><span class="date">&nbsp; , &nbsp;'. date::timespan_string(strtotime($msg['created_at'])) .'</span></div>';
	  $s .= '</div></div>';
	  
	  if ( $retweet ) {
	    $s = $s . '<div class="microblog retweet">' . $this->url_render( $msg['retweeted_status']['text']) . '</div>';
	    $s = $s . '<div class="info"><div class="author"><div class="avatar"><img src="' . $msg['retweeted_status']['user']['profile_image_url'] . '"></div>';
	    $s = $s . '<div class="name"><a href="'.miurl::subsite("maggie/", 'user/'.$msg['retweeted_status']['user']['id']) . '">' . $msg['retweeted_status']['user']['screen_name']. '</a><span class="date">&nbsp; , &nbsp;'. date::timespan_string(strtotime($msg['retweeted_status']['created_at'])) .'</span></div>';
	    $s = $s . '</div></div>';
	  }
	
	  $s = $s . '</div>';
	  
	  $s = $s . $this->post_qq_link( $msg ).'</div>';
	
	  $s = $s . '</div>';
	
	  return $s;
	}
	
	// private callbacks
	public function user_url_callback($matchs) {
		$link = miurl::subsite("maggie/", "user/".urlencode(is_numeric($matchs[1]) ? "@".$matchs[1] : $matchs[1]));
		return " <a class=\"light\" href=\"$link\">@{$matchs[1]}</a> ";
	}
	
	public function topic_url_callback($matchs) {
		$link = miurl::subsite("maggie/", "topic/".urlencode($matchs[1]));
		return " <a class=\"light\" href=\"$link\">#{$matchs[1]}#</a> ";
	}
}