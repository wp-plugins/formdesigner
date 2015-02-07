<div class="formish width75">
	<form method="post">
		<?= $this->errorSummary($errors); ?>
		<div class="shift">
			<label>E-mail адрес</label>
			<div class="unit">
				<input type="text" class="text" name="formdesigner[email]" value="<?php echo esc_attr( $email );?>" />
			</div>
		</div>
		<div class="shift">
			<label>Имя</label>
			<div class="unit">
				<input type="text" class="text"  name="formdesigner[name]" value="<?php echo esc_attr( $name );?>" />
			</div>
		</div>
		<div class="shift btn">
			<button type="submit" class="button blue" name="signin">Создать аккаунт</button>
		</div>
	</form>
</div>