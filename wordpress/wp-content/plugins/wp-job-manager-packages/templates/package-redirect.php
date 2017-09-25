<?php
/**
 * @var $type         string        Type being redirected (browse, view, apply, contact, view_name, etc)
 * @var $slug         string        Slug defining associated post type (either job or resume)
 * @var $permalink    string        Permalink/URL that is being redirected to
 * @var $white_dimmer string        Class to set white/black dimmer to use (configured in settings)
 * @var $seconds      string        Seconds to delay before redirecting (configured in settings)
 * @var $hide_all     string        HTML/CSS to hide all other elements except dimmer and loader (configured in settings)
 * @var $notice       string        Notice HTML/Text to output under the loader
 */
?>
<div id="jmpack-redirect-dimmer-wrap" class="dimmable dimmed">
	<!-- z-index must be highest possible (2147483647 is highest 32-bit number, which is what most browsers max out at) -->
	<div id="jmpack-redirect-dimmer" class="ui page dimmer <?php echo $white_dimmer; ?> transition visible active" style="z-index: 2147483647;">
	  <div class="content" style="display: table;">
		  <div class="center" style="display: table-cell;">
			  <!-- animation: none; opacity: 1; required to override Listable styles -->
			  <div id="jmpack-redirect-notice" class="ui large active centered inline text loader" style="animation: none; opacity: 1; pointer-events: all;">
				<?php echo $notice; ?>
			  </div>
		  </div>
	  </div>
	</div>
</div>

