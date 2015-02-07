<?php $el = 'form_'.$id.'_'.mt_rand(1, 10); ?>
<div id="<?= $el; ?>"></div>
<script type="text/javascript">
(function (d, w, c) {
    (w[c] = w[c] || []).push({formId:<?= $id; ?>, host:"formdesigner.ru", el: "<?= $el; ?>"});
    var s = d.createElement("script"), g = "getElementsByTagName";
	s.type = "text/javascript"; s.charset="UTF-8"; s.async = false;
	s.src = (d.location.protocol == "https:" ? "https:" : "http:")+"//formdesigner.ru/js/iform.js";
	var h=d[g]("head")[0] || d[g]("body")[0];
	h.appendChild(s);
})(document, window, "fdforms");
</script>