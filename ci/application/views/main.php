<html>
<head>
    <title><?php echo $title;?></title>
		<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>css/window.css">
		<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>css/layout.css">
		<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>bootstrap-3.3.7-dist/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>bootstrap-3.3.7-dist/css/bootstrap-theme.min.css">
</head>
<body>

  <!-- React begin -->
  <script src="https://unpkg.com/react@15/dist/react.js"></script>
  <script src="https://unpkg.com/react-dom@15/dist/react-dom.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/babel-standalone/6.18.1/babel.min.js"></script>
  <!-- React end -->


	<script src="<?php echo base_url(); ?>js/library/jquery-3.1.0.js"></script>
	<script src="<?php echo base_url(); ?>js/library/jquery-migrate-3.0.0.js"></script>
	<script src="<?php echo base_url(); ?>js/library/jquery.mousewheel.js"></script>
	<script src="<?php echo base_url(); ?>js/library/jquery-ui-1.11.4/jquery-ui.js"></script>
	<script src="<?php echo base_url(); ?>js/library/jTemplates_0_8_4/jTemplates/jquery-jtemplates_uncompressed.js"></script>

	<script src="<?php echo base_url(); ?>js/utilities.js"></script>
  <script src="<?php echo base_url(); ?>js/render.js"></script>
  <script src="<?php echo base_url(); ?>js/app/handle_input.js"></script>

  <div id='test_container'></div>
  <div id='react-root'></div>
</body>

<script src="<?php echo base_url(); ?>js/test_controller.js"></script>
<script>
  window.testController.main.init('abcde');
</script>

<script type="text/babel" src="<?php echo base_url(); ?>react/Sentences.js"></script>

<script type="text/babel" src="<?php echo base_url(); ?>js/app_controller.js"></script>
<script type="text/babel" src="<?php echo base_url(); ?>react/App.js"></script>

<script type="text/babel" src="<?php echo base_url(); ?>react/index.js"></script>

</html>
