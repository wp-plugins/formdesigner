<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>      
	    <title>Конструктор форм FormDesigner</title>
	    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	    <link rel="stylesheet" href="<?= FORMDESIGNER__PLUGIN_URL; ?>src/style.css" type="text/css" media="all" />
	    <script type="text/javascript" src="<?= includes_url( 'js/tinymce/tiny_mce_popup.js' ); ?>"></script>
		<script>
	    function insert(id) {
	   		top.window.tinyMCE.execCommand('mceInsertContent',false,'[formdesigner id="'+id+'"]');
	  		tinyMCEPopup.editor.execCommand('mceRepaint');
	  		tinyMCEPopup.close();
	    }
	    </script>
	</head>    
    <body>
    	<div class="formish">
			<?php if($error): ?>
			<div class="errorSummary">
				<ul><li><?= $error; ?></li></ul>
			</div>
			<?php elseif($projects !== array()): ?>
			<div class="shift"><p>Выберите форму, которую необходимо вставить:</p></div>
			<div class="shift">
				<div class="unit">
					<select id="forms" style="width: 555px;">
						<?php foreach($projects as $id => $name): ?>
						<option value="<?= $id; ?>"><?= $name; ?> [ID: <?= $id; ?>]</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="shift">
				<button class="button blue" onclick="var el = document.getElementById('forms');insert(el.options[el.selectedIndex].value)">Вставить форму</button>
			</div>
			<?php else: ?>
			<p>У Вас нет созданных проектов. Перейдите в раздел меню FormDesigner и создайте свой первый проект.</p>
			<?php endif; ?>
		</div>
	</body>
</html>