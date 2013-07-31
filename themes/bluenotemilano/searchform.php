<form method="get" id="searchform" action="<?php bloginfo('url'); ?>/">
	<?php
	    $i = __('Search this site', 'bnm');
		$q = get_search_query();
		$v = ( $q == "" ) ? $i : $q;
	?>
	<input class="wpdk-form-input" onblur="this.value=(this.value == '')?'<?=$i?>':this.value" onfocus="this.value=(this.value == '<?=$i?>')?'':this.value" type="text" value="<?=$v?>" name="s" id="s" />
</form>