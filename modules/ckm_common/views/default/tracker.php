<?php if (config::item ( "tracker.linezing.enable", false, false )) { ?>
<script type="text/javascript" src="http://js.tongji.linezing.com/3067278/tongji.js"></script><noscript><a href="http://www.linezing.com"><img src="http://img.tongji.linezing.com/3067278/tongji.gif"/></a></noscript>
<?php } ?>
<?php if (config::item ( "tracker.google.enable", false, false )) { ?>
<script type="text/javascript">
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','_guaTracker');

  _guaTracker('create', 'UA-2387253-7', 'uutuu.com');
  <?php if (config::ditem("tracker.google.inpage_linkid", false, false)) {
	  echo "_guaTracker('require', 'linkid', 'linkid.js');", PHP_EOL;
  } ?>
  _guaTracker('send', 'pageview');
  
  // for second property
  _guaTracker('create', 'UA-2387253-8', 'uutuu.com', {'name': 'another'});
  <?php if (config::ditem("tracker.google.inpage_linkid", false, false)) {
	  echo "_guaTracker('another.require', 'linkid', 'linkid.js');", PHP_EOL;
  } ?>
  _guaTracker('another.send', 'pageview');
  if (ZXC && ZXC.Defined('ZXC.Util.Tracker')) {
      ZXC.Import('ZXC.Util.Tracker');
	  Tracker.instance().addProperty('another', Tracker.TYPE_GOOGLE_UA);
  }
  
  <?php $user = Account::instance()->user;
  		if (!$user->is_guest()) {
	  		echo "_guaTracker('setCustomVar', 1, 'sessionid','{$user->uid}',1);";
  		}
  ?>
</script>
<?php } ?>