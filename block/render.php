<?php
  $options = get_option('sitespeaker_settings');
  if ($options['mode'] == 'auto' || $options['mode'] == 'manual') echo $options['code'];
?>
